<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\AccessService;
use App\Services\CompletionService;
use App\Services\LabService;
use App\Services\ProgressService;
use App\Services\QuizService;
use App\Repositories\LabRepository;
use Tests\TestCase;

final class LearningFlowTest extends TestCase
{
    private function freeCourse(): array
    {
        return db()->fetch("SELECT * FROM courses WHERE slug = 'soc-analyst-professional'");
    }

    public function test_lesson_cannot_be_completed_before_minimum_time(): void
    {
        $user = $this->actingAs($this->makeUser());
        $course = $this->freeCourse();
        AccessService::enroll((int) $user['id'], $course);
        $lesson = db()->fetch('SELECT l.* FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = ? AND l.status = "published" LIMIT 1', [(int) $course['id']]);
        ProgressService::startLesson((int) $user['id'], $lesson, (int) $course['id']);
        $result = ProgressService::completeLesson((int) $user['id'], $lesson, $course);
        $this->assertSame(ProgressService::COMPLETE_TOO_SOON, $result);
    }

    public function test_lesson_completes_after_minimum_time(): void
    {
        $user = $this->actingAs($this->makeUser());
        $course = $this->freeCourse();
        AccessService::enroll((int) $user['id'], $course);
        $lesson = db()->fetch('SELECT l.* FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = ? AND l.status = "published" LIMIT 1', [(int) $course['id']]);
        ProgressService::startLesson((int) $user['id'], $lesson, (int) $course['id']);
        db()->query('UPDATE lesson_progress SET started_at = UTC_TIMESTAMP() - INTERVAL 10 MINUTE WHERE user_id = ? AND lesson_id = ?', [(int) $user['id'], (int) $lesson['id']]);
        $this->assertSame(ProgressService::COMPLETE_OK, ProgressService::completeLesson((int) $user['id'], $lesson, $course));
    }

    public function test_lab_scoring_is_server_side(): void
    {
        $user = $this->actingAs($this->makeUser());
        AccessService::enroll((int) $user['id'], $this->freeCourse());
        $lab = LabRepository::findBySlug('soc-brute-force-investigation', true);
        $tasks = db()->fetchAll('SELECT id, task_type, answer_spec FROM lab_tasks WHERE lab_id = ?', [(int) $lab['id']]);
        $correct = [];
        foreach ($tasks as $t) {
            $s = json_decode($t['answer_spec'], true);
            $correct[$t['id']] = match ($t['task_type']) {
                'text' => $s['accepted'][0], 'number' => $s['value'], 'choice' => $s['correct'], 'multi' => $s['correct'], default => 'x',
            };
        }
        $attempt = LabService::submit((int) $user['id'], $lab, $correct);
        $this->assertSame(100, (int) $attempt['percent']);
        $this->assertSame(1, (int) $attempt['passed']);

        // Wrong answers score low.
        $wrong = array_map(fn ($v) => is_array($v) ? [] : 'definitely-wrong', $correct);
        $attempt2 = LabService::submit((int) $user['id'], $lab, $wrong);
        $this->assertLessThan(70, (int) $attempt2['percent']);
    }

    public function test_exam_scores_correctly_and_double_submit_is_safe(): void
    {
        $user = $this->actingAs($this->makeUser());
        $course = $this->freeCourse();
        AccessService::enroll((int) $user['id'], $course);
        $quiz = db()->fetch("SELECT * FROM quizzes WHERE slug = 'soc-analyst-final-exam'");
        $attempt = QuizService::start((int) $user['id'], $quiz);
        $resp = $this->correctResponses($attempt);
        $result = QuizService::submit((int) $user['id'], (int) $attempt['id'], $resp);
        $this->assertSame(100, (int) $result['percent']);
        $this->assertSame(1, (int) $result['passed']);
        // Second submit returns the same attempt unchanged (no re-scoring).
        $again = QuizService::submit((int) $user['id'], (int) $attempt['id'], []);
        $this->assertSame((int) $result['percent'], (int) $again['percent']);
    }

    public function test_certificate_only_after_all_requirements(): void
    {
        $user = $this->actingAs($this->makeUser());
        $course = $this->freeCourse();
        AccessService::enroll((int) $user['id'], $course);
        // Exam only — not enough.
        $quiz = db()->fetch("SELECT * FROM quizzes WHERE slug = 'soc-analyst-final-exam'");
        $attempt = QuizService::start((int) $user['id'], $quiz);
        QuizService::submit((int) $user['id'], (int) $attempt['id'], $this->correctResponses($attempt));
        $this->assertNull(db()->fetch('SELECT id FROM certificates WHERE user_id = ? AND course_id = ?', [(int) $user['id'], (int) $course['id']]), 'No certificate before lessons and labs are done.');

        // Complete lessons and labs.
        foreach (db()->fetchAll('SELECT l.* FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = ? AND l.status = "published" AND l.is_required = 1', [(int) $course['id']]) as $lesson) {
            ProgressService::startLesson((int) $user['id'], $lesson, (int) $course['id']);
            db()->query('UPDATE lesson_progress SET started_at = UTC_TIMESTAMP() - INTERVAL 10 MINUTE WHERE user_id = ? AND lesson_id = ?', [(int) $user['id'], (int) $lesson['id']]);
            ProgressService::completeLesson((int) $user['id'], $lesson, $course);
        }
        foreach (db()->fetchAll('SELECT * FROM labs WHERE course_id = ? AND is_required = 1 AND status = "published"', [(int) $course['id']]) as $labRow) {
            $lab = LabRepository::findBySlug($labRow['slug'], true);
            $ans = [];
            foreach (db()->fetchAll('SELECT id, task_type, answer_spec FROM lab_tasks WHERE lab_id = ?', [(int) $lab['id']]) as $t) {
                $s = json_decode($t['answer_spec'], true);
                $ans[$t['id']] = match ($t['task_type']) { 'text' => $s['accepted'][0], 'number' => $s['value'], 'choice' => $s['correct'], 'multi' => $s['correct'], default => 'x' };
            }
            LabService::submit((int) $user['id'], $lab, $ans);
        }
        $status = CompletionService::evaluate((int) $user['id'], (int) $course['id']);
        $this->assertTrue($status['eligible']);
        $cert = db()->fetch('SELECT * FROM certificates WHERE user_id = ? AND course_id = ?', [(int) $user['id'], (int) $course['id']]);
        $this->assertNotNull($cert, 'Certificate must be issued once all requirements are met.');
        $this->assertMatchesRegularExpression('/^CA-SOC-2026-[A-F0-9]{8}$/', $cert['certificate_number']);
        return;
    }

    private function correctResponses(array $attempt): array
    {
        $resp = [];
        foreach (QuizService::attemptQuestions($attempt, true) as $q) {
            $opts = db()->fetchAll('SELECT id, is_correct, match_key FROM answer_options WHERE question_id = ?', [(int) $q['id']]);
            $spec = $q['answer_spec'];
            $resp[$q['id']] = match ($q['question_type']) {
                'single', 'true_false', 'scenario' => (string) array_values(array_filter($opts, fn ($o) => $o['is_correct']))[0]['id'],
                'multiple' => array_values(array_map(fn ($o) => (string) $o['id'], array_filter($opts, fn ($o) => $o['is_correct']))),
                'matching' => array_reduce($opts, function ($c, $o) { if ($o['match_key'] !== null) { $c[$o['id']] = $o['match_key']; } return $c; }, []),
                'numeric' => $spec['value'], 'text' => $spec['accepted'][0], default => null,
            };
        }
        return $resp;
    }
}
