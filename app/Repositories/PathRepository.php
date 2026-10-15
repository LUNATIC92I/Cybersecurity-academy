<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Services\Localizer;

final class PathRepository
{
    public static function all(): array
    {
        $paths = db()->fetchAll('SELECT * FROM learning_paths WHERE is_published = 1 ORDER BY sort_order, id');
        $paths = Localizer::attach($paths, 'learning_path_translations', 'path_id');
        foreach ($paths as &$path) {
            $path['course_count'] = (int) db()->value(
                'SELECT COUNT(*) FROM learning_path_courses lpc JOIN courses c ON c.id = lpc.course_id WHERE lpc.path_id = ? AND c.status = "published"',
                [(int) $path['id']]
            );
            $path['module_count'] = (int) db()->value(
                'SELECT COUNT(*) FROM modules m JOIN learning_path_courses lpc ON lpc.course_id = m.course_id WHERE lpc.path_id = ? AND m.status <> "archived"',
                [(int) $path['id']]
            );
            $path['lesson_count'] = (int) db()->value(
                'SELECT COUNT(*) FROM lessons l JOIN modules m ON m.id = l.module_id JOIN learning_path_courses lpc ON lpc.course_id = m.course_id
                 WHERE lpc.path_id = ? AND l.status = "published" AND m.status = "published"',
                [(int) $path['id']]
            );
            $path['lab_count'] = (int) db()->value('SELECT COUNT(*) FROM labs WHERE path_id = ? AND status = "published"', [(int) $path['id']]);
        }
        return $paths;
    }

    public static function findBySlug(string $slug): ?array
    {
        foreach (self::all() as $path) {
            if ($path['slug'] === $slug) {
                return $path;
            }
        }
        return null;
    }

    public static function courses(int $pathId): array
    {
        $rows = db()->fetchAll(
            'SELECT c.* FROM courses c JOIN learning_path_courses lpc ON lpc.course_id = c.id
             WHERE lpc.path_id = ? AND c.status = "published" ORDER BY lpc.sort_order',
            [$pathId]
        );
        return CourseRepository::decorate($rows);
    }
}
