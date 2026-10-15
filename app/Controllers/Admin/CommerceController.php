<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuditLogger;
use App\Services\PaymentService;

final class CommerceController extends AdminController
{
    public function index(Request $request): Response
    {
        return $this->render('commerce', [
            'plans' => db()->fetchAll('SELECT p.*, t.name FROM plans p LEFT JOIN plan_translations t ON t.plan_id = p.id AND t.locale = "en" ORDER BY p.sort_order'),
            'coupons' => db()->fetchAll('SELECT * FROM coupons ORDER BY id DESC'),
            'orders' => db()->fetchAll('SELECT o.*, u.email FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.id DESC LIMIT 50'),
            'subscriptions' => db()->fetchAll(
                'SELECT s.*, u.email, p.code FROM subscriptions s JOIN users u ON u.id = s.user_id JOIN plans p ON p.id = s.plan_id ORDER BY s.id DESC LIMIT 50'
            ),
            'provider' => PaymentService::provider()->name(),
            'configured' => PaymentService::provider()->isConfigured(),
        ]);
    }

    public function storeCoupon(Request $request): Response
    {
        $data = $this->validate($request, [
            'code' => 'required|regex:/^[A-Za-z0-9_-]{3,40}$/|unique:coupons,code',
            'percent_off' => 'integer|between:1,100',
            'amount_off_cents' => 'integer',
            'max_redemptions' => 'integer',
            'expires_at' => 'date',
        ]);
        if (!$data['percent_off'] && !$data['amount_off_cents']) {
            throw new \App\Core\ValidationException(['percent_off' => __('admin.coupons.need_discount')], $request->all());
        }
        $id = db()->insert('coupons', [
            'code' => strtoupper($data['code']),
            'percent_off' => $data['percent_off'] ? (int) $data['percent_off'] : null,
            'amount_off_cents' => !$data['percent_off'] && $data['amount_off_cents'] ? (int) $data['amount_off_cents'] : null,
            'max_redemptions' => $data['max_redemptions'] ? (int) $data['max_redemptions'] : null,
            'expires_at' => $data['expires_at'] ? gmdate('Y-m-d H:i:s', strtotime($data['expires_at'])) : null,
        ]);
        AuditLogger::log('coupon.created', 'coupon', $id, ['code' => strtoupper($data['code'])]);
        flash('success', __('admin.saved'));
        return redirect(route('admin.commerce'));
    }

    public function toggleCoupon(Request $request): Response
    {
        $coupon = db()->fetch('SELECT * FROM coupons WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        db()->update('coupons', ['is_active' => (int) !$coupon['is_active']], ['id' => (int) $coupon['id']]);
        AuditLogger::log('coupon.toggled', 'coupon', (int) $coupon['id'], ['active' => !$coupon['is_active']]);
        return redirect(route('admin.commerce'));
    }

    public function updatePlan(Request $request): Response
    {
        $plan = db()->fetch('SELECT * FROM plans WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        $data = $this->validate($request, ['price_cents' => 'required|integer']);
        db()->update('plans', ['price_cents' => max(0, (int) $data['price_cents']), 'is_active' => $request->bool('is_active') ? 1 : 0], ['id' => (int) $plan['id']]);
        AuditLogger::log('plan.updated', 'plan', (int) $plan['id'], ['price_cents' => (int) $data['price_cents']]);
        flash('success', __('admin.saved'));
        return redirect(route('admin.commerce'));
    }
}
