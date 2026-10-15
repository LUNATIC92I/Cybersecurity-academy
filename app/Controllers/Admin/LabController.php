<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\ValidationException;
use App\Services\AuditLogger;
use App\Services\UploadService;

final class LabController extends AdminController
{
    private const FIELDS = ['title', 'summary', 'objectives', 'scenario', 'tools', 'prerequisites', 'setup', 'instructions', 'expected_results', 'scoring', 'solution', 'cleanup', 'lessons_learned', 'translation_status'];
    private const TASK_TYPES = ['text', 'number', 'choice', 'multi', 'query'];

    public function index(Request $request): Response
    {
        $labs = db()->fetchAll(
            'SELECT l.*, t.title, (SELECT COUNT(*) FROM lab_tasks k WHERE k.lab_id = l.id) AS tasks,
                    (SELECT COUNT(*) FROM lab_attempts a WHERE a.lab_id = l.id) AS attempts,
                    (SELECT translation_status FROM lab_translations f WHERE f.lab_id = l.id AND f.locale = "fr") AS fr_status
             FROM labs l LEFT JOIN lab_translations t ON t.lab_id = l.id AND t.locale = "en" ORDER BY l.id'
        );
        return $this->render('labs/index', ['labs' => $labs]);
    }

    public function create(Request $request): Response
    {
        return $this->render('labs/form', $this->formData(null));
    }

    public function store(Request $request): Response
    {
        $data = $this->validateLab($request, null);
        $id = db()->transaction(function () use ($data, $request) {
            $id = db()->insert('labs', $data);
            $this->saveTranslations('lab_translations', 'lab_id', $id, $request->array('t'), self::FIELDS);
            return $id;
        });
        AuditLogger::log('lab.created', 'lab', $id);
        flash('success', __('admin.saved'));
        return redirect(route('admin.labs.edit', ['id' => $id]));
    }

