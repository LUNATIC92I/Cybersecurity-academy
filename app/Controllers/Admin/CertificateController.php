<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\CertificateService;

final class CertificateController extends AdminController
{
    public function index(Request $request): Response
    {
        $q = mb_substr((string) $request->query('q', ''), 0, 80);
        $status = (string) $request->query('status', '');
        $where = ['1 = 1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(c.certificate_number LIKE ? OR c.holder_name LIKE ?)';
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
            array_push($params, $like, $like);
        }
        if (in_array($status, ['valid', 'revoked'], true)) {
            $where[] = 'c.status = ?';
            $params[] = $status;
        }
        $result = $this->paginate(
            'SELECT c.*, u.email FROM certificates c LEFT JOIN users u ON u.id = c.user_id WHERE ' . implode(' AND ', $where) . ' ORDER BY c.issued_at DESC',
            $params,
            (int) $request->query('page', 1)
        );
        $events = db()->fetchAll(
            'SELECT e.*, c.certificate_number, u.display_name AS actor FROM certificate_events e
             JOIN certificates c ON c.id = e.certificate_id LEFT JOIN users u ON u.id = e.actor_id
             WHERE e.event <> "downloaded" ORDER BY e.id DESC LIMIT 15'
        );
        return $this->render('certificates/index', [
            'result' => $result,
            'q' => $q,
            'status' => $status,
            'events' => $events,
            'courses' => db()->fetchAll('SELECT c.id, ct.title FROM courses c JOIN course_translations ct ON ct.course_id = c.id AND ct.locale = "en" ORDER BY c.id'),
        ]);
    }

    public function issue(Request $request): Response
    {
        $data = $this->validate($request, [
            'email' => 'required|email|exists:users,email',
            'course_id' => 'required|integer|exists:courses,id',
        ]);
        $userId = (int) db()->value('SELECT id FROM users WHERE email = ?', [mb_strtolower($data['email'])]);
        $cert = CertificateService::adminIssue($userId, (int) $data['course_id'], (int) auth()->id());
        flash($cert ? 'success' : 'error', $cert ? __('admin.certs.issued', ['number' => $cert['certificate_number']]) : __('admin.certs.not_eligible'));
        return redirect(route('admin.certificates'));
    }

    public function revoke(Request $request): Response
    {
        $data = $this->validate($request, ['reason' => 'required|min:5|max:255']);
        $cert = db()->fetch('SELECT * FROM certificates WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        CertificateService::revoke((int) $cert['id'], $data['reason'], (int) auth()->id());
        flash('success', __('admin.certs.revoked'));
        return redirect(route('admin.certificates'));
    }

    public function reinstate(Request $request): Response
    {
        $data = $this->validate($request, ['reason' => 'required|min:5|max:255']);
        $cert = db()->fetch('SELECT * FROM certificates WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        CertificateService::reinstate((int) $cert['id'], $data['reason'], (int) auth()->id());
        flash('success', __('admin.certs.reinstated'));
        return redirect(route('admin.certificates'));
    }
}
