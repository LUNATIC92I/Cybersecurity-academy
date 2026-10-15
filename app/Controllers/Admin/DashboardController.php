<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\StatsRepository;

final class DashboardController extends AdminController
{
    public function index(Request $request): Response
    {
        return $this->render('dashboard', [
            'stats' => StatsRepository::admin(),
            'signups' => StatsRepository::daily('users', 'created_at', 30),
            'enrolments' => StatsRepository::daily('enrollments', 'enrolled_at', 30),
            'recentAudit' => db()->fetchAll(
                'SELECT a.*, u.display_name FROM audit_logs a LEFT JOIN users u ON u.id = a.actor_id ORDER BY a.id DESC LIMIT 8'
            ),
            'recentTickets' => db()->fetchAll('SELECT * FROM support_tickets WHERE status <> "closed" ORDER BY created_at DESC LIMIT 5'),
            'paymentProvider' => \App\Services\PaymentService::provider()->name(),
            'paymentLive' => \App\Services\PaymentService::isLive(),
        ]);
    }
}
