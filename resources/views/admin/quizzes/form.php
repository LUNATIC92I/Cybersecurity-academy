<?php $q = $quiz; ?>
<header class="admin-header"><div><a class="link-muted" href="<?= e(route('admin.quizzes')) ?>">← <?= e(__('admin.nav.quizzes')) ?></a><h1 class="h2"><?= e($translations['en']['title'] ?? $q['slug']) ?></h1></div></header>
<form class="card form" method="post" action="<?= e(route('admin.quizzes.update', ['id' => $q['id']])) ?>"><?= csrf_field() ?>
  <div class="form-row form-row--3">
    <div class="form-group"><label class="form-label" for="kind"><?= e(__('exams.col.type')) ?></label><select class="input" id="kind" name="kind"><?php foreach (['knowledge_check', 'module_quiz', 'final_exam'] as $k): ?><option value="<?= $k ?>" <?= $q['kind'] === $k ? 'selected' : '' ?>><?= e(__('exams.kind.' . $k)) ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label class="form-label" for="module_id"><?= e(__('admin.module')) ?></label><select class="input" id="module_id" name="module_id"><option value=""></option><?php foreach ($modules as $m): ?><option value="<?= (int) $m['id'] ?>" <?= (int) $q['module_id'] === (int) $m['id'] ? 'selected' : '' ?>><?= e($m['title']) ?></option><?php endforeach; ?></select></div>
    <?= partial('partials/status-select', ['value' => $q['status'], 'options' => ['draft', 'published', 'archived']]) ?>
    <?= partial('partials/field', ['name' => 'question_count', 'type' => 'number', 'label' => __('exams.col.questions'), 'value' => $q['question_count'], 'min' => 1, 'required' => true]) ?>
    <?= partial('partials/field', ['name' => 'duration_minutes', 'type' => 'number', 'label' => __('admin.duration_minutes'), 'value' => $q['duration_minutes'], 'min' => 1, 'required' => true]) ?>
    <?= partial('partials/field', ['name' => 'pass_percent', 'type' => 'number', 'label' => __('admin.pass_percent'), 'value' => $q['pass_percent'], 'min' => 1, 'max' => 100, 'required' => true]) ?>
    <?= partial('partials/field', ['name' => 'max_attempts', 'type' => 'number', 'label' => __('admin.max_attempts'), 'value' => $q['max_attempts'], 'hint' => __('admin.blank_unlimited')]) ?>
    <?= partial('partials/field', ['name' => 'retake_cooldown_minutes', 'type' => 'number', 'label' => __('admin.cooldown_minutes'), 'value' => $q['retake_cooldown_minutes'], 'min' => 0, 'required' => true]) ?>
    <div class="form-group"><label class="form-label" for="show_explanations"><?= e(__('exams.feedback')) ?></label><select class="input" id="show_explanations" name="show_explanations"><?php foreach (['never', 'after_submit', 'after_pass'] as $s): ?><option value="<?= $s ?>" <?= $q['show_explanations'] === $s ? 'selected' : '' ?>><?= e(__('exams.explanations.' . $s)) ?></option><?php endforeach; ?></select></div>
  </div>
  <fieldset class="fieldset"><legend><?= e(__('admin.bank_selection')) ?></legend>
    <p class="form-hint"><?= e(__('admin.bank_selection_hint')) ?></p>
    <?php foreach ($banks as $b): ?><div class="form-row form-row--inline"><label for="bank-<?= (int) $b['id'] ?>"><?= e($b['name']) ?> <small class="muted">(<?= e(trans_choice('admin.active_questions', (int) $b['active_count'])) ?>)</small></label>
      <input class="input input--short" type="number" min="0" max="<?= (int) $b['active_count'] ?>" id="bank-<?= (int) $b['id'] ?>" name="banks[<?= (int) $b['id'] ?>]" value="<?= (int) ($b['pick_count'] ?? 0) ?>"></div><?php endforeach; ?>
  </fieldset>
  <?= partial('partials/translation-tabs', ['prefix' => 'quiz_', 'translations' => $translations, 'withStatus' => true, 'fields' => ['title' => [__('field.title'), 'text'], 'description' => [__('field.description'), 'textarea']]]) ?>
  <button class="btn btn--primary" type="submit"><?= e(__('common.save')) ?></button>
</form>
