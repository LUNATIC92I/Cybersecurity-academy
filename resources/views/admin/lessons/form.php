<?php $l = $lesson ?? []; $v = fn ($k, $d = '') => old($k, $l[$k] ?? $d); $isNew = $lesson === null; ?>
<header class="admin-header"><div><a class="link-muted" href="<?= e(route('admin.modules.edit', ['id' => $module['id']])) ?>">← <?= e(__('admin.back_module')) ?></a><h1 class="h2"><?= e($isNew ? __('admin.new_lesson') : ($translations['en']['title'] ?? $lesson['slug'])) ?></h1></div>
  <?php if (!$isNew && !empty($course)): ?><a class="btn btn--ghost btn--sm" href="<?= e(route('lessons.show', ['course' => $course['slug'], 'module' => $module['slug'], 'lesson' => $lesson['slug']])) ?>"><?= icon('eye', 'icon icon--sm') ?> <?= e(__('admin.preview')) ?></a><?php endif; ?></header>
<form class="card form" method="post" action="<?= e($isNew ? route('admin.lessons.store', ['id' => $module['id']]) : route('admin.lessons.update', ['id' => $lesson['id']])) ?>"><?= csrf_field() ?>
  <div class="form-row form-row--3">
    <?php if ($isNew): ?><?= partial('partials/field', ['name' => 'slug', 'label' => __('field.slug'), 'value' => old('slug'), 'required' => true]) ?><?php endif; ?>
    <div class="form-group"><label class="form-label" for="level"><?= e(__('catalog.level')) ?></label><select class="input" id="level" name="level"><?php foreach (['beginner', 'intermediate', 'advanced'] as $lv): ?><option value="<?= $lv ?>" <?= $v('level', 'beginner') === $lv ? 'selected' : '' ?>><?= e(level_label($lv)) ?></option><?php endforeach; ?></select></div>
    <?= partial('partials/field', ['name' => 'duration_minutes', 'type' => 'number', 'label' => __('admin.duration_minutes'), 'value' => $v('duration_minutes', 30), 'min' => 1, 'required' => true]) ?>
    <?= partial('partials/field', ['name' => 'sort_order', 'type' => 'number', 'label' => __('admin.sort_order'), 'value' => $v('sort_order', 1)]) ?>
    <?= partial('partials/field', ['name' => 'chapter_number', 'type' => 'number', 'label' => __('admin.chapter_number'), 'value' => $v('chapter_number', 1), 'min' => 1]) ?>
    <?= partial('partials/status-select', ['value' => $v('status', 'draft')]) ?>
  </div>
  <label class="checkbox"><input type="checkbox" name="is_required" value="1" <?= (int) $v('is_required', 1) ? 'checked' : '' ?>> <span><?= e(__('admin.lesson_required')) ?></span></label>
  <label class="checkbox"><input type="checkbox" name="is_preview" value="1" <?= (int) $v('is_preview', 0) ? 'checked' : '' ?>> <span><?= e(__('admin.lesson_preview')) ?></span></label>
  <p class="form-hint"><?= e(__('admin.markdown_hint')) ?></p>
  <?= partial('partials/translation-tabs', ['prefix' => 'lesson_', 'translations' => $translations, 'withStatus' => true, 'fields' => [
      'title' => [__('field.title'), 'text'], 'chapter_title' => [__('field.chapter_title'), 'text'], 'summary' => [__('field.summary'), 'textarea'],
      'objectives' => [__('lesson.objectives'), 'textarea'], 'prerequisites' => [__('lesson.prerequisites'), 'textarea'],
      'content' => [__('field.content'), 'markdown'], 'assignment' => [__('lesson.assignment'), 'textarea'],
      'completion_criteria' => [__('lesson.criteria'), 'textarea'], 'references_md' => [__('lesson.references'), 'textarea']]]) ?>
  <button class="btn btn--primary" type="submit"><?= e(__('common.save')) ?></button>
</form>
<?php if (!$isNew): ?>
<section class="card"><h2 class="h4"><?= e(__('lesson.resources')) ?></h2>
  <?php if ($resources): ?><ul class="link-list"><?php foreach ($resources as $r): ?><li><?= e($r['title']) ?> <small class="muted"><?= e($r['original_name']) ?> · <?= e($r['mime_type']) ?> · <?= e($r['locale'] ?? __('admin.all_languages')) ?></small>
    <form class="inline-form" method="post" action="<?= e(route('admin.resources.delete', ['id' => $r['id']])) ?>"><?= csrf_field() ?><button class="btn btn--ghost btn--xs" type="submit" data-confirm="<?= e(__('admin.confirm')) ?>"><?= e(__('common.delete')) ?></button></form></li><?php endforeach; ?></ul><?php endif; ?>
  <form class="form form--inline-grid" method="post" enctype="multipart/form-data" action="<?= e(route('admin.lessons.upload', ['id' => $lesson['id']])) ?>"><?= csrf_field() ?>
    <div class="form-group"><label class="form-label" for="r_title"><?= e(__('field.title')) ?></label><input class="input" id="r_title" name="title" required maxlength="190"></div>
    <div class="form-group"><label class="form-label" for="r_locale"><?= e(__('field.locale')) ?></label><select class="input" id="r_locale" name="locale"><option value=""><?= e(__('admin.all_languages')) ?></option><option value="en">EN</option><option value="fr">FR</option></select></div>
    <div class="form-group"><label class="form-label" for="r_file"><?= e(__('admin.file')) ?></label><input class="input" type="file" id="r_file" name="file" required></div>
    <button class="btn btn--primary btn--sm" type="submit"><?= icon('download', 'icon icon--sm') ?> <?= e(__('admin.upload')) ?></button>
  </form>
  <p class="form-hint"><?= e(__('admin.upload_hint', ['types' => implode(', ', array_keys((array) config('security.upload_allowed'))), 'mb' => (int) (config('security.upload_max_bytes') / 1048576)])) ?></p>
</section>
<?php endif; ?>
