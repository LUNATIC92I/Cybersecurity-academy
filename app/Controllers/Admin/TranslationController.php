<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;

/**
 * Translation status overview: every content item and its French status,
 * plus missing interface keys.
 */
final class TranslationController extends AdminController
{
    public function index(Request $request): Response
    {
        $sections = [
            'courses' => db()->fetchAll(
                'SELECT c.id, en.title, COALESCE(fr.translation_status, "missing") AS fr_status, en.translation_status AS en_status
                 FROM courses c JOIN course_translations en ON en.course_id = c.id AND en.locale = "en"
                 LEFT JOIN course_translations fr ON fr.course_id = c.id AND fr.locale = "fr" ORDER BY c.id'
            ),
            'modules' => db()->fetchAll(
                'SELECT m.id, en.title, COALESCE(fr.translation_status, "missing") AS fr_status, en.translation_status AS en_status
                 FROM modules m JOIN module_translations en ON en.module_id = m.id AND en.locale = "en"
                 LEFT JOIN module_translations fr ON fr.module_id = m.id AND fr.locale = "fr" ORDER BY m.course_id, m.sort_order'
            ),
            'lessons' => db()->fetchAll(
                'SELECT l.id, en.title, COALESCE(fr.translation_status, "missing") AS fr_status, en.translation_status AS en_status
                 FROM lessons l JOIN lesson_translations en ON en.lesson_id = l.id AND en.locale = "en"
                 LEFT JOIN lesson_translations fr ON fr.lesson_id = l.id AND fr.locale = "fr" ORDER BY l.module_id, l.sort_order'
            ),
            'labs' => db()->fetchAll(
                'SELECT lb.id, en.title, COALESCE(fr.translation_status, "missing") AS fr_status, en.translation_status AS en_status
                 FROM labs lb JOIN lab_translations en ON en.lab_id = lb.id AND en.locale = "en"
                 LEFT JOIN lab_translations fr ON fr.lab_id = lb.id AND fr.locale = "fr" ORDER BY lb.id'
            ),
            'questions' => db()->fetchAll(
                'SELECT q.id, LEFT(en.stem, 120) AS title, COALESCE(fr.translation_status, "missing") AS fr_status, en.translation_status AS en_status
                 FROM questions q JOIN question_translations en ON en.question_id = q.id AND en.locale = "en"
                 LEFT JOIN question_translations fr ON fr.question_id = q.id AND fr.locale = "fr" ORDER BY q.id'
            ),
        ];
        $summary = [];
        foreach ($sections as $name => $rows) {
            $summary[$name] = [
                'total' => count($rows),
                'published' => count(array_filter($rows, fn ($r) => $r['fr_status'] === 'published')),
            ];
        }
        $en = translator()->lines('en');
        $fr = translator()->lines('fr');
        return $this->render('translations', [
            'sections' => $sections,
            'summary' => $summary,
            'missingKeys' => array_keys(array_diff_key($en, $fr)),
            'uiKeys' => count($en),
        ]);
    }
}
