<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\CourseRepository;
use App\Services\AccessService;
use App\Services\ContentRenderer;
use App\Services\Localizer;
use App\Services\ProgressService;
use App\Services\UploadService;

final class LessonController extends Controller
{
    public function show(Request $request): Response
    {
        $preview = auth()->can('content.preview');
        $course = CourseRepository::findBySlug((string) $request->param('course'), $preview);
        if (!$course) {
            throw HttpException::notFound();
        }
        $module = db()->fetch('SELECT * FROM modules WHERE course_id = ? AND slug = ?', [(int) $course['id'], (string) $request->param('module')]);
        if (!$module || (!$preview && $module['status'] !== 'published')) {
            throw HttpException::notFound();
        }
        $lesson = db()->fetch('SELECT * FROM lessons WHERE module_id = ? AND slug = ?', [(int) $module['id'], (string) $request->param('lesson')]);
        if (!$lesson) {
            throw HttpException::notFound();
        }
        $userId = $this->userId();
        if (!AccessService::canViewLesson($userId, $lesson, $course)) {
            if ($lesson['status'] !== 'published') {
                throw HttpException::notFound();
            }
            flash('info', AccessService::canAccess($userId, $course) ? __('lesson.enroll_first') : __('course.needs_access'));
            return redirect(route('courses.show', ['slug' => $course['slug']]));
        }

        $translations = [];
        foreach (db()->fetchAll('SELECT * FROM lesson_translations WHERE lesson_id = ?', [(int) $lesson['id']]) as $row) {
            $translations[$row['locale']] = $row;
        }
        $t = Localizer::pick($translations, null, $preview);
        if (!$t) {
            throw HttpException::notFound();
        }
        $module = Localizer::attach([$module], 'module_translations', 'module_id', 't', null, $preview)[0];

        ProgressService::startLesson($userId, $lesson, (int) $course['id']);
        $renderer = new ContentRenderer();
        $html = $renderer->render($t['content']);

        $syllabus = CourseRepository::syllabus((int) $course['id'], $preview);
        $flat = [];
        foreach ($syllabus as $m) {
            foreach ($m['lessons'] as $l) {
                $flat[] = $l + ['module_slug' => $m['slug']];
            }
        }
        $index = array_search((int) $lesson['id'], array_map(fn ($l) => (int) $l['id'], $flat), true);
        $prev = $index !== false && $index > 0 ? $flat[$index - 1] : null;
        $next = $index !== false && $index < count($flat) - 1 ? $flat[$index + 1] : null;

        $progress = db()->fetch('SELECT * FROM lesson_progress WHERE user_id = ? AND lesson_id = ?', [$userId, (int) $lesson['id']]);
        $resources = db()->fetchAll(
            'SELECT * FROM lesson_resources WHERE lesson_id = ? AND (locale IS NULL OR locale = ?) ORDER BY id',
            [(int) $lesson['id'], $t['_locale']]
        );
        $moduleQuiz = db()->fetch('SELECT * FROM quizzes WHERE module_id = ? AND status = "published" ORDER BY id LIMIT 1', [(int) $module['id']]);

        return view('lessons/show', [
            'course' => $course,
            'module' => $module,
            'lesson' => $lesson,
            't' => $t,
            'html' => $html,
            'toc' => $renderer->toc($html),
            'objectives' => Localizer::lines($t['objectives']),
            'prerequisites' => Localizer::lines($t['prerequisites']),
            'assignment' => $renderer->render($t['assignment']),
            'criteria' => $renderer->render($t['completion_criteria']),
            'references' => $renderer->render($t['references_md']),
            'syllabus' => $syllabus,
            'completed' => ProgressService::completedLessonIds($userId, (int) $course['id']),
            'prev' => $prev,
            'next' => $next,
            'progress' => $progress,
            'enrolled' => AccessService::isEnrolled($userId, (int) $course['id']),
            'resources' => $resources,
            'moduleQuiz' => $moduleQuiz,
            'minSeconds' => (int) setting('lesson_min_seconds', 45),
            'meta' => $this->meta($t['title'] . ' — ' . $course['t']['title'], $t['summary']) + ['robots' => 'noindex'],
        ]);
    }

    public function complete(Request $request): Response
    {
        [$lesson, $course] = $this->resolve((int) $request->param('id'));
        $result = ProgressService::completeLesson($this->userId(), $lesson, $course);
        $message = match ($result) {
            ProgressService::COMPLETE_OK => __('lesson.completed'),
            ProgressService::COMPLETE_TOO_SOON => __('lesson.too_soon', ['seconds' => (int) setting('lesson_min_seconds', 45)]),
            default => __('lesson.enroll_first'),
        };
        if ($request->wantsJson()) {
            $progress = db()->fetch('SELECT percent FROM course_progress WHERE user_id = ? AND course_id = ?', [$this->userId(), (int) $course['id']]);
            return Response::json(['status' => $result, 'message' => $message, 'percent' => (int) ($progress['percent'] ?? 0)], $result === 'ok' ? 200 : 422);
        }
        flash($result === ProgressService::COMPLETE_OK ? 'success' : 'error', $message);
        return back();
    }

    public function heartbeat(Request $request): Response
    {
        [$lesson] = $this->resolve((int) $request->param('id'));
        ProgressService::heartbeat($this->userId(), $lesson, $request->int('seconds'));
        return Response::json(['ok' => true]);
    }

    public function download(Request $request): Response
    {
        $resource = db()->fetch('SELECT * FROM lesson_resources WHERE id = ?', [(int) $request->param('id')]);
        if (!$resource) {
            throw HttpException::notFound();
        }
        // Authorise against the parent lesson or lab; never by URL alone.
        if ($resource['lesson_id']) {
            [$lesson, $course] = $this->resolve((int) $resource['lesson_id']);
        } elseif ($resource['lab_id']) {
            $lab = db()->fetch('SELECT * FROM labs WHERE id = ?', [(int) $resource['lab_id']]);
            if (!$lab || ($lab['status'] !== 'published' && !auth()->can('content.preview'))) {
                throw HttpException::notFound();
            }
        }
        $path = str_starts_with($resource['storage_path'], 'dataset:')
            ? \App\Services\LabService::datasetPath(substr($resource['storage_path'], 8))
            : UploadService::absolutePath($resource['storage_path']);
        if (!$path) {
            throw HttpException::notFound();
        }
        return Response::file($path, $resource['original_name'], $resource['mime_type']);
    }

    /**
     * @return array{0: array, 1: array} lesson and course, after access checks
     */
    private function resolve(int $lessonId): array
    {
        $lesson = db()->fetch('SELECT * FROM lessons WHERE id = ?', [$lessonId]);
        if (!$lesson) {
            throw HttpException::notFound();
        }
        $course = db()->fetch('SELECT c.* FROM courses c JOIN modules m ON m.course_id = c.id WHERE m.id = ?', [(int) $lesson['module_id']]);
        if (!$course || !AccessService::canViewLesson($this->userId(), $lesson, $course)) {
            throw HttpException::forbidden();
        }
        return [$lesson, $course];
    }
}
