<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Services\Localizer;

final class CourseRepository
{
    public static function catalog(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $where = ['c.status = "published"'];
        $params = [];
        if (!empty($filters['path'])) {
            $where[] = 'c.id IN (SELECT lpc.course_id FROM learning_path_courses lpc JOIN learning_paths lp ON lp.id = lpc.path_id WHERE lp.slug = ?)';
            $params[] = $filters['path'];
        }
        if (!empty($filters['level']) && in_array($filters['level'], ['beginner', 'intermediate', 'advanced', 'all'], true)) {
            $where[] = 'c.level = ?';
            $params[] = $filters['level'];
        }
        if (!empty($filters['access']) && in_array($filters['access'], ['free', 'premium'], true)) {
            $where[] = 'c.access_level = ?';
            $params[] = $filters['access'];
        }
        if (!empty($filters['q'])) {
            $where[] = 'c.id IN (SELECT course_id FROM course_translations WHERE title LIKE ? OR subtitle LIKE ? OR description LIKE ?)';
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], mb_substr((string) $filters['q'], 0, 80)) . '%';
            array_push($params, $like, $like, $like);
        }
        $whereSql = implode(' AND ', $where);
        $total = (int) db()->value("SELECT COUNT(*) FROM courses c WHERE $whereSql", $params);
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows = db()->fetchAll(
            "SELECT c.* FROM courses c WHERE $whereSql ORDER BY c.is_featured DESC, c.id LIMIT $perPage OFFSET $offset",
            $params
        );
        return [
            'items' => self::decorate($rows),
            'total' => $total,
            'page' => $page,
            'pages' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    public static function featured(int $limit = 3): array
    {
        $rows = db()->fetchAll('SELECT * FROM courses WHERE status = "published" ORDER BY is_featured DESC, id LIMIT ' . max(1, $limit));
        return self::decorate($rows);
    }

    public static function findBySlug(string $slug, bool $includeUnpublished = false): ?array
    {
        $course = db()->fetch('SELECT * FROM courses WHERE slug = ?', [$slug]);
        if (!$course || (!$includeUnpublished && $course['status'] !== 'published')) {
            return null;
        }
        return self::decorate([$course], $includeUnpublished)[0];
    }

    public static function find(int $id): ?array
    {
        $course = db()->fetch('SELECT * FROM courses WHERE id = ?', [$id]);
        return $course ? self::decorate([$course], true)[0] : null;
    }

    /**
     * Attach translation, learning path and syllabus counters.
     */
    public static function decorate(array $courses, bool $allowDrafts = false): array
    {
        if (!$courses) {
            return [];
        }
        $courses = Localizer::attach($courses, 'course_translations', 'course_id', 't', null, $allowDrafts);
        $ids = array_map(fn ($c) => (int) $c['id'], $courses);
        $in = Database::placeholders($ids);
        $counts = db()->fetchAll(
            "SELECT m.course_id,
                    COUNT(DISTINCT m.id) AS modules,
                    COUNT(DISTINCT CASE WHEN l.status = 'published' AND m.status = 'published' THEN l.id END) AS lessons,
                    COALESCE(SUM(CASE WHEN l.status = 'published' AND m.status = 'published' THEN l.duration_minutes END), 0) AS minutes
             FROM modules m LEFT JOIN lessons l ON l.module_id = m.id
             WHERE m.course_id IN ($in) GROUP BY m.course_id",
            $ids
        );
        $countMap = array_column($counts, null, 'course_id');
        $labs = db()->fetchAll("SELECT course_id, COUNT(*) AS n FROM labs WHERE status = 'published' AND course_id IN ($in) GROUP BY course_id", $ids);
        $labMap = array_column($labs, 'n', 'course_id');
        $paths = db()->fetchAll(
            "SELECT lpc.course_id, lp.* FROM learning_path_courses lpc JOIN learning_paths lp ON lp.id = lpc.path_id WHERE lpc.course_id IN ($in)",
            $ids
        );
        $paths = Localizer::attach(array_map(fn ($p) => $p, $paths), 'learning_path_translations', 'path_id', 't');
        $pathMap = [];
        foreach ($paths as $p) {
            $pathMap[(int) $p['course_id']] ??= $p;
        }
        foreach ($courses as &$course) {
            $c = $countMap[$course['id']] ?? ['modules' => 0, 'lessons' => 0, 'minutes' => 0];
            $course['module_count'] = (int) $c['modules'];
            $course['lesson_count'] = (int) $c['lessons'];
            $course['lesson_minutes'] = (int) $c['minutes'];
            $course['lab_count'] = (int) ($labMap[$course['id']] ?? 0);
            $course['path'] = $pathMap[(int) $course['id']] ?? null;
        }
        return $courses;
    }

    /**
     * Modules with their lessons (translated). Unpublished items only for staff.
     */
    public static function syllabus(int $courseId, bool $includeUnpublished = false): array
    {
        $statusSql = $includeUnpublished ? '' : ' AND status = "published"';
        $modules = db()->fetchAll("SELECT * FROM modules WHERE course_id = ? AND status <> 'archived' $statusSql ORDER BY sort_order, id", [$courseId]);
        $modules = Localizer::attach($modules, 'module_translations', 'module_id', 't', null, $includeUnpublished);
        if (!$modules) {
            return [];
        }
        $moduleIds = array_map(fn ($m) => (int) $m['id'], $modules);
        $lessons = db()->fetchAll(
            'SELECT * FROM lessons WHERE module_id IN (' . Database::placeholders($moduleIds) . ") AND status <> 'archived'" . $statusSql . ' ORDER BY sort_order, id',
            $moduleIds
        );
        $lessons = Localizer::attach($lessons, 'lesson_translations', 'lesson_id', 't', null, $includeUnpublished);
        $byModule = [];
        foreach ($lessons as $lesson) {
            $byModule[(int) $lesson['module_id']][] = $lesson;
        }
        foreach ($modules as &$module) {
            $module['lessons'] = $byModule[(int) $module['id']] ?? [];
            $module['topics'] = Localizer::lines($module['t']['topics'] ?? '');
        }
        return $modules;
    }
}
