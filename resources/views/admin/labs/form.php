<?php $l = $lab ?? []; $v = fn ($k, $d = '') => old($k, $l[$k] ?? $d); $isNew = $lab === null; ?>
<header class="admin-header"><div><a class="link-muted" href="<?= e(route('admin.labs')) ?>">← <?= e(__('admin.nav.labs')) ?></a><h1 class="h2"><?= e($isNew ? __('admin.new_lab') : ($translations['en']['title'] ?? $lab['slug'])) ?></h1></div>
  <?php if (!$isNew): ?><a class="btn btn--ghost btn--sm" href="<?= e(route('labs.show', ['slug' => $lab['slug']])) ?>"><?= icon('eye', 'icon icon--sm') ?> <?= e(__('admin.preview')) ?></a><?php endif; ?></header>
<form class="card form" method="post" enctype="multipart/form-data" action="<?= e($isNew ? route('admin.labs.store') : route('admin.labs.update', ['id' => $lab['id']])) ?>"><?= csrf_field() ?>
  <div class="form-row form-row--3">
    <?php if ($isNew): ?><?= partial('partials/field', ['name' => 'slug', 'label' => __('field.slug'), 'value' => old('slug'), 'required' => true]) ?><?php endif; ?>
    <div class="form-group"><label class="form-label" for="lab_type"><?= e(__('labs.filter_type')) ?></label><select class="input" id="lab_type" name="lab_type"><?php foreach (['simulation', 'download', 'virtual'] as $x): ?><option value="<?= $x ?>" <?= $v('lab_type', 'simulation') === $x ? 'selected' : '' ?>><?= e(__('labs.type.' . $x)) ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label class="form-label" for="difficulty"><?= e(__('catalog.level')) ?></label><select class="input" id="difficulty" name="difficulty"><?php foreach (['beginner', 'intermediate', 'advanced'] as $x): ?><option value="<?= $x ?>" <?= $v('difficulty', 'beginner') === $x ? 'selected' : '' ?>><?= e(level_label($x)) ?></option><?php endforeach; ?></select></div>
    <?= partial('partials/field', ['name' => 'duration_minutes', 'type' => 'number', 'label' => __('admin.duration_minutes'), 'value' => $v('duration_minutes', 45), 'required' => true]) ?>
    <?= partial('partials/field', ['name' => 'pass_percent', 'type' => 'number', 'label' => __('admin.pass_percent'), 'value' => $v('pass_percent', 70), 'min' => 1, 'max' => 100, 'required' => true]) ?>
    <?= partial('partials/field', ['name' => 'max_attempts', 'type' => 'number', 'label' => __('admin.max_attempts'), 'value' => $v('max_attempts'), 'hint' => __('admin.blank_unlimited')]) ?>
    <div class="form-group"><label class="form-label" for="risk_level"><?= e(__('admin.risk_level')) ?></label><select class="input" id="risk_level" name="risk_level"><?php foreach (['standard', 'elevated'] as $x): ?><option value="<?= $x ?>" <?= $v('risk_level', 'standard') === $x ? 'selected' : '' ?>><?= e(__('admin.risk.' . $x)) ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label class="form-label" for="path_id"><?= e(__('catalog.path')) ?></label><select class="input" id="path_id" name="path_id"><option value=""></option><?php foreach ($paths as $p): ?><option value="<?= (int) $p['id'] ?>" <?= (int) $v('path_id') === (int) $p['id'] ? 'selected' : '' ?>><?= e($p['title']) ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label class="form-label" for="course_id"><?= e(__('admin.course')) ?></label><select class="input" id="course_id" name="course_id"><option value=""><?= e(__('admin.standalone')) ?></option><?php foreach ($courses as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) $v('course_id') === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label class="form-label" for="dataset_file"><?= e(__('admin.dataset')) ?></label><select class="input" id="dataset_file" name="dataset_file"><option value=""></option><?php foreach ($datasets as $d): ?><option value="<?= e($d) ?>" <?= $v('dataset_file') === $d ? 'selected' : '' ?>><?= e($d) ?></option><?php endforeach; ?></select></div>
    <?= partial('partials/status-select', ['value' => $v('status', 'draft')]) ?>
  </div>
  <label class="checkbox"><input type="checkbox" name="is_required" value="1" <?= (int) $v('is_required', 1) ? 'checked' : '' ?>> <span><?= e(__('admin.lab_required')) ?></span></label>
  <?= partial('partials/translation-tabs', ['prefix' => 'lab_', 'translations' => $translations, 'withStatus' => true, 'fields' => [
      'title' => [__('field.title'), 'text'], 'summary' => [__('field.summary'), 'textarea'], 'objectives' => [__('labs.section.objectives'), 'textarea'],
      'scenario' => [__('labs.section.scenario'), 'markdown'], 'tools' => [__('labs.section.tools'), 'textarea'], 'prerequisites' => [__('labs.section.prerequisites'), 'textarea'],
      'setup' => [__('labs.section.setup'), 'markdown'], 'instructions' => [__('labs.section.instructions'), 'markdown'], 'expected_results' => [__('labs.section.expected'), 'textarea'],
      'scoring' => [__('labs.section.scoring'), 'textarea'], 'solution' => [__('labs.section.solution'), 'markdown'], 'cleanup' => [__('labs.section.cleanup'), 'textarea'],
      'lessons_learned' => [__('labs.section.lessons_learned'), 'textarea']]]) ?>
  <?php if (!$isNew): ?>
    <fieldset class="fieldset"><legend><?= e(__('admin.add_resource')) ?></legend><div class="form-row form-row--3">
      <div class="form-group"><label class="form-label" for="resource_title"><?= e(__('field.title')) ?></label><input class="input" id="resource_title" name="resource_title" maxlength="190"></div>
      <div class="form-group"><label class="form-label" for="resource_locale"><?= e(__('field.locale')) ?></label><select class="input" id="resource_locale" name="resource_locale"><option value=""><?= e(__('admin.all_languages')) ?></option><option value="en">EN</option><option value="fr">FR</option></select></div>
      <div class="form-group"><label class="form-label" for="resource"><?= e(__('admin.file')) ?></label><input class="input" type="file" id="resource" name="resource"></div>
    </div></fieldset>
  <?php endif; ?>
  <button class="btn btn--primary" type="submit"><?= e(__('common.save')) ?></button>
</form>
<?php if (!$isNew): ?>
<section class="card" id="tasks"><h2 class="h4"><?= e(__('labs.tasks')) ?></h2>
  <p class="form-hint"><?= e(__('admin.task_spec_hint')) ?></p>
  <?php foreach ($tasks as $task): ?>
    <details class="task-editor"><summary>#<?= (int) $task['sort_order'] ?> · <?= e($task['task_type']) ?> · <?= e(mb_substr($task['translations']['en']['prompt'] ?? '', 0, 90)) ?></summary>
      <?= partial('admin/labs/task-form', ['task' => $task, 'action' => route('admin.labs.tasks.update', ['id' => $task['id']])]) ?>
      <form method="post" action="<?= e(route('admin.labs.tasks.delete', ['id' => $task['id']])) ?>"><?= csrf_field() ?><button class="btn btn--ghost btn--xs" type="submit" data-confirm="<?= e(__('admin.confirm')) ?>"><?= e(__('common.delete')) ?></button></form>
    </details>
  <?php endforeach; ?>
  <h3 class="h4"><?= e(__('admin.add_task')) ?></h3>
  <?= partial('admin/labs/task-form', ['task' => null, 'action' => route('admin.labs.tasks.store', ['id' => $lab['id']])]) ?>
</section>
<?php endif; ?>
