<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\CourseRepository;
use App\Repositories\LabRepository;
use App\Repositories\PathRepository;
use App\Services\AccessService;
use App\Services\CompletionService;
use App\Services\Localizer;
use App\Services\ProgressService;

final class CatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'path' => (string) $request->query('path', ''),
            'level' => (string) $request->query('level', ''),
            'access' => (string) $request->query('access', ''),
            'q' => (string) $request->query('q', ''),
        ];
        $result = CourseRepository::catalog($filters, max(1, (int) $request->query('page', 1)));
        return view('catalog/index', [
            'paths' => PathRepository::all(),
            'result' => $result,
            'filters' => $filters,
            'meta' => $this->meta(__('nav.catalog'), __('catalog.lead')),
        ]);
    }

    public function path(Request $request): Response
    {
        $path = PathRepository::findBySlug((string) $request->param('slug'));
        if (!$path) {
            throw HttpException::notFound();
        }
        $courses = PathRepository::courses((int) $path['id']);
        foreach ($courses as &$course) {
            $course['syllabus'] = CourseRepository::syllabus((int) $course['id'], false);
            $course['all_modules'] = Localizer::attach(
                db()->fetchAll('SELECT * FROM modules WHERE course_id = ? AND status <> "archived" ORDER BY sort_order', [(int) $course['id']]),
                'module_translations', 'module_id', 't', null, true
            );
        }
        $labs = LabRepository::published(['path' => $path['slug']]);
        return view('catalog/path', [
            'path' => $path,
            'courses' => $courses,
            'labs' => $labs,
            'meta' => $this->meta($path['t']['title'], $path['t']['tagline']),
        ]);
    }

    public function course(Request $request): Response
    {
        $preview = auth()->can('content.preview');
        $course = CourseRepository::findBySlug((string) $request->param('slug'), $preview);
        if (!$course) {
            throw HttpException::notFound();
        }
        // Show every non-archived module so learners can see the full syllabus;
        // lessons that are not yet published are listed as "in development".
        $modules = Localizer::attach(
            db()->fetchAll('SELECT * FROM modules WHERE course_id = ? AND status <> "archived" ORDER BY sort_order, id', [(int) $course['id']]),
            'module_translations', 'module_id', 't', null, $preview
        );
        $syllabus = array_column(CourseRepository::syllabus((int) $course['id'], $preview), null, 'id');
        foreach ($modules as &$module) {
            $module['lessons'] = $syllabus[$module['id']]['lessons'] ?? [];
            $module['topics'] = Localizer::lines($module['t']['topics'] ?? '');
        }
        unset($module);

        $userId = auth()->id();
        $enrolled = $userId ? AccessService::isEnrolled($userId, (int) $course['id']) : false;
        $accessSource = $userId ? AccessService::accessSource($userId, $course) : null;
        $completed = $userId ? ProgressService::completedLessonIds($userId, (int) $course['id']) : [];
        $status = $userId && $enrolled ? CompletionService::evaluate($userId, (int) $course['id']) : null;
        $progress = $userId ? db()->fetch('SELECT * FROM course_progress WHERE user_id = ? AND course_id = ?', [$userId, (int) $course['id']]) : null;
        $next = $userId && $enrolled ? ProgressService::nextLesson($userId, (int) $course['id']) : null;
        $quizzes = Localizer::attach(
            db()->fetchAll('SELECT * FROM quizzes WHERE course_id = ? AND status = "published" ORDER BY kind = "final_exam", id', [(int) $course['id']]),
            'quiz_translations', 'quiz_id'
        );
        $certificate = $userId ? db()->fetch('SELECT * FROM certificates WHERE user_id = ? AND course_id = ?', [$userId, (int) $course['id']]) : null;

        $structured = [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $course['t']['title'],
            'description' => $course['t']['subtitle'],
            'provider' => ['@type' => 'Organization', 'name' => 'Cybersecurity-Academy', 'sameAs' => url('/')],
            'inLanguage' => ['en', 'fr'],
        ];

        return view('catalog/course', [
            'course' => $course,
            'modules' => $modules,
            'labs' => LabRepository::forCourse((int) $course['id']),
            'quizzes' => $quizzes,
            'enrolled' => $enrolled,
            'accessSource' => $accessSource,
            'completed' => $completed,
            'status' => $status,
            'progress' => $progress,
            'next' => $next,
            'certificate' => $certificate,
            'meta' => $this->meta($course['t']['title'], $course['t']['subtitle'], ['structured' => $structured, 'og_type' => 'article']),
        ]);
    }

    public function enroll(Request $request): Response
    {
        $course = CourseRepository::findBySlug((string) $request->param('slug'));
        if (!$course) {
            throw HttpException::notFound();
        }
        if (!AccessService::enroll($this->userId(), $course)) {
            flash('info', __('course.needs_access'));
            return redirect(route('pricing') . '#course-' . $course['id']);
        }
        flash('success', __('course.enrolled', ['title' => $course['t']['title']]));
        $next = ProgressService::nextLesson($this->userId(), (int) $course['id']);
        if ($next) {
            return redirect(route('lessons.show', ['course' => $course['slug'], 'module' => $next['module_slug'], 'lesson' => $next['slug']]));
        }
        return redirect(route('courses.show', ['slug' => $course['slug']]));
    }
}
