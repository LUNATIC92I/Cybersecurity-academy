<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuditLogger;

final class QuizController extends AdminController
{
    public function index(Request $request): Response
    {
        $quizzes = db()->fetchAll(
            'SELECT q.*, t.title, ct.title AS course_title,
                    (SELECT COUNT(*) FROM quiz_attempts a WHERE a.quiz_id = q.id AND a.status = "submitted") AS attempts,
                    (SELECT ROUND(AVG(percent)) FROM quiz_attempts a WHERE a.quiz_id = q.id AND a.status = "submitted") AS avg_score
             FROM quizzes q LEFT JOIN quiz_translations t ON t.quiz_id = q.id AND t.locale = "en"
             LEFT JOIN course_translations ct ON ct.course_id = q.course_id AND ct.locale = "en" ORDER BY q.course_id, q.id'
        );
        return $this->render('quizzes/index', [
            'quizzes' => $quizzes,
            'courses' => db()->fetchAll('SELECT c.id, ct.title FROM courses c JOIN course_translations ct ON ct.course_id = c.id AND ct.locale = "en" ORDER BY c.id'),
        ]);
    }

    public function store(Request $request): Response
    {
        $data = $this->validate($request, [
            'course_id' => 'required|integer|exists:courses,id',
            'slug' => 'required|slug|max:120|unique:quizzes,slug',
            'kind' => 'required|in:knowledge_check,module_quiz,final_exam',
            'title_en' => 'required|max:190',
        ]);
        $id = db()->insert('quizzes', [
            'course_id' => (int) $data['course_id'],
            'slug' => $data['slug'],
            'kind' => $data['kind'],
            'status' => 'draft',
        ]);
        db()->insert('quiz_translations', ['quiz_id' => $id, 'locale' => 'en', 'title' => $data['title_en'], 'translation_status' => 'draft']);
        AuditLogger::log('quiz.created', 'quiz', $id);
        flash('success', __('admin.saved'));
        return redirect(route('admin.quizzes.edit', ['id' => $id]));
    }

    public function edit(Request $request): Response
    {
        $quiz = db()->fetch('SELECT * FROM quizzes WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        return $this->render('quizzes/form', [
            'quiz' => $quiz,
            'translations' => $this->translationsFor('quiz_translations', 'quiz_id', (int) $quiz['id']),
            'banks' => db()->fetchAll(
                'SELECT b.*, (SELECT COUNT(*) FROM questions q WHERE q.bank_id = b.id AND q.status = "active") AS active_count,
                        qb.pick_count
                 FROM question_banks b LEFT JOIN quiz_banks qb ON qb.bank_id = b.id AND qb.quiz_id = ?
                 WHERE b.course_id = ? ORDER BY b.id',
                [(int) $quiz['id'], (int) $quiz['course_id']]
            ),
            'modules' => db()->fetchAll('SELECT m.id, mt.title FROM modules m JOIN module_translations mt ON mt.module_id = m.id AND mt.locale = "en" WHERE m.course_id = ? ORDER BY m.sort_order', [(int) $quiz['course_id']]),
        ]);
    }

    public function update(Request $request): Response
    {
        $quiz = db()->fetch('SELECT * FROM quizzes WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        $data = $this->validate($request, [
            'kind' => 'required|in:knowledge_check,module_quiz,final_exam',
            'module_id' => 'integer',
            'question_count' => 'required|integer|between:1,200',
            'duration_minutes' => 'required|integer|between:1,600',
            'pass_percent' => 'required|integer|between:1,100',
            'max_attempts' => 'integer',
            'retake_cooldown_minutes' => 'required|integer',
            'show_explanations' => 'required|in:never,after_submit,after_pass',
            'status' => 'required|in:draft,published,archived',
        ]);
        $status = $data['status'];
        if ($status !== $quiz['status'] && $status !== 'draft' && !auth()->can('content.publish')) {
            flash('error', __('admin.publish_denied'));
            $status = $quiz['status'];
        }
        db()->transaction(function () use ($quiz, $data, $status, $request) {
            db()->update('quizzes', [
                'kind' => $data['kind'],
                'module_id' => $data['module_id'] ? (int) $data['module_id'] : null,
                'question_count' => (int) $data['question_count'],
                'duration_minutes' => (int) $data['duration_minutes'],
                'pass_percent' => (int) $data['pass_percent'],
                'max_attempts' => $data['max_attempts'] ? (int) $data['max_attempts'] : null,
                'retake_cooldown_minutes' => max(0, (int) $data['retake_cooldown_minutes']),
                'show_explanations' => $data['show_explanations'],
                'status' => $status,
            ], ['id' => (int) $quiz['id']]);
            $this->saveTranslations('quiz_translations', 'quiz_id', (int) $quiz['id'], $request->array('t'), ['title', 'description', 'translation_status']);
            db()->delete('quiz_banks', ['quiz_id' => (int) $quiz['id']]);
            foreach ($request->array('banks') as $bankId => $count) {
                $count = (int) $count;
                $valid = db()->value('SELECT COUNT(*) FROM question_banks WHERE id = ? AND course_id = ?', [(int) $bankId, (int) $quiz['course_id']]);
                if ($count > 0 && $valid) {
                    db()->insert('quiz_banks', ['quiz_id' => (int) $quiz['id'], 'bank_id' => (int) $bankId, 'pick_count' => $count]);
                }
            }
        });
        AuditLogger::log('quiz.updated', 'quiz', (int) $quiz['id'], ['pass_percent' => (int) $data['pass_percent'], 'status' => $status]);
        flash('success', __('admin.saved'));
        return redirect(route('admin.quizzes.edit', ['id' => $quiz['id']]));
    }
}
