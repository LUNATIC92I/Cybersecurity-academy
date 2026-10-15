<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuditLogger;
use App\Services\UploadService;

final class LessonController extends AdminController
{
    private const FIELDS = ['title', 'chapter_title', 'summary', 'objectives', 'prerequisites', 'content', 'assignment', 'completion_criteria', 'references_md', 'translation_status'];

    public function create(Request $request): Response
    {
        $module = db()->fetch('SELECT * FROM modules WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        return $this->render('lessons/form', ['module' => $module, 'lesson' => null, 'translations' => [], 'resources' => []]);
    }

    public function store(Request $request): Response
    {
        $module = db()->fetch('SELECT * FROM modules WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        $data = $this->validateLesson($request, null);
        if (db()->value('SELECT COUNT(*) FROM lessons WHERE module_id = ? AND slug = ?', [(int) $module['id'], $data['slug']])) {
            throw new \App\Core\ValidationException(['slug' => __('validation.unique', ['field' => __('field.slug')])], $request->all());
        }
        $id = db()->transaction(function () use ($module, $data, $request) {
            $id = db()->insert('lessons', $data + ['module_id' => (int) $module['id']]);
            $this->saveTranslations('lesson_translations', 'lesson_id', $id, $request->array('t'), self::FIELDS);
            return $id;
        });
        AuditLogger::log('lesson.created', 'lesson', $id);
        flash('success', __('admin.saved'));
        return redirect(route('admin.lessons.edit', ['id' => $id]));
    }

    public function edit(Request $request): Response
    {
        $lesson = db()->fetch('SELECT * FROM lessons WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        $module = db()->fetch('SELECT * FROM modules WHERE id = ?', [(int) $lesson['module_id']]);
        $course = db()->fetch('SELECT * FROM courses WHERE id = ?', [(int) $module['course_id']]);
        return $this->render('lessons/form', [
            'module' => $module,
            'course' => $course,
            'lesson' => $lesson,
            'translations' => $this->translationsFor('lesson_translations', 'lesson_id', (int) $lesson['id']),
            'resources' => db()->fetchAll('SELECT * FROM lesson_resources WHERE lesson_id = ? ORDER BY id', [(int) $lesson['id']]),
        ]);
    }

    public function update(Request $request): Response
    {
        $lesson = db()->fetch('SELECT * FROM lessons WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        $data = $this->validateLesson($request, $lesson);
        db()->transaction(function () use ($lesson, $data, $request) {
            unset($data['slug']);
            db()->update('lessons', $data, ['id' => (int) $lesson['id']]);
            $this->saveTranslations('lesson_translations', 'lesson_id', (int) $lesson['id'], $request->array('t'), self::FIELDS);
            $reviewed = $request->array('t');
            foreach (['en', 'fr'] as $loc) {
                if (($reviewed[$loc]['translation_status'] ?? '') === 'published') {
                    db()->query('UPDATE lesson_translations SET reviewed_by = ? WHERE lesson_id = ? AND locale = ?', [auth()->id(), (int) $lesson['id'], $loc]);
                }
            }
        });
        AuditLogger::log('lesson.updated', 'lesson', (int) $lesson['id'], ['status' => $data['status']]);
        flash('success', __('admin.saved'));
        return redirect(route('admin.lessons.edit', ['id' => $lesson['id']]));
    }

    public function upload(Request $request): Response
    {
        $lesson = db()->fetch('SELECT * FROM lessons WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        $file = $request->file('file');
        $title = mb_substr($request->string('title'), 0, 190);
        if (!$file || $title === '') {
            flash('error', __('admin.upload.missing'));
            return redirect(route('admin.lessons.edit', ['id' => $lesson['id']]));
        }
        try {
            $stored = UploadService::store($file, 'resources');
        } catch (\DomainException $e) {
            flash('error', __('admin.' . str_replace('_', '.', $e->getMessage())));
            return redirect(route('admin.lessons.edit', ['id' => $lesson['id']]));
        }
        $locale = in_array($request->string('locale'), ['en', 'fr'], true) ? $request->string('locale') : null;
        $id = db()->insert('lesson_resources', [
            'lesson_id' => (int) $lesson['id'],
            'locale' => $locale,
            'title' => $title,
            'storage_path' => $stored['path'],
            'original_name' => $stored['original'],
            'mime_type' => $stored['mime'],
            'size_bytes' => $stored['size'],
            'uploaded_by' => auth()->id(),
        ]);
        AuditLogger::log('resource.uploaded', 'lesson_resource', $id, ['lesson_id' => (int) $lesson['id'], 'mime' => $stored['mime']]);
        flash('success', __('admin.saved'));
        return redirect(route('admin.lessons.edit', ['id' => $lesson['id']]));
    }

    public function deleteResource(Request $request): Response
    {
        $resource = db()->fetch('SELECT * FROM lesson_resources WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        if (!str_starts_with($resource['storage_path'], 'dataset:') && ($path = UploadService::absolutePath($resource['storage_path']))) {
            @unlink($path);
        }
        db()->delete('lesson_resources', ['id' => (int) $resource['id']]);
        AuditLogger::log('resource.deleted', 'lesson_resource', (int) $resource['id']);
        flash('success', __('admin.saved'));
        return $resource['lesson_id']
            ? redirect(route('admin.lessons.edit', ['id' => $resource['lesson_id']]))
            : redirect(route('admin.labs.edit', ['id' => $resource['lab_id']]));
    }

    private function validateLesson(Request $request, ?array $lesson): array
    {
        $rules = [
            'level' => 'required|in:beginner,intermediate,advanced',
            'duration_minutes' => 'required|integer|between:1,600',
            'sort_order' => 'integer',
            'chapter_number' => 'integer',
            'status' => 'required|in:draft,in_review,published,archived',
        ];
        if ($lesson === null) {
            $rules['slug'] = 'required|slug|max:120';
        }
        $data = $this->validate($request, $rules);
        $t = $request->array('t');
        if (empty($t['en']['title']) || empty($t['en']['content'])) {
            throw new \App\Core\ValidationException(['t_en_title' => __('admin.english_required')], $request->all());
        }
        return [
            'slug' => $data['slug'] ?? $lesson['slug'],
            'level' => $data['level'],
            'duration_minutes' => (int) $data['duration_minutes'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'chapter_number' => max(1, (int) ($data['chapter_number'] ?? 1)),
            'is_required' => $request->bool('is_required') ? 1 : 0,
            'is_preview' => $request->bool('is_preview') ? 1 : 0,
            'status' => $this->guardStatus($data['status'], $lesson['status'] ?? null),
        ];
    }
}
