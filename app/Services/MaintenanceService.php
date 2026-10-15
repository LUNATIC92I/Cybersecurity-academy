<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Housekeeping, intended to run hourly from cron (`php bin/console maintenance:run`).
 */
final class MaintenanceService
{
    public static function run(): array
    {
        $db = db();
        $report = [];
        $report['password_resets_purged'] = $db->query('DELETE FROM password_resets WHERE expires_at < UTC_TIMESTAMP() OR used_at IS NOT NULL')->rowCount();
        $report['rate_limits_purged'] = $db->query('DELETE FROM rate_limits WHERE reset_at < UTC_TIMESTAMP()')->rowCount();
        $report['login_attempts_purged'] = $db->query('DELETE FROM login_attempts WHERE created_at < UTC_TIMESTAMP() - INTERVAL 90 DAY')->rowCount();
        $report['attempts_expired'] = $db->query(
            'UPDATE quiz_attempts SET status = "expired", submitted_at = UTC_TIMESTAMP()
             WHERE status = "in_progress" AND expires_at < UTC_TIMESTAMP() - INTERVAL 5 MINUTE'
        )->rowCount();
        $report['subscriptions_expired'] = $db->query(
            'UPDATE subscriptions SET status = "expired" WHERE status = "active" AND current_period_end < UTC_TIMESTAMP()'
        )->rowCount();
        $report['orders_abandoned'] = $db->query(
            'UPDATE orders SET status = "canceled" WHERE status = "pending" AND created_at < UTC_TIMESTAMP() - INTERVAL 2 DAY'
        )->rowCount();

        $grace = (int) config('security.account_deletion_grace_days', 14);
        $due = $db->column(
            'SELECT id FROM users WHERE deletion_requested_at IS NOT NULL AND deletion_requested_at < UTC_TIMESTAMP() - INTERVAL ' . $grace . ' DAY'
        );
        foreach ($due as $id) {
            UserService::erase((int) $id);
        }
        $report['accounts_erased'] = count($due);
        return $report;
    }
}
