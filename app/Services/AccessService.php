<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Decides whether a user may study a course and why. All checks run on the
 * server against subscriptions, grants and enrolments.
 */
final class AccessService
{
    /**
     * @return string|null access source (free|subscription|purchase|grant) or null
     */
    public static function accessSource(int $userId, array $course): ?string
    {
        if ($course['access_level'] === 'free') {
            return 'free';
        }
        $grant = db()->fetch(
            'SELECT source FROM access_grants
             WHERE user_id = ? AND (expires_at IS NULL OR expires_at > UTC_TIMESTAMP())
               AND (
                    (course_id IS NULL AND path_id IS NULL)
                 OR course_id = ?
                 OR path_id IN (SELECT path_id FROM learning_path_courses WHERE course_id = ?)
               )
             ORDER BY source = "purchase" DESC LIMIT 1',
            [$userId, (int) $course['id'], (int) $course['id']]
        );
        if ($grant) {
            return $grant['source'] === 'purchase' ? 'purchase' : 'grant';
        }
        if (self::hasActiveSubscription($userId)) {
            return 'subscription';
        }
        return null;
    }

    public static function canAccess(?int $userId, array $course): bool
    {
        if ($userId === null) {
            return false;
        }
        if (auth()->check() && auth()->id() === $userId && auth()->can('content.preview')) {
            return true;
        }
        return self::accessSource($userId, $course) !== null;
    }

    public static function hasActiveSubscription(int $userId): bool
    {
        return (bool) db()->value(
            'SELECT COUNT(*) FROM subscriptions s JOIN plans p ON p.id = s.plan_id
             WHERE s.user_id = ? AND s.status = "active" AND p.includes_all_courses = 1
               AND (s.current_period_end IS NULL OR s.current_period_end > UTC_TIMESTAMP())',
            [$userId]
        );
    }

    public static function activeSubscription(int $userId): ?array
    {
        return db()->fetch(
            'SELECT s.*, p.code AS plan_code FROM subscriptions s JOIN plans p ON p.id = s.plan_id
             WHERE s.user_id = ? AND s.status = "active"
               AND (s.current_period_end IS NULL OR s.current_period_end > UTC_TIMESTAMP())
             ORDER BY s.current_period_end DESC LIMIT 1',
            [$userId]
        );
    }

    public static function isEnrolled(int $userId, int $courseId): bool
    {
        return (bool) db()->value('SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND course_id = ?', [$userId, $courseId]);
    }

    /**
     * Enrol if the user has access. Returns false when access is missing.
     */
    public static function enroll(int $userId, array $course): bool
    {
        $source = self::accessSource($userId, $course);
        if ($source === null) {
            return false;
        }
        db()->query(
            'INSERT IGNORE INTO enrollments (user_id, course_id, access_source) VALUES (?, ?, ?)',
            [$userId, (int) $course['id'], $source]
        );
        ProgressService::recalculate($userId, (int) $course['id']);
        ActivityService::record($userId, 'enrolled', (int) $course['id']);
        return true;
    }

    /**
     * Lesson-level gate: enrolled learners, or preview lessons for any
     * signed-in user, or staff with preview permission.
     */
    public static function canViewLesson(?int $userId, array $lesson, array $course): bool
    {
        if ($userId === null) {
            return false;
        }
        if (auth()->can('content.preview')) {
            return true;
        }
        if ($lesson['status'] !== 'published' || $course['status'] !== 'published') {
            return false;
        }
        if ((int) $lesson['is_preview'] === 1) {
            return true;
        }
        return self::isEnrolled($userId, (int) $course['id']) && self::accessSource($userId, $course) !== null;
    }
}
