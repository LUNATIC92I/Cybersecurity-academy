<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Lesson and course progress. Progress is always derived from server-side
 * records; the browser can only ask to start or complete a lesson, and
 * completion is refused if the minimum study time has not elapsed.
 */
final class ProgressService
{
    public const COMPLETE_OK = 'ok';
    public const COMPLETE_TOO_SOON = 'too_soon';
    public const COMPLETE_NOT_ENROLLED = 'not_enrolled';

    public static function startLesson(int $userId, array $lesson, int $courseId): void
    {
        db()->query(
            'INSERT INTO lesson_progress (user_id, lesson_id, status, started_at, last_viewed_at)
             VALUES (?, ?, "started", UTC_TIMESTAMP(), UTC_TIMESTAMP())
             ON DUPLICATE KEY UPDATE last_viewed_at = UTC_TIMESTAMP()',
            [$userId, (int) $lesson['id']]
        );
        if (AccessService::isEnrolled($userId, $courseId)) {
            db()->query(
                'INSERT INTO course_progress (user_id, course_id, last_lesson_id) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE last_lesson_id = VALUES(last_lesson_id)',
                [$userId, $courseId, (int) $lesson['id']]
            );
        }
    }

    /**
     * Adds visible-tab study time reported by the lesson page. Each call is
     * capped at 60 seconds and the total at three times the lesson duration,
     * so the figure cannot be inflated meaningfully by a script.
     */
    public static function heartbeat(int $userId, array $lesson, int $seconds): void
    {
        $seconds = max(0, min(60, $seconds));
        $cap = max(600, (int) $lesson['duration_minutes'] * 60 * 3);
        db()->query(
            'UPDATE lesson_progress SET active_seconds = LEAST(?, active_seconds + ?), last_viewed_at = UTC_TIMESTAMP()
             WHERE user_id = ? AND lesson_id = ? AND last_viewed_at <= UTC_TIMESTAMP() - INTERVAL 20 SECOND',
            [$cap, $seconds, $userId, (int) $lesson['id']]
        );
    }

    public static function completeLesson(int $userId, array $lesson, array $course): string
    {
        if (!AccessService::isEnrolled($userId, (int) $course['id'])) {
            return self::COMPLETE_NOT_ENROLLED;
        }
        $progress = db()->fetch('SELECT * FROM lesson_progress WHERE user_id = ? AND lesson_id = ?', [$userId, (int) $lesson['id']]);
        if (!$progress) {
            return self::COMPLETE_TOO_SOON;
        }
        if ($progress['status'] === 'completed') {
            return self::COMPLETE_OK;
        }
        $minSeconds = (int) setting('lesson_min_seconds', 45);
        $elapsed = time() - strtotime($progress['started_at'] . ' UTC');
        if ($elapsed < $minSeconds) {
            return self::COMPLETE_TOO_SOON;
        }
        db()->query(
            'UPDATE lesson_progress SET status = "completed", completed_at = UTC_TIMESTAMP() WHERE user_id = ? AND lesson_id = ?',
            [$userId, (int) $lesson['id']]
        );
        ActivityService::record($userId, 'lesson_completed', (int) $lesson['id']);
        self::recalculate($userId, (int) $course['id']);
        AchievementService::evaluate($userId);
        return self::COMPLETE_OK;
    }

    /**
     * Recompute the cached course_progress row from source records and
     * issue the certificate when every completion rule is met.
     */
    public static function recalculate(int $userId, int $courseId): array
    {
        $status = CompletionService::evaluate($userId, $courseId);
        $units = $status['lessons']['total'] + $status['labs']['total'] + ($status['exam']['exists'] ? 1 : 0);
        $done = $status['lessons']['done'] + $status['labs']['done'] + ($status['exam']['passed'] ? 1 : 0);
        $percent = $units > 0 ? (int) floor($done * 100 / $units) : 0;
        if (!$status['eligible'] && $percent === 100) {
            $percent = 99;
        }

        db()->query(
            'INSERT INTO course_progress (user_id, course_id, percent, lessons_completed, lessons_total, labs_passed, labs_total, exam_passed, completed_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE percent = VALUES(percent), lessons_completed = VALUES(lessons_completed),
               lessons_total = VALUES(lessons_total), labs_passed = VALUES(labs_passed), labs_total = VALUES(labs_total),
               exam_passed = VALUES(exam_passed), completed_at = COALESCE(completed_at, VALUES(completed_at))',
            [
                $userId, $courseId, $percent,
                $status['lessons']['done'], $status['lessons']['total'],
                $status['labs']['done'], $status['labs']['total'],
                $status['exam']['passed'] ? 1 : 0,
                $status['eligible'] ? now() : null,
            ]
        );

        if ($status['eligible']) {
            db()->query(
                'UPDATE enrollments SET completed_at = COALESCE(completed_at, UTC_TIMESTAMP()) WHERE user_id = ? AND course_id = ?',
                [$userId, $courseId]
            );
            CertificateService::issueIfEligible($userId, $courseId);
        }
        return $status + ['percent' => $percent];
    }

    public static function completedLessonIds(int $userId, int $courseId): array
    {
        return array_map('intval', db()->column(
            'SELECT lp.lesson_id FROM lesson_progress lp
             JOIN lessons l ON l.id = lp.lesson_id JOIN modules m ON m.id = l.module_id
             WHERE lp.user_id = ? AND m.course_id = ? AND lp.status = "completed"',
            [$userId, $courseId]
        ));
    }

    public static function totalStudySeconds(int $userId): int
    {
        return (int) db()->value('SELECT COALESCE(SUM(active_seconds), 0) FROM lesson_progress WHERE user_id = ?', [$userId]);
    }

    /**
     * Next lesson the learner should open in a course (first incomplete
     * published lesson in syllabus order).
     */
    public static function nextLesson(int $userId, int $courseId): ?array
    {
        return db()->fetch(
            'SELECT l.*, m.slug AS module_slug FROM lessons l
             JOIN modules m ON m.id = l.module_id
             LEFT JOIN lesson_progress lp ON lp.lesson_id = l.id AND lp.user_id = ?
             WHERE m.course_id = ? AND l.status = "published" AND m.status = "published"
               AND (lp.status IS NULL OR lp.status <> "completed")
             ORDER BY m.sort_order, l.sort_order LIMIT 1',
            [$userId, $courseId]
        );
    }
}
