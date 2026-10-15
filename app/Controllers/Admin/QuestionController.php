<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\ValidationException;
use App\Services\AuditLogger;

/**
 * Question bank management. New question types only need a scorer branch in
 * QuizService::scoreQuestion; banks and quizzes are pure data.
 */
final class QuestionController extends AdminController
{
    private const TYPES = ['single', 'multiple', 'true_false', 'scenario', 'matching', 'numeric', 'text'];

    public function index(Request $request): Response
    {
        $bankId = (int) $request->query('bank', 0);
        $type = (string) $request->query('type', '');
        $where = ['1 = 1'];
        $params = [];
        if ($bankId) {
            $where[] = 'q.bank_id = ?';
            $params[] = $bankId;
        }
        if (in_array($type, self::TYPES, true)) {
            $where[] = 'q.question_type = ?';
            $params[] = $type;
        }
        $result = $this->paginate(
            'SELECT q.*, en.stem, b.name AS bank_name, COALESCE(fr.translation_status, "missing") AS fr_status
             FROM questions q JOIN question_banks b ON b.id = q.bank_id
             LEFT JOIN question_translations en ON en.question_id = q.id AND en.locale = "en"
             LEFT JOIN question_translations fr ON fr.question_id = q.id AND fr.locale = "fr"
             WHERE ' . implode(' AND ', $where) . ' ORDER BY q.id DESC',
            $params,
            (int) $request->query('page', 1)
        );
        return $this->render('questions/index', [
            'result' => $result,
            'banks' => $this->banks(),
            'bankId' => $bankId,
            'type' => $type,
            'types' => self::TYPES,
            'courses' => db()->fetchAll('SELECT c.id, ct.title FROM courses c JOIN course_translations ct ON ct.course_id = c.id AND ct.locale = "en" ORDER BY c.id'),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('questions/form', ['question' => null, 'translations' => [], 'options' => [], 'banks' => $this->banks(), 'types' => self::TYPES]);
    }

    public function store(Request $request): Response
    {
        [$data, $options] = $this->validateQuestion($request);
        $id = db()->transaction(function () use ($data, $options, $request) {
            $id = db()->insert('questions', $data);
            $this->saveTranslations('question_translations', 'question_id', $id, $request->array('t'), ['stem', 'scenario', 'explanation', 'translation_status']);
            $this->saveOptions($id, $options);
            return $id;
        });
        AuditLogger::log('question.created', 'question', $id);
        flash('success', __('admin.saved'));
        return redirect(route('admin.questions.edit', ['id' => $id]));
    }

    public function edit(Request $request): Response
    {
        $question = db()->fetch('SELECT * FROM questions WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        $options = db()->fetchAll('SELECT * FROM answer_options WHERE question_id = ? ORDER BY sort_order, id', [(int) $question['id']]);
        foreach ($options as &$option) {
            $option['translations'] = $this->translationsFor('answer_option_translations', 'option_id', (int) $option['id']);
        }
        return $this->render('questions/form', [
            'question' => $question,
            'translations' => $this->translationsFor('question_translations', 'question_id', (int) $question['id']),
            'options' => $options,
            'banks' => $this->banks(),
            'types' => self::TYPES,
        ]);
    }

    public function update(Request $request): Response
    {
        $question = db()->fetch('SELECT * FROM questions WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        [$data, $options] = $this->validateQuestion($request);
        db()->transaction(function () use ($question, $data, $options, $request) {
            db()->update('questions', $data, ['id' => (int) $question['id']]);
            $this->saveTranslations('question_translations', 'question_id', (int) $question['id'], $request->array('t'), ['stem', 'scenario', 'explanation', 'translation_status']);
            // Options referenced by past responses are kept consistent because
            // responses store option ids; replacing options only affects new attempts.
            db()->delete('answer_options', ['question_id' => (int) $question['id']]);
            $this->saveOptions((int) $question['id'], $options);
        });
        AuditLogger::log('question.updated', 'question', (int) $question['id']);
        flash('success', __('admin.saved'));
        return redirect(route('admin.questions.edit', ['id' => $question['id']]));
    }

    public function storeBank(Request $request): Response
    {
        $data = $this->validate($request, [
            'course_id' => 'required|integer|exists:courses,id',
            'code' => 'required|slug|max:80|unique:question_banks,code',
            'name' => 'required|max:190',
        ]);
        $id = db()->insert('question_banks', ['course_id' => (int) $data['course_id'], 'code' => $data['code'], 'name' => $data['name']]);
        AuditLogger::log('question_bank.created', 'question_bank', $id);
        flash('success', __('admin.saved'));
        return redirect(route('admin.questions', ['bank' => $id]));
    }

    private function validateQuestion(Request $request): array
    {
        $data = $this->validate($request, [
            'bank_id' => 'required|integer|exists:question_banks,id',
            'question_type' => 'required|in:' . implode(',', self::TYPES),
            'difficulty' => 'required|in:easy,medium,hard',
            'points' => 'required|integer|between:1,20',
            'status' => 'required|in:active,retired',
        ]);
        $t = $request->array('t');
        if (empty($t['en']['stem'])) {
            throw new ValidationException(['stem' => __('admin.english_required')], $request->all());
        }
        $spec = null;
        if ($data['question_type'] === 'numeric') {
            if (!is_numeric($request->input('numeric_value'))) {
                throw new ValidationException(['numeric_value' => __('validation.numeric', ['field' => __('field.numeric_value')])], $request->all());
            }
            $spec = ['value' => (float) $request->input('numeric_value'), 'tolerance' => abs((float) $request->input('numeric_tolerance', 0))];
        } elseif ($data['question_type'] === 'text') {
            $accepted = array_values(array_filter(array_map('trim', preg_split('/\R/', $request->string('accepted_answers')) ?: [])));
            if (!$accepted) {
                throw new ValidationException(['accepted_answers' => __('admin.questions.accepted_required')], $request->all());
            }
            $spec = ['accepted' => $accepted];
        }

        $options = [];
        if (!in_array($data['question_type'], ['numeric', 'text'], true)) {
            foreach ($request->array('options') as $i => $row) {
                if (!is_array($row) || trim((string) ($row['en'] ?? '')) === '') {
                    continue;
                }
                $options[] = [
                    'sort_order' => count($options),
                    'is_correct' => !empty($row['correct']) ? 1 : 0,
                    'match_key' => $data['question_type'] === 'matching' ? (trim((string) ($row['match_key'] ?? '')) ?: 'k' . $i) : null,
                    'en' => trim((string) $row['en']),
                    'fr' => trim((string) ($row['fr'] ?? '')),
                    'match_en' => trim((string) ($row['match_en'] ?? '')),
                    'match_fr' => trim((string) ($row['match_fr'] ?? '')),
                ];
            }
            $correct = count(array_filter($options, fn ($o) => $o['is_correct']));
            $error = match (true) {
                count($options) < 2 => __('admin.questions.min_options'),
                in_array($data['question_type'], ['single', 'scenario', 'true_false'], true) && $correct !== 1 => __('admin.questions.one_correct'),
                $data['question_type'] === 'multiple' && $correct < 1 => __('admin.questions.some_correct'),
                $data['question_type'] === 'matching' && count(array_filter($options, fn ($o) => $o['match_en'] === '')) > 0 => __('admin.questions.match_labels'),
                default => null,
            };
            if ($error) {
                throw new ValidationException(['options' => $error], $request->all());
            }
        }
        return [[
            'bank_id' => (int) $data['bank_id'],
            'question_type' => $data['question_type'],
            'difficulty' => $data['difficulty'],
            'points' => (int) $data['points'],
            'status' => $data['status'],
            'answer_spec' => $spec ? json_encode($spec, JSON_UNESCAPED_UNICODE) : null,
        ], $options];
    }

    private function saveOptions(int $questionId, array $options): void
    {
        foreach ($options as $o) {
            $oid = db()->insert('answer_options', [
                'question_id' => $questionId,
                'sort_order' => $o['sort_order'],
                'is_correct' => $o['is_correct'],
                'match_key' => $o['match_key'],
            ]);
            db()->insert('answer_option_translations', ['option_id' => $oid, 'locale' => 'en', 'label' => $o['en'], 'match_label' => $o['match_en'] ?: null]);
            if ($o['fr'] !== '') {
                db()->insert('answer_option_translations', ['option_id' => $oid, 'locale' => 'fr', 'label' => $o['fr'], 'match_label' => $o['match_fr'] ?: null]);
            }
        }
    }

    private function banks(): array
    {
        return db()->fetchAll(
            'SELECT b.*, (SELECT COUNT(*) FROM questions q WHERE q.bank_id = b.id AND q.status = "active") AS active_count
             FROM question_banks b ORDER BY b.course_id, b.id'
        );
    }
}
