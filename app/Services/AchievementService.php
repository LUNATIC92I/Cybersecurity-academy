<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Awards badges when measurable thresholds are reached.
 */
final class AchievementService
{
    public static function evaluate(int $userId): array
    {
        $metrics = [
            'lessons_completed' => (int) db()->value('SELECT COUNT(*) FROM lesson_progress WHERE user_id = ? AND status = "completed"', [$userId]),
            'labs_passed' => (int) db()->value('SELECT COUNT(DISTINCT lab_id) FROM lab_attempts WHERE user_id = ? AND passed = 1', [$userId]),
            'exams_passed' => (int) db()->value('SELECT COUNT(DISTINCT quiz_id) FROM quiz_attempts WHERE user_id = ? AND passed = 1', [$userId]),
            'streak_days' => ActivityService::streak($userId),
            'certificates' => (int) db()->value('SELECT COUNT(*) FROM certificates WHERE user_id = ? AND status = "valid"', [$userId]),
        ];
        $awarded = [];
        $candidates = db()->fetchAll(
            'SELECT a.* FROM achievements a
             LEFT JOIN user_achievements ua ON ua.achievement_id = a.id AND ua.user_id = ?
             WHERE ua.id IS NULL',
            [$userId]
        );
        foreach ($candidates as $achievement) {
            if (($metrics[$achievement['criteria_type']] ?? 0) >= (int) $achievement['threshold']) {
                $inserted = db()->query(
                    'INSERT IGNORE INTO user_achievements (user_id, achievement_id, share_token) VALUES (?, ?, ?)',
                    [$userId, (int) $achievement['id'], bin2hex(random_bytes(16))]
                )->rowCount();
                if ($inserted) {
                    $awarded[] = $achievement['code'];
                    NotificationService::notify($userId, 'achievement_earned', ['code' => $achievement['code']], route('dashboard.achievements'));
                }
            }
        }
        return $awarded;
    }

    public static function forUser(int $userId): array
    {
        $rows = db()->fetchAll(
            'SELECT a.*, ua.earned_at, ua.is_public, ua.share_token, ua.id AS user_achievement_id
             FROM achievements a
             LEFT JOIN user_achievements ua ON ua.achievement_id = a.id AND ua.user_id = ?
             ORDER BY a.criteria_type, a.threshold',
            [$userId]
        );
        return Localizer::attach($rows, 'achievement_translations', 'achievement_id', 't', locale());
    }
}
