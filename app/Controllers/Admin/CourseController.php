<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuditLogger;
use App\Services\Localizer;

final class CourseController extends AdminController
{
    private const COURSE_FIELDS = ['title', 'subtitle', 'description', 'outcomes', 'audience', 'certificate_title', 'translation_status'];
    private const MODULE_FIELDS = ['title', 'summary', 'topics', 'translation_status'];

    public function index(Request $request): Response
    {
        $courses = db()->fetchAll(
            'SELECT c.*, ct.title, (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) AS modules,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS enrolments,
                    (SELECT translation_status FROM course_translations f WHERE f.course_id = c.id AND f.locale = "fr") AS fr_status
             FROM courses c LEFT JOIN course_translations ct ON ct.course_id = c.id AND ct.locale = "en" ORDER BY c.id'
        );
        return $this->render('courses/index', ['courses' => $courses]);
    }

    public function create(Request $request): Response
    {
        return $this->render('courses/form', [
            'course' => null,
            'translations' => [],
            'paths' => $this->paths(),
            'coursePaths' => [],
        ]);
    }

    public function store(Request $request): Response
    {
        $data = $this->validateCourse($request);
        $id = db()->transaction(function () use ($data, $request) {
            $id = db()->insert('courses', $data + ['created_by' => auth()->id()]);
            $this->saveTranslations('course_translations', 'course_id', $id, $request->array('t'), self::COURSE_FIELDS);
            $this->syncPaths($id, $request->array('paths'));
            return $id;
        });
        AuditLogger::log('course.created', 'course', $id, ['slug' => $data['slug']]);
        flash('success', __('admin.saved'));
        return redirect(route('admin.courses.edit', ['id' => $id]));
    }

    public function edit(Request $request): Response
    {
        $course = db()->fetch('SELECT * FROM courses WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        $modules = Localizer::attach(
            db()->fetchAll('SELECT m.*, (SELECT COUNT(*) FROM lessons l WHERE l.module_id = m.id) AS lesson_count FROM modules m WHERE course_id = ? ORDER BY sort_order, id', [(int) $course['id']]),
            'module_translations', 'module_id', 't', 'en', true
        );
        return $this->render('courses/form', [
            'course' => $course,
            'translations' => $this->translationsFor('course_translations', 'course_id', (int) $course['id']),
            'modules' => $modules,
            'paths' => $this->paths(),
            'coursePaths' => array_map('intval', db()->column('SELECT path_id FROM learning_path_courses WHERE course_id = ?', [(int) $course['id']])),
        ]);
    }

