<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Lab grading. Answer specifications live in lab_tasks.answer_spec and are
 * never sent to the browser; scores are computed and stored server-side.
 */
final class LabService
{
    public const SOLUTION_AFTER_ATTEMPTS = 3;

    public static function tasks(int $labId, ?string $locale = null): array
    {
        $tasks = db()->fetchAll('SELECT id, lab_id, sort_order, task_type, points FROM lab_tasks WHERE lab_id = ? ORDER BY sort_order, id', [$labId]);
        return Localizer::attach($tasks, 'lab_task_translations', 'task_id', 't', $locale);
    }

    public static function attempts(int $userId, int $labId): array
    {
        return db()->fetchAll('SELECT * FROM lab_attempts WHERE user_id = ? AND lab_id = ? ORDER BY attempt_no DESC', [$userId, $labId]);
    }

    public static function hasPassed(int $userId, int $labId): bool
    {
        return (bool) db()->value('SELECT COUNT(*) FROM lab_attempts WHERE user_id = ? AND lab_id = ? AND passed = 1', [$userId, $labId]);
    }

    public static function canSeeSolution(int $userId, int $labId): bool
    {
        return self::hasPassed($userId, $labId)
            || count(self::attempts($userId, $labId)) >= self::SOLUTION_AFTER_ATTEMPTS
            || auth()->can('content.preview');
    }

    public static function hasAcknowledged(int $userId, int $labId): bool
    {
        return (bool) db()->value('SELECT COUNT(*) FROM lab_acknowledgements WHERE user_id = ? AND lab_id = ?', [$userId, $labId]);
    }

    public static function acknowledge(int $userId, int $labId, string $ip): void
    {
        db()->query('INSERT IGNORE INTO lab_acknowledgements (user_id, lab_id, ip_address) VALUES (?, ?, ?)', [$userId, $labId, $ip]);
        AuditLogger::log('lab.safety_acknowledged', 'lab', $labId, [], $userId);
    }

    /**
     * Grade a submission. $answers is keyed by task id.
     *
     * @return array attempt row
     */
    public static function submit(int $userId, array $lab, array $answers): array
    {
        return db()->transaction(function () use ($userId, $lab, $answers) {
            // Lock the user row to serialise concurrent submissions (prevents
            // duplicate attempt numbers and bypassing max_attempts).
            db()->value('SELECT id FROM users WHERE id = ? FOR UPDATE', [$userId]);
            $count = (int) db()->value(
                'SELECT COUNT(*) FROM lab_attempts WHERE user_id = ? AND lab_id = ?',
                [$userId, (int) $lab['id']]
            );
            if ($lab['max_attempts'] !== null && $count >= (int) $lab['max_attempts']) {
                throw new \DomainException('max_attempts');
            }

            $tasks = db()->fetchAll('SELECT * FROM lab_tasks WHERE lab_id = ? ORDER BY sort_order, id', [(int) $lab['id']]);
            $score = 0;
            $max = 0;
            $results = [];
            $stored = [];
            foreach ($tasks as $task) {
                $spec = json_decode((string) $task['answer_spec'], true) ?: [];
                $given = $answers[$task['id']] ?? ($task['task_type'] === 'multi' ? [] : '');
                if ($task['task_type'] !== 'multi' && is_array($given)) {
                    $given = '';
                }
                if (is_string($given)) {
                    $given = mb_substr($given, 0, 4000);
                }
                $correct = match ($task['task_type']) {
                    'text' => AnswerChecker::text((string) $given, $spec),
                    'number' => AnswerChecker::number($given, $spec),
                    'choice' => AnswerChecker::choice($given, $spec),
                    'multi' => AnswerChecker::multi($given, $spec),
                    'query' => AnswerChecker::query((string) $given, $spec)['ok'],
                    default => false,
                };
                $points = (int) $task['points'];
                $max += $points;
                if ($correct) {
                    $score += $points;
                }
                $results[$task['id']] = ['correct' => $correct, 'points' => $correct ? $points : 0, 'max' => $points];
                $stored[$task['id']] = $given;
            }
            $percent = $max > 0 ? (int) floor($score * 100 / $max) : 0;
            $passed = $percent >= (int) $lab['pass_percent'];

            $id = db()->insert('lab_attempts', [
                'user_id' => $userId,
                'lab_id' => (int) $lab['id'],
                'attempt_no' => $count + 1,
                'score' => $score,
                'max_score' => $max,
                'percent' => $percent,
                'passed' => $passed ? 1 : 0,
                'results' => json_encode($results),
                'answers' => json_encode($stored, JSON_UNESCAPED_UNICODE),
            ]);
            ActivityService::record($userId, $passed ? 'lab_passed' : 'lab_attempted', (int) $lab['id']);

            if ($lab['course_id']) {
                ProgressService::recalculate($userId, (int) $lab['course_id']);
            }
            AchievementService::evaluate($userId);

            return db()->fetch('SELECT * FROM lab_attempts WHERE id = ?', [$id]);
        });
    }

    /**
     * Read a dataset file shipped with the curriculum. Only bare file names
     * inside database/content/datasets are accepted (no path traversal).
     */
    public static function datasetPath(?string $file): ?string
    {
        if (!$file || !preg_match('/^[a-z0-9][a-z0-9._-]*\.(csv|json|log|txt|eml|md)$/i', $file)) {
            return null;
        }
        $base = realpath(base_path('database/content/datasets'));
        $path = realpath(base_path('database/content/datasets/' . $file));
        if ($base === false || $path === false || !str_starts_with($path, $base . DIRECTORY_SEPARATOR)) {
            return null;
        }
        return $path;
    }
}
