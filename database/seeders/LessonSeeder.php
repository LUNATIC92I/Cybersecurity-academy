<?php

declare(strict_types=1);

namespace Database\Seeders;

/**
 * Imports lesson Markdown files:
 *   database/content/lessons/{course}/{module}/{NN}-{slug}.{en|fr}.md
 *
 * Each file has a front-matter block and optional sections introduced by
 * lines such as "===assignment===", "===criteria===", "===references===".
 */
final class LessonSeeder
{
    public function run(): string
    {
        $root = base_path('database/content/lessons');
        $count = 0;
        foreach (glob($root . '/*/*/*.en.md') ?: [] as $enFile) {
            $relative = substr($enFile, strlen($root) + 1);
            [$courseSlug, $moduleSlug, $file] = explode('/', $relative);
            if (!preg_match('/^(\d+)-([a-z0-9-]+)\.en\.md$/', $file, $m)) {
                continue;
            }
            $moduleId = db()->value(
                'SELECT m.id FROM modules m JOIN courses c ON c.id = m.course_id WHERE c.slug = ? AND m.slug = ?',
                [$courseSlug, $moduleSlug]
            );
            if (!$moduleId) {
                throw new \RuntimeException("Module not found for lesson file $relative");
            }
            $en = self::parse((string) file_get_contents($enFile));
            $frFile = substr($enFile, 0, -6) . '.fr.md';
            $fr = is_file($frFile) ? self::parse((string) file_get_contents($frFile)) : null;

            $meta = $en['meta'];
            db()->query(
                'INSERT INTO lessons (module_id, slug, sort_order, chapter_number, level, duration_minutes, is_required, is_preview, status)
                 VALUES (?, ?, ?, ?, ?, ?, 1, ?, "published")
                 ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order), chapter_number = VALUES(chapter_number), level = VALUES(level),
                   duration_minutes = VALUES(duration_minutes), is_preview = VALUES(is_preview)',
                [(int) $moduleId, $m[2], (int) $m[1], (int) ($meta['chapter_number'] ?? 1), $meta['level'] ?? 'beginner', (int) ($meta['duration'] ?? 30), !empty($meta['preview']) && $meta['preview'] !== 'false' ? 1 : 0]
            );
            $lessonId = (int) db()->value('SELECT id FROM lessons WHERE module_id = ? AND slug = ?', [(int) $moduleId, $m[2]]);
            foreach (['en' => $en, 'fr' => $fr] as $locale => $doc) {
                if ($doc === null) {
                    continue;
                }
                $mt = $doc['meta'];
                db()->query(
                    'INSERT INTO lesson_translations (lesson_id, locale, title, chapter_title, summary, objectives, prerequisites, content, assignment, completion_criteria, references_md, translation_status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE title = VALUES(title), chapter_title = VALUES(chapter_title), summary = VALUES(summary),
                       objectives = VALUES(objectives), prerequisites = VALUES(prerequisites), content = VALUES(content),
                       assignment = VALUES(assignment), completion_criteria = VALUES(completion_criteria), references_md = VALUES(references_md),
                       translation_status = VALUES(translation_status)',
                    [
                        $lessonId, $locale, $mt['title'] ?? $m[2], $mt['chapter'] ?? null, $mt['summary'] ?? '',
                        implode("\n", (array) ($mt['objectives'] ?? [])), implode("\n", (array) ($mt['prerequisites'] ?? [])),
                        $doc['body'], $doc['sections']['assignment'] ?? null, $doc['sections']['criteria'] ?? null,
                        $doc['sections']['references'] ?? null, $mt['status'] ?? 'published',
                    ]
                );
            }
            foreach ((array) ($meta['resources'] ?? []) as $resource) {
                [$title, $dataset] = array_map('trim', explode('|', $resource) + [1 => '']);
                $path = 'dataset:' . $dataset;
                if ($dataset && \App\Services\LabService::datasetPath($dataset) && !db()->value('SELECT COUNT(*) FROM lesson_resources WHERE lesson_id = ? AND storage_path = ?', [$lessonId, $path])) {
                    $abs = \App\Services\LabService::datasetPath($dataset);
                    db()->insert('lesson_resources', [
                        'lesson_id' => $lessonId,
                        'title' => $title,
                        'storage_path' => $path,
                        'original_name' => $dataset,
                        'mime_type' => str_ends_with($dataset, '.csv') ? 'text/csv' : 'text/plain',
                        'size_bytes' => (int) filesize($abs),
                    ]);
                }
            }
            $count++;
        }
        return "$count lessons";
    }

    /**
     * @return array{meta: array, body: string, sections: array<string, string>}
     */
    public static function parse(string $raw): array
    {
        $raw = str_replace("\r\n", "\n", $raw);
        $meta = [];
        $body = $raw;
        if (preg_match('/^---\n(.*?)\n---\n(.*)$/s', $raw, $m)) {
            $body = $m[2];
            $currentList = null;
            foreach (explode("\n", $m[1]) as $line) {
                if (preg_match('/^\s*-\s+(.*)$/', $line, $lm) && $currentList !== null) {
                    $meta[$currentList][] = trim($lm[1]);
                } elseif (preg_match('/^([a-z_]+):\s*(.*)$/', $line, $km)) {
                    $value = trim($km[2]);
                    if ($value === '') {
                        $currentList = $km[1];
                        $meta[$currentList] = [];
                    } else {
                        $currentList = null;
                        $meta[$km[1]] = trim($value, '"');
                    }
                }
            }
        }
        $sections = [];
        $parts = preg_split('/^===([a-z]+)===\s*$/m', $body, -1, PREG_SPLIT_DELIM_CAPTURE);
        $body = trim((string) array_shift($parts));
        for ($i = 0; $i + 1 < count($parts); $i += 2) {
            $sections[$parts[$i]] = trim($parts[$i + 1]);
        }
        return ['meta' => $meta, 'body' => $body, 'sections' => $sections];
    }
}
