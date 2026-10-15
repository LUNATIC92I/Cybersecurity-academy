<?php

declare(strict_types=1);

namespace Database\Seeders;

/**
 * Loads database/content/curriculum.php: learning paths, courses and the
 * module syllabus, each in English and French.
 */
final class CurriculumSeeder
{
    public function run(): string
    {
        $data = require base_path('database/content/curriculum.php');
        $modules = 0;
        foreach ($data['paths'] as $order => $path) {
            db()->query(
                'INSERT INTO learning_paths (code, slug, accent, icon, sort_order, is_published) VALUES (?, ?, ?, ?, ?, 1)
                 ON DUPLICATE KEY UPDATE slug = VALUES(slug), accent = VALUES(accent), icon = VALUES(icon), sort_order = VALUES(sort_order)',
                [$path['code'], $path['slug'], $path['accent'], $path['icon'], $order]
            );
            $pathId = (int) db()->value('SELECT id FROM learning_paths WHERE code = ?', [$path['code']]);
            foreach (['en', 'fr'] as $loc) {
                $t = $path[$loc];
                db()->query(
                    'INSERT INTO learning_path_translations (path_id, locale, title, tagline, description, career_outcomes, translation_status)
                     VALUES (?, ?, ?, ?, ?, ?, "published")
                     ON DUPLICATE KEY UPDATE title = VALUES(title), tagline = VALUES(tagline), description = VALUES(description),
                       career_outcomes = VALUES(career_outcomes), translation_status = VALUES(translation_status)',
                    [$pathId, $loc, $t['title'], $t['tagline'], $t['description'], implode("\n", $t['outcomes'])]
                );
            }

            $course = $path['course'];
            db()->query(
                'INSERT INTO courses (slug, level, access_level, price_cents, currency, estimated_hours, status, is_featured, min_exam_score, require_all_labs, certificate_enabled, published_at)
                 VALUES (?, ?, ?, ?, "USD", ?, "published", 1, ?, 1, 1, UTC_TIMESTAMP())
                 ON DUPLICATE KEY UPDATE level = VALUES(level), access_level = VALUES(access_level), price_cents = VALUES(price_cents),
                   estimated_hours = VALUES(estimated_hours), min_exam_score = VALUES(min_exam_score)',
                [$course['slug'], $course['level'], $course['access'], $course['price_cents'], $course['hours'], $course['min_exam_score']]
            );
            $courseId = (int) db()->value('SELECT id FROM courses WHERE slug = ?', [$course['slug']]);
            foreach (['en', 'fr'] as $loc) {
                $t = $course[$loc];
                db()->query(
                    'INSERT INTO course_translations (course_id, locale, title, subtitle, description, outcomes, audience, certificate_title, translation_status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, "published")
                     ON DUPLICATE KEY UPDATE title = VALUES(title), subtitle = VALUES(subtitle), description = VALUES(description),
                       outcomes = VALUES(outcomes), audience = VALUES(audience), certificate_title = VALUES(certificate_title), translation_status = VALUES(translation_status)',
                    [$courseId, $loc, $t['title'], $t['subtitle'], $t['description'], implode("\n", $t['outcomes']), $t['audience'], $t['certificate_title']]
                );
            }
            db()->query('INSERT IGNORE INTO learning_path_courses (path_id, course_id, sort_order) VALUES (?, ?, 0)', [$pathId, $courseId]);

            foreach ($course['modules'] as $i => $module) {
                db()->query(
                    'INSERT INTO modules (course_id, slug, sort_order, level, status) VALUES (?, ?, ?, ?, "published")
                     ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order), level = VALUES(level)',
                    [$courseId, $module['slug'], $i + 1, $module['level']]
                );
                $moduleId = (int) db()->value('SELECT id FROM modules WHERE course_id = ? AND slug = ?', [$courseId, $module['slug']]);
                foreach (['en', 'fr'] as $loc) {
                    $t = $module[$loc];
                    db()->query(
                        'INSERT INTO module_translations (module_id, locale, title, summary, topics, translation_status)
                         VALUES (?, ?, ?, ?, ?, "published")
                         ON DUPLICATE KEY UPDATE title = VALUES(title), summary = VALUES(summary), topics = VALUES(topics), translation_status = VALUES(translation_status)',
                        [$moduleId, $loc, $t['title'], $t['summary'], implode("\n", $t['topics'])]
                    );
                }
                $modules++;
            }
        }
        return "$modules modules";
    }
}
