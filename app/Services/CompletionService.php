<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Course completion rules (configurable per course by administrators):
 *  - every published, required lesson in published modules is completed;
 *  - every published, required lab linked to the course is passed
 *    (when the course has require_all_labs = 1);
 *  - the published final exam is passed with at least
 *    max(exam pass %, course min_exam_score).
 * Opening the last page never completes a course.
 */
final class CompletionService
{
    public static function evaluate(int $userId, int $courseId): array
    {
        $course = db()->fetch('SELECT * FROM courses WHERE id = ?', [$courseId]);
        if (!$course) {
            throw new \RuntimeException('Course not found.');
        }

        $lessonsTotal = (int) db()->value(
            'SELECT COUNT(*) FROM lessons l JOIN modules m ON m.id = l.module_id
             WHERE m.course_id = ? AND l.status = "published" AND m.status = "published" AND l.is_required = 1',
            [$courseId]
        );
        $lessonsDone = (int) db()->value(
            'SELECT COUNT(*) FROM lessons l JOIN modules m ON m.id = l.module_id
             JOIN lesson_progress lp ON lp.lesson_id = l.id AND lp.user_id = ? AND lp.status = "completed"
             WHERE m.course_id = ? AND l.status = "published" AND m.status = "published" AND l.is_required = 1',
            [$userId, $courseId]
        );

        $labsTotal = 0;
        $labsDone = 0;
        if ((int) $course['require_all_labs'] === 1) {
            $labsTotal = (int) db()->value(
                'SELECT COUNT(*) FROM labs WHERE course_id = ? AND status = "published" AND is_required = 1',
                [$courseId]
            );
            $labsDone = (int) db()->value(
                'SELECT COUNT(DISTINCT la.lab_id) FROM lab_attempts la JOIN labs lb ON lb.id = la.lab_id
                 WHERE la.user_id = ? AND la.passed = 1 AND lb.course_id = ? AND lb.status = "published" AND lb.is_required = 1',
                [$userId, $courseId]
            );
        }

        $exam = db()->fetch(
            'SELECT * FROM quizzes WHERE course_id = ? AND kind = "final_exam" AND status = "published" ORDER BY id LIMIT 1',
            [$courseId]
        );
        $required = $exam ? max((int) $exam['pass_percent'], (int) $course['min_exam_score']) : (int) $course['min_exam_score'];
        $best = $exam ? (int) db()->value(
            'SELECT COALESCE(MAX(percent), 0) FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? AND status = "submitted"',
            [$userId, (int) $exam['id']]
        ) : 0;
        $hasAttempt = $exam ? (bool) db()->value(
            'SELECT COUNT(*) FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? AND status = "submitted"',
            [$userId, (int) $exam['id']]
        ) : false;
        $examPassed = $exam !== null && $hasAttempt && $best >= $required;

        $eligible = $lessonsDone >= $lessonsTotal && $labsDone >= $labsTotal && $examPassed;

        return [
            'course' => $course,
            'lessons' => ['done' => $lessonsDone, 'total' => $lessonsTotal],
            'labs' => ['done' => $labsDone, 'total' => $labsTotal],
            'exam' => [
                'exists' => $exam !== null,
                'quiz' => $exam,
                'passed' => $examPassed,
                'best' => $best,
                'required' => $required,
            ],
            'eligible' => $eligible,
        ];
    }
}
