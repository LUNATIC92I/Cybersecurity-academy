<?php

declare(strict_types=1);

namespace Database\Seeders;

/**
 * Loads lab definitions from database/content/labs/*.php.
 */
final class LabSeeder
{
    private const TEXT_FIELDS = ['title', 'summary', 'scenario', 'setup', 'instructions', 'expected_results', 'scoring', 'solution', 'cleanup', 'lessons_learned'];
    private const LIST_FIELDS = ['objectives', 'tools', 'prerequisites'];

    public function run(): string
    {
        $count = 0;
        foreach (glob(base_path('database/content/labs/*.php')) ?: [] as $file) {
            $lab = require $file;
            $pathId = db()->value('SELECT id FROM learning_paths WHERE code = ?', [$lab['path']]);
            $courseId = isset($lab['course']) ? db()->value('SELECT id FROM courses WHERE slug = ?', [$lab['course']]) : null;
            $moduleId = isset($lab['module']) && $courseId ? db()->value('SELECT id FROM modules WHERE course_id = ? AND slug = ?', [(int) $courseId, $lab['module']]) : null;
            db()->query(
                'INSERT INTO labs (slug, path_id, course_id, module_id, lab_type, difficulty, duration_minutes, pass_percent, max_attempts, is_required, risk_level, dataset_file, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "published")
                 ON DUPLICATE KEY UPDATE path_id = VALUES(path_id), course_id = VALUES(course_id), module_id = VALUES(module_id), lab_type = VALUES(lab_type),
                   difficulty = VALUES(difficulty), duration_minutes = VALUES(duration_minutes), pass_percent = VALUES(pass_percent),
                   max_attempts = VALUES(max_attempts), is_required = VALUES(is_required), risk_level = VALUES(risk_level), dataset_file = VALUES(dataset_file)',
                [
                    $lab['slug'], $pathId, $courseId, $moduleId, $lab['type'], $lab['difficulty'], $lab['duration'], $lab['pass'],
                    $lab['max_attempts'] ?? null, !empty($lab['required']) ? 1 : 0, $lab['risk'] ?? 'standard', $lab['dataset'] ?? null,
                ]
            );
            $labId = (int) db()->value('SELECT id FROM labs WHERE slug = ?', [$lab['slug']]);
            foreach (['en', 'fr'] as $loc) {
                $t = $lab[$loc];
                $row = [];
                foreach (self::TEXT_FIELDS as $f) {
                    $row[$f] = $t[$f] ?? null;
                }
                foreach (self::LIST_FIELDS as $f) {
                    $row[$f] = implode("\n", (array) ($t[$f] ?? []));
                }
                $columns = array_keys($row);
                db()->query(
                    'INSERT INTO lab_translations (lab_id, locale, ' . implode(', ', $columns) . ', translation_status) VALUES (?, ?, '
                    . implode(', ', array_fill(0, count($columns), '?')) . ', "published") ON DUPLICATE KEY UPDATE '
                    . implode(', ', array_map(fn ($c) => "$c = VALUES($c)", $columns)),
                    array_merge([$labId, $loc], array_values($row))
                );
            }

            $hasAttempts = (int) db()->value('SELECT COUNT(*) FROM lab_attempts WHERE lab_id = ?', [$labId]) > 0;
            if (!$hasAttempts) {
                db()->delete('lab_tasks', ['lab_id' => $labId]);
                foreach ($lab['tasks'] as $i => $task) {
                    $taskId = db()->insert('lab_tasks', [
                        'lab_id' => $labId,
                        'sort_order' => $i + 1,
                        'task_type' => $task['type'],
                        'points' => $task['points'],
                        'answer_spec' => json_encode($task['spec'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);
                    foreach (['en', 'fr'] as $loc) {
                        $tt = $task[$loc];
                        db()->insert('lab_task_translations', [
                            'task_id' => $taskId,
                            'locale' => $loc,
                            'prompt' => $tt['prompt'],
                            'hint' => $tt['hint'] ?? null,
                            'explanation' => $tt['explanation'] ?? null,
                            'options' => isset($tt['options']) ? json_encode($tt['options'], JSON_UNESCAPED_UNICODE) : null,
                        ]);
                    }
                }
            }
            if (!empty($lab['dataset']) && \App\Services\LabService::datasetPath($lab['dataset'])
                && !db()->value('SELECT COUNT(*) FROM lesson_resources WHERE lab_id = ? AND storage_path = ?', [$labId, 'dataset:' . $lab['dataset']])) {
                db()->insert('lesson_resources', [
                    'lab_id' => $labId,
                    'title' => $lab['en']['title'] . ' — dataset',
                    'storage_path' => 'dataset:' . $lab['dataset'],
                    'original_name' => $lab['dataset'],
                    'mime_type' => str_ends_with($lab['dataset'], '.csv') ? 'text/csv' : 'text/plain',
                    'size_bytes' => (int) filesize(\App\Services\LabService::datasetPath($lab['dataset'])),
                ]);
            }
            $count++;
        }
        return "$count labs";
    }
}
