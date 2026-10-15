<?php $q = $question ?? []; $v = fn ($k, $d = '') => old($k, $q[$k] ?? $d); $spec = json_decode((string) ($q['answer_spec'] ?? ''), true) ?: []; $rows = $options; while (count($rows) < 6) { $rows[] = []; } ?>
<header class="admin-header"><div><a class="link-muted" href="<?= e(route('admin.questions')) ?>">← <?= e(__('admin.nav.questions')) ?></a><h1 class="h2"><?= e($question ? __('admin.edit_question', ['id' => $question['id']]) : __('admin.new_question')) ?></h1></div></header>
<form class="card form" method="post" action="<?= e($question ? route('admin.questions.update', ['id' => $question['id']]) : route('admin.questions.store')) ?>"><?= csrf_field() ?>
  <div class="form-row form-row--3">
    <div class="form-group"><label class="form-label" for="bank_id"><?= e(__('admin.bank')) ?></label><select class="input" id="bank_id" name="bank_id"><?php foreach ($banks as $b): ?><option value="<?= (int) $b['id'] ?>" <?= (int) $v('bank_id') === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label class="form-label" for="question_type"><?= e(__('admin.question_type')) ?></label><select class="input" id="question_type" name="question_type"><?php foreach ($types as $ty): ?><option value="<?= $ty ?>" <?= $v('question_type', 'single') === $ty ? 'selected' : '' ?>><?= e(__('exams.qtype.' . $ty)) ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label class="form-label" for="difficulty"><?= e(__('admin.difficulty')) ?></label><select class="input" id="difficulty" name="difficulty"><?php foreach (['easy', 'medium', 'hard'] as $d): ?><option value="<?= $d ?>" <?= $v('difficulty', 'medium') === $d ? 'selected' : '' ?>><?= e(__('admin.diff.' . $d)) ?></option><?php endforeach; ?></select></div>
    <?= partial('partials/field', ['name' => 'points', 'type' => 'number', 'label' => __('admin.points'), 'value' => $v('points', 1), 'min' => 1, 'max' => 20]) ?>
    <div class="form-group"><label class="form-label" for="qstatus"><?= e(__('admin.status')) ?></label><select class="input" id="qstatus" name="status"><option value="active" <?= $v('status', 'active') === 'active' ? 'selected' : '' ?>><?= e(__('admin.q_active')) ?></option><option value="retired" <?= $v('status') === 'retired' ? 'selected' : '' ?>><?= e(__('admin.q_retired')) ?></option></select></div>
  </div>
  <?= partial('partials/translation-tabs', ['prefix' => 'q_', 'translations' => $translations, 'withStatus' => true, 'fields' => ['stem' => [__('admin.stem'), 'textarea'], 'scenario' => [__('admin.scenario'), 'textarea'], 'explanation' => [__('exams.explanation'), 'textarea']]]) ?>
  <fieldset class="fieldset"><legend><?= e(__('admin.options')) ?></legend>
    <p class="form-hint"><?= e(__('admin.options_hint')) ?></p>
    <?php if ($err = error_for('options')): ?><p class="form-error"><?= e($err) ?></p><?php endif; ?>
    <div class="table-wrap"><table class="table table--compact"><thead><tr><th scope="col"><?= e(__('admin.correct')) ?></th><th scope="col">EN</th><th scope="col">FR</th><th scope="col"><?= e(__('admin.match_key')) ?></th><th scope="col"><?= e(__('admin.match_label')) ?> EN</th><th scope="col"><?= e(__('admin.match_label')) ?> FR</th></tr></thead><tbody>
      <?php foreach ($rows as $i => $o): $t = $o['translations'] ?? []; ?>
        <tr><td><input type="checkbox" name="options[<?= $i ?>][correct]" value="1" aria-label="<?= e(__('admin.correct')) ?>" <?= !empty($o['is_correct']) ? 'checked' : '' ?>></td>
          <td><input class="input input--sm" name="options[<?= $i ?>][en]" value="<?= e($t['en']['label'] ?? '') ?>" aria-label="EN"></td>
          <td><input class="input input--sm" name="options[<?= $i ?>][fr]" value="<?= e($t['fr']['label'] ?? '') ?>" aria-label="FR"></td>
          <td><input class="input input--sm" name="options[<?= $i ?>][match_key]" value="<?= e($o['match_key'] ?? '') ?>" aria-label="<?= e(__('admin.match_key')) ?>"></td>
          <td><input class="input input--sm" name="options[<?= $i ?>][match_en]" value="<?= e($t['en']['match_label'] ?? '') ?>" aria-label="<?= e(__('admin.match_label')) ?> EN"></td>
          <td><input class="input input--sm" name="options[<?= $i ?>][match_fr]" value="<?= e($t['fr']['match_label'] ?? '') ?>" aria-label="<?= e(__('admin.match_label')) ?> FR"></td></tr>
      <?php endforeach; ?></tbody></table></div>
  </fieldset>
  <fieldset class="fieldset"><legend><?= e(__('admin.free_answers')) ?></legend><div class="form-row form-row--3">
    <?= partial('partials/field', ['name' => 'numeric_value', 'label' => __('field.numeric_value'), 'value' => old('numeric_value', $spec['value'] ?? '')]) ?>
    <?= partial('partials/field', ['name' => 'numeric_tolerance', 'label' => __('admin.tolerance'), 'value' => old('numeric_tolerance', $spec['tolerance'] ?? 0)]) ?>
    <?= partial('partials/field', ['name' => 'accepted_answers', 'type' => 'textarea', 'rows' => 3, 'label' => __('field.accepted_answers'), 'value' => old('accepted_answers', implode("\n", (array) ($spec['accepted'] ?? []))), 'hint' => __('admin.accepted_hint')]) ?>
  </div></fieldset>
  <button class="btn btn--primary" type="submit"><?= e(__('common.save')) ?></button>
</form>
