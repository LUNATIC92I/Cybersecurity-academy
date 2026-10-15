<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Daily learning activity used for streaks and the recent-activity feed.
 */
final class ActivityService
{
    public static function record(int $userId, string $type, ?int $referenceId = null): void
    {
        db()->insert('learning_activity', [
            'user_id' => $userId,
            'activity_date' => gmdate('Y-m-d'),
            'type' => $type,
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * Consecutive days (UTC) with at least one activity, ending today or
     * yesterday.
     */
    public static function streak(int $userId): int
    {
        $dates = db()->column(
            'SELECT DISTINCT activity_date FROM learning_activity WHERE user_id = ? ORDER BY activity_date DESC LIMIT 400',
            [$userId]
        );
        if (!$dates) {
            return 0;
        }
        $expected = new \DateTimeImmutable('today', new \DateTimeZone('UTC'));
        if ($dates[0] !== $expected->format('Y-m-d')) {
            $expected = $expected->modify('-1 day');
        }
        $streak = 0;
        foreach ($dates as $date) {
            if ($date !== $expected->format('Y-m-d')) {
                break;
            }
            $streak++;
            $expected = $expected->modify('-1 day');
        }
        return $streak;
    }

    public static function recent(int $userId, int $limit = 8): array
    {
        return db()->fetchAll(
            'SELECT type, reference_id, created_at FROM learning_activity WHERE user_id = ? ORDER BY id DESC LIMIT ' . max(1, min(50, $limit)),
            [$userId]
        );
    }

    /**
     * Activity counts for the last $days days, oldest first (for charts).
     */
    public static function daily(int $userId, int $days = 14): array
    {
        $rows = db()->fetchAll(
            'SELECT activity_date, COUNT(*) AS n FROM learning_activity
             WHERE user_id = ? AND activity_date >= ? GROUP BY activity_date',
            [$userId, gmdate('Y-m-d', strtotime('-' . ($days - 1) . ' days'))]
        );
        $map = array_column($rows, 'n', 'activity_date');
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = gmdate('Y-m-d', strtotime("-$i days"));
            $out[] = ['date' => $d, 'count' => (int) ($map[$d] ?? 0)];
        }
        return $out;
    }
}