    public function edit(Request $request): Response
    {
        $lab = db()->fetch('SELECT * FROM labs WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        return $this->render('labs/form', $this->formData($lab));
    }

    public function update(Request $request): Response
    {
        $lab = db()->fetch('SELECT * FROM labs WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        $data = $this->validateLab($request, $lab);
        db()->transaction(function () use ($lab, $data, $request) {
            db()->update('labs', $data, ['id' => (int) $lab['id']]);
            $this->saveTranslations('lab_translations', 'lab_id', (int) $lab['id'], $request->array('t'), self::FIELDS);
        });
        if (($file = $request->file('resource')) && $request->string('resource_title') !== '') {
            try {
                $stored = UploadService::store($file, 'labs');
                db()->insert('lesson_resources', [
                    'lab_id' => (int) $lab['id'],
                    'locale' => in_array($request->string('resource_locale'), ['en', 'fr'], true) ? $request->string('resource_locale') : null,
                    'title' => mb_substr($request->string('resource_title'), 0, 190),
                    'storage_path' => $stored['path'],
                    'original_name' => $stored['original'],
                    'mime_type' => $stored['mime'],
                    'size_bytes' => $stored['size'],
                    'uploaded_by' => auth()->id(),
                ]);
            } catch (\DomainException $e) {
                flash('error', __('admin.' . str_replace('_', '.', $e->getMessage())));
                return redirect(route('admin.labs.edit', ['id' => $lab['id']]));
            }
        }
        AuditLogger::log('lab.updated', 'lab', (int) $lab['id'], ['status' => $data['status']]);
        flash('success', __('admin.saved'));
        return redirect(route('admin.labs.edit', ['id' => $lab['id']]));
    }

    public function storeTask(Request $request): Response
    {
        $lab = db()->fetch('SELECT * FROM labs WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        [$task, $translations] = $this->validateTask($request);
        $id = db()->insert('lab_tasks', $task + ['lab_id' => (int) $lab['id']]);
        $this->saveTaskTranslations($id, $translations);
        AuditLogger::log('lab.task_created', 'lab_task', $id, ['lab_id' => (int) $lab['id']]);
        flash('success', __('admin.saved'));
        return redirect(route('admin.labs.edit', ['id' => $lab['id']]) . '#tasks');
    }

    public function updateTask(Request $request): Response
    {
        $task = db()->fetch('SELECT * FROM lab_tasks WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        [$data, $translations] = $this->validateTask($request);
        db()->update('lab_tasks', $data, ['id' => (int) $task['id']]);
        $this->saveTaskTranslations((int) $task['id'], $translations);
        AuditLogger::log('lab.task_updated', 'lab_task', (int) $task['id']);
        flash('success', __('admin.saved'));
        return redirect(route('admin.labs.edit', ['id' => $task['lab_id']]) . '#tasks');
    }

    public function deleteTask(Request $request): Response
    {
        $task = db()->fetch('SELECT * FROM lab_tasks WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        db()->delete('lab_tasks', ['id' => (int) $task['id']]);
        AuditLogger::log('lab.task_deleted', 'lab_task', (int) $task['id']);
        flash('success', __('admin.saved'));
        return redirect(route('admin.labs.edit', ['id' => $task['lab_id']]) . '#tasks');
    }

    private function formData(?array $lab): array
    {
        $tasks = [];
        if ($lab) {
            foreach (db()->fetchAll('SELECT * FROM lab_tasks WHERE lab_id = ? ORDER BY sort_order, id', [(int) $lab['id']]) as $task) {
                $task['translations'] = $this->translationsFor('lab_task_translations', 'task_id', (int) $task['id']);
                $tasks[] = $task;
            }
        }
        return [
            'lab' => $lab,
            'translations' => $lab ? $this->translationsFor('lab_translations', 'lab_id', (int) $lab['id']) : [],
            'tasks' => $tasks,
            'resources' => $lab ? db()->fetchAll('SELECT * FROM lesson_resources WHERE lab_id = ?', [(int) $lab['id']]) : [],
            'paths' => db()->fetchAll('SELECT p.id, pt.title FROM learning_paths p JOIN learning_path_translations pt ON pt.path_id = p.id AND pt.locale = "en" ORDER BY p.sort_order'),
            'courses' => db()->fetchAll('SELECT c.id, ct.title FROM courses c JOIN course_translations ct ON ct.course_id = c.id AND ct.locale = "en" ORDER BY c.id'),
            'datasets' => array_map('basename', glob(base_path('database/content/datasets/*')) ?: []),
        ];
    }

    private function validateLab(Request $request, ?array $lab): array
    {
        $rules = [
            'lab_type' => 'required|in:simulation,download,virtual',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'duration_minutes' => 'required|integer|between:5,600',
            'pass_percent' => 'required|integer|between:1,100',
            'max_attempts' => 'integer',
            'risk_level' => 'required|in:standard,elevated',
            'status' => 'required|in:draft,in_review,published,archived',
            'path_id' => 'integer',
            'course_id' => 'integer',
        ];
        if (!$lab) {
            $rules['slug'] = 'required|slug|max:120|unique:labs,slug';
        }
        $data = $this->validate($request, $rules);
        $t = $request->array('t');
        if (empty($t['en']['title']) || empty($t['en']['summary'])) {
            throw new ValidationException(['t_en_title' => __('admin.english_required')], $request->all());
        }
        $dataset = $request->string('dataset_file');
        if ($dataset !== '' && !\App\Services\LabService::datasetPath($dataset)) {
            $dataset = '';
        }
        $out = [
            'lab_type' => $data['lab_type'],
            'difficulty' => $data['difficulty'],
            'duration_minutes' => (int) $data['duration_minutes'],
            'pass_percent' => (int) $data['pass_percent'],
            'max_attempts' => $data['max_attempts'] ? (int) $data['max_attempts'] : null,
            'risk_level' => $data['risk_level'],
            'is_required' => $request->bool('is_required') ? 1 : 0,
            'status' => $this->guardStatus($data['status'], $lab['status'] ?? null),
            'path_id' => $data['path_id'] ? (int) $data['path_id'] : null,
            'course_id' => $data['course_id'] ? (int) $data['course_id'] : null,
            'dataset_file' => $dataset ?: null,
        ];
        if (!$lab) {
            $out['slug'] = $data['slug'];
        }
        return $out;
    }

    private function validateTask(Request $request): array
    {
        $data = $this->validate($request, [
            'task_type' => 'required|in:' . implode(',', self::TASK_TYPES),
            'points' => 'required|integer|between:1,100',
            'sort_order' => 'integer',
            'answer_spec' => 'required|max:4000',
        ]);
        $spec = json_decode($data['answer_spec'], true);
        if (!is_array($spec)) {
            throw new ValidationException(['answer_spec' => __('admin.invalid_json')], $request->all());
        }
        if ($data['task_type'] === 'query') {
            foreach (array_merge((array) ($spec['required'] ?? []), (array) ($spec['forbidden'] ?? [])) as $pattern) {
                if (!is_string($pattern) || @preg_match($pattern, '') === false) {
                    throw new ValidationException(['answer_spec' => __('admin.invalid_regex')], $request->all());
                }
            }
        }
        $translations = [];
        foreach (['en', 'fr'] as $loc) {
            $row = $request->array('t')[$loc] ?? [];
            $options = null;
            if (!empty($row['options'])) {
                $options = json_decode((string) $row['options'], true);
                if (!is_array($options)) {
                    throw new ValidationException(['options' => __('admin.invalid_json')], $request->all());
                }
            }
            $translations[$loc] = [
                'prompt' => trim((string) ($row['prompt'] ?? '')),
                'hint' => trim((string) ($row['hint'] ?? '')) ?: null,
                'explanation' => trim((string) ($row['explanation'] ?? '')) ?: null,
                'options' => $options ? json_encode($options, JSON_UNESCAPED_UNICODE) : null,
            ];
        }
        if ($translations['en']['prompt'] === '') {
            throw new ValidationException(['prompt' => __('admin.english_required')], $request->all());
        }
        return [[
            'task_type' => $data['task_type'],
            'points' => (int) $data['points'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'answer_spec' => json_encode($spec, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ], $translations];
    }

    private function saveTaskTranslations(int $taskId, array $translations): void
    {
        foreach ($translations as $loc => $row) {
            if ($row['prompt'] === '') {
                continue;
            }
            db()->query(
                'INSERT INTO lab_task_translations (task_id, locale, prompt, hint, explanation, options) VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE prompt = VALUES(prompt), hint = VALUES(hint), explanation = VALUES(explanation), options = VALUES(options)',
                [$taskId, $loc, $row['prompt'], $row['hint'], $row['explanation'], $row['options']]
            );
        }
    }
}