    public function update(Request $request): Response
    {
        $course = db()->fetch('SELECT * FROM courses WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        $data = $this->validateCourse($request, (int) $course['id'], $course['status']);
        db()->transaction(function () use ($course, $data, $request) {
            if ($data['status'] === 'published' && !$course['published_at']) {
                $data['published_at'] = now();
            }
            db()->update('courses', $data, ['id' => (int) $course['id']]);
            $this->saveTranslations('course_translations', 'course_id', (int) $course['id'], $request->array('t'), self::COURSE_FIELDS);
            $this->syncPaths((int) $course['id'], $request->array('paths'));
        });
        AuditLogger::log('course.updated', 'course', (int) $course['id'], ['status' => $data['status'], 'min_exam_score' => $data['min_exam_score'], 'require_all_labs' => $data['require_all_labs']]);
        flash('success', __('admin.saved'));
        return redirect(route('admin.courses.edit', ['id' => $course['id']]));
    }

    public function storeModule(Request $request): Response
    {
        $course = db()->fetch('SELECT * FROM courses WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        $data = $this->validate($request, [
            'slug' => 'required|slug|max:120',
            'level' => 'required|in:beginner,intermediate,advanced',
            'sort_order' => 'integer',
        ]);
        if (db()->value('SELECT COUNT(*) FROM modules WHERE course_id = ? AND slug = ?', [(int) $course['id'], $data['slug']])) {
            session()->flash('errors', ['slug' => __('validation.unique', ['field' => __('field.slug')])]);
            return redirect(route('admin.courses.edit', ['id' => $course['id']]));
        }
        $id = db()->insert('modules', [
            'course_id' => (int) $course['id'],
            'slug' => $data['slug'],
            'level' => $data['level'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'status' => 'draft',
        ]);
        $this->saveTranslations('module_translations', 'module_id', $id, $request->array('t'), self::MODULE_FIELDS);
        AuditLogger::log('module.created', 'module', $id);
        flash('success', __('admin.saved'));
        return redirect(route('admin.modules.edit', ['id' => $id]));
    }

    public function editModule(Request $request): Response
    {
        $module = db()->fetch('SELECT * FROM modules WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        $lessons = Localizer::attach(
            db()->fetchAll('SELECT * FROM lessons WHERE module_id = ? ORDER BY sort_order, id', [(int) $module['id']]),
            'lesson_translations', 'lesson_id', 't', 'en', true
        );
        foreach ($lessons as &$lesson) {
            $lesson['fr_status'] = db()->value('SELECT translation_status FROM lesson_translations WHERE lesson_id = ? AND locale = "fr"', [(int) $lesson['id']]) ?? 'missing';
        }
        return $this->render('courses/module', [
            'module' => $module,
            'course' => db()->fetch('SELECT c.*, ct.title FROM courses c LEFT JOIN course_translations ct ON ct.course_id = c.id AND ct.locale = "en" WHERE c.id = ?', [(int) $module['course_id']]),
            'translations' => $this->translationsFor('module_translations', 'module_id', (int) $module['id']),
            'lessons' => $lessons,
        ]);
    }

    public function updateModule(Request $request): Response
    {
        $module = db()->fetch('SELECT * FROM modules WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        $data = $this->validate($request, [
            'level' => 'required|in:beginner,intermediate,advanced',
            'sort_order' => 'integer',
            'status' => 'required|in:draft,in_review,published,archived',
        ]);
        db()->update('modules', [
            'level' => $data['level'],
            'sort_order' => (int) $data['sort_order'],
            'status' => $this->guardStatus($data['status'], $module['status']),
        ], ['id' => (int) $module['id']]);
        $this->saveTranslations('module_translations', 'module_id', (int) $module['id'], $request->array('t'), self::MODULE_FIELDS);
        AuditLogger::log('module.updated', 'module', (int) $module['id']);
        flash('success', __('admin.saved'));
        return redirect(route('admin.modules.edit', ['id' => $module['id']]));
    }

    private function validateCourse(Request $request, ?int $id = null, ?string $currentStatus = null): array
    {
        $data = $this->validate($request, [
            'slug' => 'required|slug|max:120|unique:courses,slug' . ($id ? ',' . $id : ''),
            'level' => 'required|in:beginner,intermediate,advanced,all',
            'access_level' => 'required|in:free,premium',
            'price_cents' => 'integer',
            'estimated_hours' => 'integer',
            'status' => 'required|in:draft,in_review,published,archived',
            'min_exam_score' => 'required|integer|between:0,100',
        ]);
        $t = $request->array('t');
        if (empty($t['en']['title']) || empty($t['en']['description'])) {
            throw new \App\Core\ValidationException(['t_en_title' => __('admin.english_required')], $request->all());
        }
        return [
            'slug' => $data['slug'],
            'level' => $data['level'],
            'access_level' => $data['access_level'],
            'price_cents' => max(0, (int) $data['price_cents']),
            'estimated_hours' => max(0, (int) $data['estimated_hours']),
            'status' => $this->guardStatus($data['status'], $currentStatus),
            'is_featured' => $request->bool('is_featured') ? 1 : 0,
            'min_exam_score' => (int) $data['min_exam_score'],
            'require_all_labs' => $request->bool('require_all_labs') ? 1 : 0,
            'certificate_enabled' => $request->bool('certificate_enabled') ? 1 : 0,
        ];
    }

    private function syncPaths(int $courseId, array $pathIds): void
    {
        db()->delete('learning_path_courses', ['course_id' => $courseId]);
        foreach (array_unique(array_map('intval', $pathIds)) as $pathId) {
            if ($pathId > 0) {
                db()->query('INSERT IGNORE INTO learning_path_courses (path_id, course_id, sort_order) VALUES (?, ?, ?)', [$pathId, $courseId, $courseId]);
            }
        }
    }

    private function paths(): array
    {
        return db()->fetchAll('SELECT p.id, pt.title FROM learning_paths p JOIN learning_path_translations pt ON pt.path_id = p.id AND pt.locale = "en" ORDER BY p.sort_order');
    }
}
