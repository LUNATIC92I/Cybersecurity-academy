<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Services\Localizer;

final class LabRepository
{
    public static function published(array $filters = []): array
    {
        $where = ['l.status = "published"'];
        $params = [];
        if (!empty($filters['type']) && in_array($filters['type'], ['simulation', 'download', 'virtual'], true)) {
            $where[] = 'l.lab_type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['path'])) {
            $where[] = 'lp.slug = ?';
            $params[] = $filters['path'];
        }
        $rows = db()->fetchAll(
            'SELECT l.*, lp.slug AS path_slug, lp.code AS path_code, lp.accent AS path_accent
             FROM labs l LEFT JOIN learning_paths lp ON lp.id = l.path_id
             WHERE ' . implode(' AND ', $where) . ' ORDER BY lp.sort_order, l.difficulty, l.id',
            $params
        );
        return self::decorate($rows);
    }

    public static function findBySlug(string $slug, bool $includeUnpublished = false): ?array
    {
        $lab = db()->fetch(
            'SELECT l.*, lp.slug AS path_slug, lp.code AS path_code, lp.accent AS path_accent
             FROM labs l LEFT JOIN learning_paths lp ON lp.id = l.path_id WHERE l.slug = ?',
            [$slug]
        );
        if (!$lab || (!$includeUnpublished && $lab['status'] !== 'published')) {
            return null;
        }
        return self::decorate([$lab], $includeUnpublished)[0];
    }

    public static function forCourse(int $courseId): array
    {
        return self::decorate(db()->fetchAll(
            'SELECT l.*, lp.slug AS path_slug, lp.code AS path_code, lp.accent AS path_accent
             FROM labs l LEFT JOIN learning_paths lp ON lp.id = l.path_id
             WHERE l.course_id = ? AND l.status = "published" ORDER BY l.id',
            [$courseId]
        ));
    }

    private static function decorate(array $labs, bool $allowDrafts = false): array
    {
        $labs = Localizer::attach($labs, 'lab_translations', 'lab_id', 't', null, $allowDrafts);
        foreach ($labs as &$lab) {
            $lab['task_count'] = (int) db()->value('SELECT COUNT(*) FROM lab_tasks WHERE lab_id = ?', [(int) $lab['id']]);
            $lab['max_points'] = (int) db()->value('SELECT COALESCE(SUM(points),0) FROM lab_tasks WHERE lab_id = ?', [(int) $lab['id']]);
        }
        return $labs;
    }
}
