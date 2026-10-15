<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Platform statistics computed from live database records only.
 */
final class StatsRepository
{
    public static function public(): array
    {
        $db = db();
        return [
            'paths' => (int) $db->value('SELECT COUNT(*) FROM learning_paths WHERE is_published = 1'),
            'courses' => (int) $db->value('SELECT COUNT(*) FROM courses WHERE status = "published"'),
            'modules' => (int) $db->value('SELECT COUNT(*) FROM modules m JOIN courses c ON c.id = m.course_id WHERE c.status = "published" AND m.status <> "archived"'),
            'lessons' => (int) $db->value('SELECT COUNT(*) FROM lessons l JOIN modules m ON m.id = l.module_id WHERE l.status = "published" AND m.status = "published"'),
            'labs' => (int) $db->value('SELECT COUNT(*) FROM labs WHERE status = "published"'),
            'questions' => (int) $db->value('SELECT COUNT(*) FROM questions WHERE status = "active"'),
            'learners' => (int) $db->value('SELECT COUNT(DISTINCT ur.user_id) FROM user_roles ur JOIN roles r ON r.id = ur.role_id JOIN users u ON u.id = ur.user_id WHERE r.code = "student" AND u.status = "active"'),
            'certificates' => (int) $db->value('SELECT COUNT(*) FROM certificates WHERE status = "valid"'),
            'lessons_completed' => (int) $db->value('SELECT COUNT(*) FROM lesson_progress WHERE status = "completed"'),
            'labs_passed' => (int) $db->value('SELECT COUNT(*) FROM lab_attempts WHERE passed = 1'),
        ];
    }

    public static function admin(): array
    {
        $db = db();
        return self::public() + [
            'users_total' => (int) $db->value('SELECT COUNT(*) FROM users'),
            'users_30d' => (int) $db->value('SELECT COUNT(*) FROM users WHERE created_at >= UTC_TIMESTAMP() - INTERVAL 30 DAY'),
            'enrollments' => (int) $db->value('SELECT COUNT(*) FROM enrollments'),
            'attempts_7d' => (int) $db->value('SELECT COUNT(*) FROM quiz_attempts WHERE started_at >= UTC_TIMESTAMP() - INTERVAL 7 DAY'),
            'open_tickets' => (int) $db->value('SELECT COUNT(*) FROM support_tickets WHERE status <> "closed"'),
            'revenue_cents' => (int) $db->value('SELECT COALESCE(SUM(amount_cents),0) FROM payments WHERE status = "succeeded" AND provider <> "demo"'),
            'demo_orders' => (int) $db->value('SELECT COUNT(*) FROM orders WHERE provider = "demo" AND status = "paid"'),
            'active_subscriptions' => (int) $db->value('SELECT COUNT(*) FROM subscriptions WHERE status = "active"'),
            'fr_lessons_pending' => (int) $db->value('SELECT COUNT(*) FROM lessons l LEFT JOIN lesson_translations t ON t.lesson_id = l.id AND t.locale = "fr" WHERE l.status <> "archived" AND (t.lesson_id IS NULL OR t.translation_status <> "published")'),
        ];
    }

    /**
     * Daily signups/enrolments for the last N days (admin chart).
     */
    public static function daily(string $table, string $column, int $days = 30): array
    {
        if (!in_array($table, ['users', 'enrollments', 'quiz_attempts', 'lab_attempts'], true) || !preg_match('/^[a-z_]+$/', $column)) {
            throw new \InvalidArgumentException('Invalid table.');
        }
        $rows = db()->fetchAll(
            "SELECT DATE($column) AS d, COUNT(*) AS n FROM `$table` WHERE $column >= UTC_TIMESTAMP() - INTERVAL $days DAY GROUP BY DATE($column)"
        );
        $map = array_column($rows, 'n', 'd');
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = gmdate('Y-m-d', strtotime("-$i days"));
            $out[] = ['date' => $d, 'count' => (int) ($map[$d] ?? 0)];
        }
        return $out;
    }
}
