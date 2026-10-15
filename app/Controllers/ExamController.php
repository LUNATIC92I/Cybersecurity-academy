<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\CourseRepository;
use App\Services\Localizer;
use App\Services\QuizService;

final class ExamController extends Controller
{
    public function index(Request $request): Response
    {
        $quizzes = db()->fetchAll(
            'SELECT q.*, c.slug AS course_slug FROM quizzes q JOIN courses c ON c.id = q.course_id
             WHERE q.status = "published" AND c.status = "published" ORDER BY c.id, q.kind = "final_exam", q.id'
        );
        $quizzes = Localizer::attach($quizzes, 'quiz_translations', 'quiz_id');
        $courses = array_column(CourseRepository::catalog([], 1, 100)['items'], null, 'id');
        $best = [];
        if (auth()->check()) {
            foreach (db()->fetchAll('SELECT quiz_id, MAX(percent) AS best, MAX(passed) AS passed FROM quiz_attempts WHERE user_id = ? AND status = "submitted" GROUP BY quiz_id', [auth()->id()]) as $row) {
                $best[(int) $row['quiz_id']] = $row;
            }
        }
        $counts = [];
        foreach (db()->fetchAll('SELECT question_type, COUNT(*) AS n FROM questions WHERE status = "active" GROUP BY question_type') as $row) {
            $counts[$row['question_type']] = (int) $row['n'];
        }
        return view('exams/index', [
            'quizzes' => $quizzes,
            'courses' => $courses,
            'best' => $best,
            'typeCounts' => $counts,
            'meta' => $this->meta(__('nav.assessments'), __('exams.lead')),
        ]);
    }

    public function show(Request $request): Response
    {
        $quiz = $this->findQuiz((string) $request->param('slug'));
        $userId = $this->userId();
        $course = CourseRepository::find((int) $quiz['course_id']);
        return view('exams/show', [
            'quiz' => $quiz,
            'course' => $course,
            'eligibility' => QuizService::eligibility($userId, $quiz),
            'active' => QuizService::activeAttempt($userId, (int) $quiz['id']),
            'attempts' => QuizService::attempts($userId, (int) $quiz['id']),
            'meta' => $this->meta($quiz['t']['title']) + ['robots' => 'noindex'],
        ]);
    }

    public function start(Request $request): Response
    {
        $quiz = $this->findQuiz((string) $request->param('slug'));
        $this->throttle('quiz-start:' . $this->userId(), 20, 3600);
        try {
            $attempt = QuizService::start($this->userId(), $quiz);
        } catch (\DomainException $e) {
            flash('error', __('exams.cannot_start.' . $e->getMessage()));
            return redirect(route('assessments.show', ['slug' => $quiz['slug']]));
        }
        return redirect(route('attempts.take', ['id' => $attempt['id']]));
    }

    public function take(Request $request): Response
    {
        $attempt = $this->ownAttempt((int) $request->param('id'));
        QuizService::expireStale($this->userId(), (int) $attempt['quiz_id']);
        $attempt = $this->ownAttempt((int) $attempt['id']);
        if ($attempt['status'] !== 'in_progress') {
            return redirect(route('attempts.results', ['id' => $attempt['id']]));
        }
        $quiz = $this->quizById((int) $attempt['quiz_id']);
        return view('exams/take', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'questions' => QuizService::attemptQuestions($attempt, false),
            'remaining' => max(0, strtotime($attempt['expires_at'] . ' UTC') - time()),
            'meta' => $this->meta($quiz['t']['title']) + ['robots' => 'noindex'],
        ]);
    }

    public function submit(Request $request): Response
    {
        $attempt = $this->ownAttempt((int) $request->param('id'));
        try {
            QuizService::submit($this->userId(), (int) $attempt['id'], $request->array('responses'));
        } catch (\DomainException) {
            throw HttpException::notFound();
        }
        return redirect(route('attempts.results', ['id' => $attempt['id']]));
    }

    public function results(Request $request): Response
    {
        $attempt = $this->ownAttempt((int) $request->param('id'));
        if ($attempt['status'] === 'in_progress') {
            return redirect(route('attempts.take', ['id' => $attempt['id']]));
        }
        $quiz = $this->quizById((int) $attempt['quiz_id']);
        $course = CourseRepository::find((int) $quiz['course_id']);
        $showAnswers = QuizService::canShowExplanations($quiz, $attempt);
        $certificate = db()->fetch('SELECT * FROM certificates WHERE user_id = ? AND course_id = ?', [$this->userId(), (int) $quiz['course_id']]);
        return view('exams/results', [
            'quiz' => $quiz,
            'course' => $course,
            'attempt' => $attempt,
            'questions' => QuizService::attemptQuestions($attempt, true),
            'showAnswers' => $showAnswers,
            'eligibility' => QuizService::eligibility($this->userId(), $quiz),
            'certificate' => $certificate,
            'renderer' => new \App\Services\ContentRenderer(),
            'meta' => $this->meta($quiz['t']['title']) + ['robots' => 'noindex'],
        ]);
    }

    private function findQuiz(string $slug): array
    {
        $quiz = QuizService::findBySlug($slug);
        if (!$quiz || ($quiz['status'] !== 'published' && !auth()->can('content.preview'))) {
            throw HttpException::notFound();
        }
        return Localizer::attach([$quiz], 'quiz_translations', 'quiz_id')[0];
    }

    private function quizById(int $id): array
    {
        return Localizer::attach([QuizService::find($id)], 'quiz_translations', 'quiz_id')[0];
    }

    private function ownAttempt(int $id): array
    {
        $attempt = db()->fetch('SELECT * FROM quiz_attempts WHERE id = ?', [$id]);
        if (!$attempt || (int) $attempt['user_id'] !== $this->userId()) {
            throw HttpException::notFound();
        }
        return $attempt;
    }
}
