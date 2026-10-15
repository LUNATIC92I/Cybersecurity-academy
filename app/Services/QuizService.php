<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Assessment engine.
 *
 * - Questions are drawn at random from the quiz's banks when an attempt
 *   starts and frozen in quiz_attempt_questions; only those questions can be
 *   answered for that attempt.
 * - Scoring happens exclusively here, from answer_options.is_correct and
 *   questions.answer_spec. The browser never receives either.
 * - Submissions lock the attempt row, so a double submit cannot score twice.
 */
final class QuizService
{
    public const GRACE_SECONDS = 60;

    public static function find(int $id): ?array
    {
        return db()->fetch('SELECT * FROM quizzes WHERE id = ?', [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return db()->fetch('SELECT * FROM quizzes WHERE slug = ?', [$slug]);
    }

    public static function attempts(int $userId, int $quizId): array
    {
        return db()->fetchAll(
            'SELECT * FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? ORDER BY attempt_no DESC',
            [$userId, $quizId]
        );
    }

    public static function activeAttempt(int $userId, int $quizId): ?array
    {
        self::expireStale($userId, $quizId);
        return db()->fetch(
            'SELECT * FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? AND status = "in_progress" ORDER BY id DESC LIMIT 1',
            [$userId, $quizId]
        );
    }

    /**
     * Mark attempts past their deadline (plus grace) as expired.
     */
    public static function expireStale(int $userId, int $quizId): void
    {
        db()->query(
            'UPDATE quiz_attempts SET status = "expired", submitted_at = UTC_TIMESTAMP()
             WHERE user_id = ? AND quiz_id = ? AND status = "in_progress"
               AND expires_at < UTC_TIMESTAMP() - INTERVAL ' . self::GRACE_SECONDS . ' SECOND',
            [$userId, $quizId]
        );
    }

    /**
     * @return array{allowed: bool, reason: ?string, wait_minutes?: int}
     */
    public static function eligibility(int $userId, array $quiz): array
    {
        if ($quiz['status'] !== 'published' && !auth()->can('content.preview')) {
            return ['allowed' => false, 'reason' => 'unpublished'];
        }
        $course = db()->fetch('SELECT * FROM courses WHERE id = ?', [(int) $quiz['course_id']]);
        if (!$course || !AccessService::canAccess($userId, $course)) {
            return ['allowed' => false, 'reason' => 'no_access'];
        }
        if (!AccessService::isEnrolled($userId, (int) $course['id']) && !auth()->can('content.preview')) {
            return ['allowed' => false, 'reason' => 'not_enrolled'];
        }
        $attempts = array_values(array_filter(self::attempts($userId, (int) $quiz['id']), fn ($a) => $a['status'] !== 'in_progress'));
        if ($quiz['max_attempts'] !== null && count($attempts) >= (int) $quiz['max_attempts']) {
            return ['allowed' => false, 'reason' => 'max_attempts'];
        }
        if ($attempts && (int) $quiz['retake_cooldown_minutes'] > 0) {
            $last = strtotime($attempts[0]['submitted_at'] . ' UTC');
            $wait = $last + (int) $quiz['retake_cooldown_minutes'] * 60 - time();
            if ($wait > 0) {
                return ['allowed' => false, 'reason' => 'cooldown', 'wait_minutes' => (int) ceil($wait / 60)];
            }
        }
        if ($quiz['kind'] === 'final_exam' && self::hasPassed($userId, (int) $quiz['id'])) {
            return ['allowed' => false, 'reason' => 'already_passed'];
        }
        return ['allowed' => true, 'reason' => null];
    }

    public static function hasPassed(int $userId, int $quizId): bool
    {
        return (bool) db()->value('SELECT COUNT(*) FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? AND passed = 1', [$userId, $quizId]);
    }

    /**
     * Start (or resume) an attempt.
     */
    public static function start(int $userId, array $quiz): array
    {
        if ($active = self::activeAttempt($userId, (int) $quiz['id'])) {
            return $active;
        }
        $eligibility = self::eligibility($userId, $quiz);
        if (!$eligibility['allowed']) {
            throw new \DomainException((string) $eligibility['reason']);
        }

        return db()->transaction(function () use ($userId, $quiz) {
            db()->value('SELECT id FROM users WHERE id = ? FOR UPDATE', [$userId]);
            if ($active = db()->fetch(
                'SELECT * FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? AND status = "in_progress" LIMIT 1',
                [$userId, (int) $quiz['id']]
            )) {
                return $active;
            }

            $questionIds = self::drawQuestions($quiz);
            if ($questionIds === []) {
                throw new \DomainException('no_questions');
            }
            $attemptNo = 1 + (int) db()->value(
                'SELECT COALESCE(MAX(attempt_no), 0) FROM quiz_attempts WHERE user_id = ? AND quiz_id = ?',
                [$userId, (int) $quiz['id']]
            );
            $maxPoints = (int) db()->value(
                'SELECT COALESCE(SUM(points), 0) FROM questions WHERE id IN (' . Database::placeholders($questionIds) . ')',
                $questionIds
            );
            $attemptId = db()->insert('quiz_attempts', [
                'user_id' => $userId,
                'quiz_id' => (int) $quiz['id'],
                'attempt_no' => $attemptNo,
                'status' => 'in_progress',
                'locale' => \App\Services\Localizer::contentLocale(),
                'started_at' => now(),
                'expires_at' => gmdate('Y-m-d H:i:s', time() + (int) $quiz['duration_minutes'] * 60),
                'max_points' => $maxPoints,
            ]);
            foreach ($questionIds as $i => $qid) {
                $optionIds = array_map('intval', db()->column('SELECT id FROM answer_options WHERE question_id = ? ORDER BY sort_order, id', [$qid]));
                $type = db()->value('SELECT question_type FROM questions WHERE id = ?', [$qid]);
                if ($type !== 'true_false') {
                    self::secureShuffle($optionIds);
                }
                db()->insert('quiz_attempt_questions', [
                    'attempt_id' => $attemptId,
                    'question_id' => $qid,
                    'sort_order' => $i,
                    'option_order' => json_encode($optionIds),
                ]);
            }
            ActivityService::record($userId, 'quiz_started', (int) $quiz['id']);
            return db()->fetch('SELECT * FROM quiz_attempts WHERE id = ?', [$attemptId]);
        });
    }

    /**
     * Random selection from each linked bank; capped at question_count.
     */
    public static function drawQuestions(array $quiz): array
    {
        $banks = db()->fetchAll('SELECT bank_id, pick_count FROM quiz_banks WHERE quiz_id = ?', [(int) $quiz['id']]);
        $selected = [];
        foreach ($banks as $bank) {
            $ids = array_map('intval', db()->column(
                'SELECT id FROM questions WHERE bank_id = ? AND status = "active"',
                [(int) $bank['bank_id']]
            ));
            self::secureShuffle($ids);
            $selected = array_merge($selected, array_slice($ids, 0, (int) $bank['pick_count']));
        }
        $selected = array_values(array_unique($selected));
        self::secureShuffle($selected);
        return array_slice($selected, 0, max(1, (int) $quiz['question_count']));
    }

    /**
     * Questions of an attempt in display form. Correctness data is only
     * included when $withAnswers is true (results page, after submission).
     */
    public static function attemptQuestions(array $attempt, bool $withAnswers = false): array
    {
        $rows = db()->fetchAll(
            'SELECT q.id, q.question_type, q.difficulty, q.points, q.answer_spec, aq.option_order
             FROM quiz_attempt_questions aq JOIN questions q ON q.id = aq.question_id
             WHERE aq.attempt_id = ? ORDER BY aq.sort_order',
            [(int) $attempt['id']]
        );
        $rows = Localizer::attach($rows, 'question_translations', 'question_id', 't', $attempt['locale']);
        $responses = [];
        if ($withAnswers) {
            foreach (db()->fetchAll('SELECT * FROM quiz_responses WHERE attempt_id = ?', [(int) $attempt['id']]) as $r) {
                $responses[(int) $r['question_id']] = $r;
            }
        }
        foreach ($rows as &$row) {
            $order = json_decode((string) $row['option_order'], true) ?: [];
            $options = $order ? db()->fetchAll(
                'SELECT id, is_correct, match_key FROM answer_options WHERE id IN (' . Database::placeholders($order) . ')',
                $order
            ) : [];
            $options = Localizer::attach($options, 'answer_option_translations', 'option_id', 't', $attempt['locale']);
            $byId = array_column($options, null, 'id');
            $sorted = [];
            foreach ($order as $oid) {
                if (isset($byId[$oid])) {
                    $o = $byId[$oid];
                    if (!$withAnswers) {
                        unset($o['is_correct'], $o['match_key']);
                    }
                    $sorted[] = $o;
                }
            }
            $row['options'] = $sorted;
            if ($row['question_type'] === 'matching') {
                // Right-hand choices: distinct match keys with labels, ordered
                // independently of the left-hand items.
                $keys = db()->fetchAll(
                    'SELECT ao.match_key, aot.match_label, aot.locale FROM answer_options ao
                     JOIN answer_option_translations aot ON aot.option_id = ao.id
                     WHERE ao.question_id = ? AND ao.match_key IS NOT NULL',
                    [(int) $row['id']]
                );
                $choices = [];
                foreach ($keys as $k) {
                    if (!isset($choices[$k['match_key']]) || $k['locale'] === $attempt['locale']) {
                        $choices[$k['match_key']] = $k['match_label'];
                    }
                }
                ksort($choices);
                $row['match_choices'] = $choices;
            }
            if (!$withAnswers) {
                unset($row['answer_spec']);
            } else {
                $row['answer_spec'] = json_decode((string) $row['answer_spec'], true) ?: [];
                $row['response'] = $responses[(int) $row['id']] ?? null;
                if ($row['response']) {
                    $row['response']['response'] = json_decode((string) $row['response']['response'], true);
                }
            }
            unset($row['option_order']);
        }
        return $rows;
    }

    /**
     * Score and close an attempt. $responses is keyed by question id.
     */
    public static function submit(int $userId, int $attemptId, array $responses): array
    {
        $attempt = db()->transaction(function () use ($userId, $attemptId, $responses) {
            $attempt = db()->fetch('SELECT * FROM quiz_attempts WHERE id = ? FOR UPDATE', [$attemptId]);
            if (!$attempt || (int) $attempt['user_id'] !== $userId) {
                throw new \DomainException('not_found');
            }
            if ($attempt['status'] !== 'in_progress') {
                // Duplicate submission: return the already-scored attempt unchanged.
                return $attempt;
            }
            $late = time() > strtotime($attempt['expires_at'] . ' UTC') + self::GRACE_SECONDS;

            $questions = db()->fetchAll(
                'SELECT q.* FROM quiz_attempt_questions aq JOIN questions q ON q.id = aq.question_id WHERE aq.attempt_id = ?',
                [$attemptId]
            );
            $score = 0.0;
            $max = 0;
            foreach ($questions as $question) {
                $given = $late ? null : ($responses[$question['id']] ?? null);
                [$correct, $fraction] = self::scoreQuestion($question, $given);
                $points = (int) $question['points'];
                $awarded = round($points * $fraction, 2);
                $max += $points;
                $score += $awarded;
                db()->insert('quiz_responses', [
                    'attempt_id' => $attemptId,
                    'question_id' => (int) $question['id'],
                    'response' => json_encode(self::sanitizeResponse($given), JSON_UNESCAPED_UNICODE),
                    'is_correct' => $correct ? 1 : 0,
                    'points_awarded' => $awarded,
                ]);
            }
            $quiz = self::find((int) $attempt['quiz_id']);
            $percent = $max > 0 ? (int) floor($score * 100 / $max) : 0;
            $required = (int) $quiz['pass_percent'];
            if ($quiz['kind'] === 'final_exam') {
                $required = max($required, (int) db()->value('SELECT min_exam_score FROM courses WHERE id = ?', [(int) $quiz['course_id']]));
            }
            $passed = !$late && $percent >= $required;
            db()->update('quiz_attempts', [
                'status' => $late ? 'expired' : 'submitted',
                'submitted_at' => now(),
                'score_points' => (int) floor($score),
                'max_points' => $max,
                'percent' => $percent,
                'passed' => $passed ? 1 : 0,
            ], ['id' => $attemptId]);

            ActivityService::record($userId, $passed ? 'quiz_passed' : 'quiz_submitted', (int) $quiz['id']);
            return db()->fetch('SELECT * FROM quiz_attempts WHERE id = ?', [$attemptId]);
        });

        $quiz = self::find((int) $attempt['quiz_id']);
        ProgressService::recalculate($userId, (int) $quiz['course_id']);
        AchievementService::evaluate($userId);
        return $attempt;
    }

    /**
     * @return array{0: bool, 1: float} [fully correct, fraction of points]
     */
    public static function scoreQuestion(array $question, mixed $given): array
    {
        $spec = json_decode((string) ($question['answer_spec'] ?? ''), true) ?: [];
        $options = db()->fetchAll('SELECT id, is_correct, match_key FROM answer_options WHERE question_id = ?', [(int) $question['id']]);
        $correctIds = array_map(fn ($o) => (string) $o['id'], array_filter($options, fn ($o) => (int) $o['is_correct'] === 1));

        switch ($question['question_type']) {
            case 'single':
            case 'true_false':
            case 'scenario':
                $ok = is_scalar($given) && in_array((string) $given, $correctIds, true);
                return [$ok, $ok ? 1.0 : 0.0];
            case 'multiple':
                $ok = AnswerChecker::multi($given, ['correct' => $correctIds]);
                return [$ok, $ok ? 1.0 : 0.0];
            case 'matching':
                if (!is_array($given)) {
                    return [false, 0.0];
                }
                $pairs = array_filter($options, fn ($o) => $o['match_key'] !== null);
                if (!$pairs) {
                    return [false, 0.0];
                }
                $right = 0;
                foreach ($pairs as $o) {
                    if (isset($given[$o['id']]) && is_scalar($given[$o['id']]) && (string) $given[$o['id']] === (string) $o['match_key']) {
                        $right++;
                    }
                }
                return [$right === count($pairs), $right / count($pairs)];
            case 'numeric':
                $ok = AnswerChecker::number($given, $spec);
                return [$ok, $ok ? 1.0 : 0.0];
            case 'text':
                $ok = is_string($given) && AnswerChecker::text(mb_substr($given, 0, 500), $spec);
                return [$ok, $ok ? 1.0 : 0.0];
        }
        return [false, 0.0];
    }

    private static function sanitizeResponse(mixed $given): mixed
    {
        if (is_string($given)) {
            return mb_substr($given, 0, 500);
        }
        if (is_array($given)) {
            $out = [];
            foreach (array_slice($given, 0, 20, true) as $k => $v) {
                if (is_scalar($v)) {
                    $out[(string) $k] = mb_substr((string) $v, 0, 100);
                }
            }
            return $out;
        }
        return is_scalar($given) ? $given : null;
    }

    public static function canShowExplanations(array $quiz, array $attempt): bool
    {
        return match ($quiz['show_explanations']) {
            'never' => false,
            'after_pass' => (int) $attempt['passed'] === 1,
            default => true,
        };
    }

    public static function secureShuffle(array &$items): void
    {
        for ($i = count($items) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }
    }
}
