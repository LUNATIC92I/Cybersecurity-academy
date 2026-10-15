<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\LabRepository;
use App\Repositories\PathRepository;
use App\Services\AccessService;
use App\Services\ContentRenderer;
use App\Services\LabService;
use App\Services\Localizer;

final class LabController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = ['type' => (string) $request->query('type', ''), 'path' => (string) $request->query('path', '')];
        $labs = LabRepository::published($filters);
        $passed = [];
        if (auth()->check()) {
            $passed = array_map('intval', db()->column('SELECT DISTINCT lab_id FROM lab_attempts WHERE user_id = ? AND passed = 1', [auth()->id()]));
        }
        return view('labs/index', [
            'labs' => $labs,
            'filters' => $filters,
            'paths' => PathRepository::all(),
            'passed' => $passed,
            'meta' => $this->meta(__('nav.labs'), __('labs.lead')),
        ]);
    }

    public function show(Request $request): Response
    {
        $lab = $this->findLab($request);
        $renderer = new ContentRenderer();
        $userId = auth()->id();
        $canAttempt = $userId !== null && $this->canAttempt($userId, $lab);
        $acknowledged = $userId !== null && ($lab['risk_level'] !== 'elevated' || LabService::hasAcknowledged($userId, (int) $lab['id']));
        $attempts = $userId ? LabService::attempts($userId, (int) $lab['id']) : [];
        $showSolution = $userId !== null && LabService::canSeeSolution($userId, (int) $lab['id']);
        $dataset = null;
        if ($lab['dataset_file'] && $canAttempt && $acknowledged) {
            $dataset = LabService::datasetPath($lab['dataset_file']) ? $lab['dataset_file'] : null;
        }
        $resources = db()->fetchAll('SELECT * FROM lesson_resources WHERE lab_id = ? AND (locale IS NULL OR locale = ?) ORDER BY id', [(int) $lab['id'], $lab['t']['_locale'] ?? locale()]);
        $course = $lab['course_id'] ? \App\Repositories\CourseRepository::find((int) $lab['course_id']) : null;

        $t = $lab['t'];
        $sections = [];
        foreach (['scenario', 'setup', 'instructions'] as $key) {
            $sections[$key] = $renderer->render($t[$key] ?? '');
        }
        return view('labs/show', [
            'lab' => $lab,
            'course' => $course,
            'sections' => $sections,
            'objectives' => Localizer::lines($t['objectives'] ?? ''),
            'tools' => Localizer::lines($t['tools'] ?? ''),
            'prerequisites' => Localizer::lines($t['prerequisites'] ?? ''),
            'expected' => $renderer->render($t['expected_results'] ?? ''),
            'scoring' => $renderer->render($t['scoring'] ?? ''),
            'solution' => $showSolution ? $renderer->render($t['solution'] ?? '') : null,
            'cleanup' => $renderer->render($t['cleanup'] ?? ''),
            'lessonsLearned' => $renderer->render($t['lessons_learned'] ?? ''),
            'tasks' => LabService::tasks((int) $lab['id']),
            'canAttempt' => $canAttempt,
            'acknowledged' => $acknowledged,
            'attempts' => $attempts,
            'lastResults' => $attempts ? (json_decode((string) $attempts[0]['results'], true) ?: []) : [],
            'lastAnswers' => $attempts ? (json_decode((string) $attempts[0]['answers'], true) ?: []) : [],
            'dataset' => $dataset,
            'resources' => $resources,
            'renderer' => $renderer,
            'meta' => $this->meta($t['title'], $t['summary']),
        ]);
    }

    public function acknowledge(Request $request): Response
    {
        $lab = $this->findLab($request);
        $this->validate($request, ['confirm_authorized' => 'accepted', 'confirm_isolated' => 'accepted']);
        LabService::acknowledge($this->userId(), (int) $lab['id'], $request->ip());
        flash('success', __('labs.acknowledged'));
        return redirect(route('labs.show', ['slug' => $lab['slug']]));
    }

    public function submit(Request $request): Response
    {
        $lab = $this->findLab($request);
        $userId = $this->userId();
        if (!$this->canAttempt($userId, $lab)) {
            throw HttpException::forbidden();
        }
        if ($lab['risk_level'] === 'elevated' && !LabService::hasAcknowledged($userId, (int) $lab['id'])) {
            flash('error', __('labs.ack_required'));
            return redirect(route('labs.show', ['slug' => $lab['slug']]));
        }
        $this->throttle('lab-submit:' . $userId . ':' . $lab['id'], 10, 600);
        try {
            $attempt = LabService::submit($userId, $lab, $request->array('answers'));
        } catch (\DomainException) {
            flash('error', __('labs.max_attempts'));
            return redirect(route('labs.show', ['slug' => $lab['slug']]));
        }
        flash(
            (int) $attempt['passed'] === 1 ? 'success' : 'info',
            __((int) $attempt['passed'] === 1 ? 'labs.passed' : 'labs.not_passed', ['score' => $attempt['percent'], 'pass' => $lab['pass_percent']])
        );
        return redirect(route('labs.show', ['slug' => $lab['slug']]) . '#results');
    }

    public function attempt(Request $request): Response
    {
        $lab = $this->findLab($request);
        $attempt = db()->fetch('SELECT * FROM lab_attempts WHERE id = ? AND lab_id = ?', [(int) $request->param('id'), (int) $lab['id']]);
        // Learners may only read their own attempts (IDOR protection).
        if (!$attempt || ((int) $attempt['user_id'] !== $this->userId() && !auth()->can('progress.view'))) {
            throw HttpException::notFound();
        }
        return Response::json([
            'attempt_no' => (int) $attempt['attempt_no'],
            'percent' => (int) $attempt['percent'],
            'passed' => (bool) $attempt['passed'],
            'results' => json_decode((string) $attempt['results'], true),
            'submitted_at' => $attempt['submitted_at'],
        ]);
    }

    public function dataset(Request $request): Response
    {
        $lab = $this->findLab($request);
        $path = $this->datasetFor($lab);
        return Response::file($path, basename($path), $this->mimeFor($path));
    }

    /**
     * Dataset rows for the in-browser log viewer (CSV parsed server-side).
     */
    public function datasetJson(Request $request): Response
    {
        $lab = $this->findLab($request);
        $path = $this->datasetFor($lab);
        if (!str_ends_with($path, '.csv')) {
            return Response::json(['columns' => [], 'rows' => [], 'raw' => mb_substr((string) file_get_contents($path), 0, 200000)]);
        }
        $handle = fopen($path, 'rb');
        $columns = fgetcsv($handle, null, ',', '"', '\\') ?: [];
        $rows = [];
        while (($row = fgetcsv($handle, null, ',', '"', '\\')) !== false && count($rows) < 5000) {
            $rows[] = $row;
        }
        fclose($handle);
        return Response::json(['columns' => $columns, 'rows' => $rows]);
    }

    private function datasetFor(array $lab): string
    {
        $userId = $this->userId();
        if (!$this->canAttempt($userId, $lab)
            || ($lab['risk_level'] === 'elevated' && !LabService::hasAcknowledged($userId, (int) $lab['id']))) {
            throw HttpException::forbidden();
        }
        $path = LabService::datasetPath($lab['dataset_file']);
        if (!$path) {
            throw HttpException::notFound();
        }
        return $path;
    }

    private function mimeFor(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'csv' => 'text/csv; charset=UTF-8',
            'json' => 'application/json',
            'eml' => 'message/rfc822',
            default => 'text/plain; charset=UTF-8',
        };
    }

    private function findLab(Request $request): array
    {
        $lab = LabRepository::findBySlug((string) $request->param('slug'), auth()->can('content.preview'));
        if (!$lab) {
            throw HttpException::notFound();
        }
        return $lab;
    }

    /**
     * Labs attached to a premium course require access to that course;
     * standalone labs are available to any signed-in learner.
     */
    private function canAttempt(int $userId, array $lab): bool
    {
        if (auth()->can('content.preview')) {
            return true;
        }
        if (!$lab['course_id']) {
            return true;
        }
        $course = db()->fetch('SELECT * FROM courses WHERE id = ?', [(int) $lab['course_id']]);
        return $course && AccessService::canAccess($userId, $course);
    }
}
