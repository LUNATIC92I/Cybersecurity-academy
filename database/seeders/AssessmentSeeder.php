<?php

declare(strict_types=1);

namespace Database\Seeders;

/**
 * Loads question banks and quizzes from database/content/questions/*.php.
 * A bank that already contains questions is left untouched so that
 * historical attempts keep referencing the same question records.
 */
final class AssessmentSeeder
{
    public function run(): string
    {
        $questions = 0;
        $quizzes = 0;
        foreach (glob(base_path('database/content/questions/*.php')) ?: [] as $file) {
            $data = require $file;
            $courseId = (int) db()->value('SELECT id FROM courses WHERE slug = ?', [$data['course']]);
            if (!$courseId) {
                throw new \RuntimeException('Unknown course in ' . basename($file));
            }
            foreach ($data['banks'] as $code => $bank) {
                $moduleId = isset($bank['module']) ? db()->value('SELECT id FROM modules WHERE course_id = ? AND slug = ?', [$courseId, $bank['module']]) : null;
                db()->query(
                    'INSERT INTO question_banks (course_id, module_id, code, name) VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE name = VALUES(name), module_id = VALUES(module_id)',
                    [$courseId, $moduleId, $code, $bank['name']]
                );
                $bankId = (int) db()->value('SELECT id FROM question_banks WHERE code = ?', [$code]);
                if ((int) db()->value('SELECT COUNT(*) FROM questions WHERE bank_id = ?', [$bankId]) > 0) {
                    continue;
                }
                foreach ($bank['questions'] as $q) {
                    $qid = db()->insert('questions', [
                        'bank_id' => $bankId,
                        'question_type' => $q['type'],
                        'difficulty' => $q['difficulty'] ?? 'medium',
                        'points' => $q['points'] ?? 1,
                        'answer_spec' => isset($q['spec']) ? json_encode($q['spec'], JSON_UNESCAPED_UNICODE) : null,
                    ]);
                    foreach (['en', 'fr'] as $loc) {
                        db()->insert('question_translations', [
                            'question_id' => $qid,
                            'locale' => $loc,
                            'stem' => $q[$loc]['stem'],
                            'scenario' => $q[$loc]['scenario'] ?? null,
                            'explanation' => $q[$loc]['explanation'] ?? null,
                            'translation_status' => 'published',
                        ]);
                    }
                    foreach ($q['options'] ?? [] as $i => $o) {
                        $oid = db()->insert('answer_options', [
                            'question_id' => $qid,
                            'sort_order' => $i,
                            'is_correct' => !empty($o['correct']) ? 1 : 0,
                            'match_key' => $o['match_key'] ?? null,
                        ]);
                        foreach (['en', 'fr'] as $loc) {
                            db()->insert('answer_option_translations', [
                                'option_id' => $oid,
                                'locale' => $loc,
                                'label' => $o[$loc],
                                'match_label' => $o['match_' . $loc] ?? null,
                            ]);
                        }
                    }
                    $questions++;
                }
            }
            foreach ($data['quizzes'] as $quiz) {
                $moduleId = isset($quiz['module']) ? db()->value('SELECT id FROM modules WHERE course_id = ? AND slug = ?', [$courseId, $quiz['module']]) : null;
                db()->query(
                    'INSERT INTO quizzes (course_id, module_id, slug, kind, question_count, duration_minutes, pass_percent, max_attempts, retake_cooldown_minutes, show_explanations, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "published")
                     ON DUPLICATE KEY UPDATE module_id = VALUES(module_id), kind = VALUES(kind), question_count = VALUES(question_count),
                       duration_minutes = VALUES(duration_minutes), pass_percent = VALUES(pass_percent), max_attempts = VALUES(max_attempts),
                       retake_cooldown_minutes = VALUES(retake_cooldown_minutes), show_explanations = VALUES(show_explanations)',
                    [
                        $courseId, $moduleId, $quiz['slug'], $quiz['kind'], $quiz['count'], $quiz['duration'], $quiz['pass'],
                        $quiz['max_attempts'] ?? null, $quiz['cooldown'] ?? 0, $quiz['explanations'] ?? 'after_submit',
                    ]
                );
                $quizId = (int) db()->value('SELECT id FROM quizzes WHERE slug = ?', [$quiz['slug']]);
                foreach (['en', 'fr'] as $loc) {
                    db()->query(
                        'INSERT INTO quiz_translations (quiz_id, locale, title, description, translation_status) VALUES (?, ?, ?, ?, "published")
                         ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description)',
                        [$quizId, $loc, $quiz[$loc]['title'], $quiz[$loc]['description']]
                    );
                }
                db()->delete('quiz_banks', ['quiz_id' => $quizId]);
                foreach ($quiz['banks'] as $code => $pick) {
                    $bankId = (int) db()->value('SELECT id FROM question_banks WHERE code = ?', [$code]);
                    db()->insert('quiz_banks', ['quiz_id' => $quizId, 'bank_id' => $bankId, 'pick_count' => $pick]);
                }
                $quizzes++;
            }
        }
        return "$questions questions, $quizzes quizzes";
    }
}
