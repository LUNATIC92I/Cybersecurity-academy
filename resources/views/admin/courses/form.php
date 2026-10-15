<?php $c = $course ?? []; $v = fn ($k, $d = '') => old($k, $c[$k] ?? $d); $isNew = $course === null; ?>
<header class="admin-header"><div><a class="link-muted" href="<?= e(route('admin.courses')) ?>">← <?= e(__('admin.nav.courses')) ?></a><h1 class="h2"><?= e($isNew ? __('admin.new_course') : ($translations['en']['title'] ?? $course['slug'])) ?></h1></div>
  <?php if (!$isNew): ?><a class="btn btn--ghost btn--sm" href="<?= e(route('courses.show', ['slug' => $course['slug']])) ?>"><?= icon('eye', 'icon icon--sm') ?> <?= e(__('admin.preview')) ?></a><?php endif; ?></header>
<form class="card form" method="post" action="<?= e($isNew ? route('admin.courses.store') : route('admin.courses.update', ['id' => $course['id']])) ?>">
  <?= csrf_field() ?>
  <h2 class="h4"><?= e(__('admin.settings_section')) ?></h2>
  <div class="form-row form-row--3">
    <?= partial('partials/field', ['name' => 'slug', 'label' => __('field.slug'), 'value' => $v('slug'), 'required' => true]) ?>
    <div class="form-group"><label class="form-label" for="level"><?= e(__('catalog.level')) ?></label><select class="input" id="level" name="level"><?php foreach (['beginner', 'intermediate', 'advanced', 'all'] as $l): ?><option value="<?= $l ?>" <?= $v('level', 'beginner') === $l ? 'selected' : '' ?>><?= e(level_label($l)) ?></option><?php endforeach; ?></select></div>
    <?= partial('partials/status-select', ['value' => $v('status', 'draft')]) ?>
    <div class="form-group"><label class="form-label" for="access_level"><?= e(__('catalog.access')) ?></label><select class="input" id="access_level" name="access_level"><?php foreach (['free', 'premium'] as $a): ?><option value="<?= $a ?>" <?= $v('access_level', 'premium') === $a ? 'selected' : '' ?>><?= e(__('course.access.' . $a)) ?></option><?php endforeach; ?></select></div>
    <?= partial('partials/field', ['name' => 'price_cents', 'type' => 'number', 'label' => __('admin.price_cents'), 'value' => $v('price_cents', 0), 'min' => 0]) ?>
    <?= partial('partials/field', ['name' => 'estimated_hours', 'type' => 'number', 'label' => __('admin.estimated_hours'), 'value' => $v('estimated_hours', 0), 'min' => 0]) ?>
  </div>
  <fieldset class="fieldset"><legend><?= e(__('admin.completion_rules')) ?></legend>
    <div class="form-row form-row--3">
      <?= partial('partials/field', ['name' => 'min_exam_score', 'type' => 'number', 'label' => __('admin.min_exam_score'), 'value' => $v('min_exam_score', 70), 'min' => 0, 'max' => 100, 'required' => true]) ?>
      <label class="checkbox"><input type="checkbox" name="require_all_labs" value="1" <?= (int) $v('require_all_labs', 1) ? 'checked' : '' ?>> <span><?= e(__('admin.require_all_labs')) ?></span></label>
      <label class="checkbox"><input type="checkbox" name="certificate_enabled" value="1" <?= (int) $v('certificate_enabled', 1) ? 'checked' : '' ?>> <span><?= e(__('admin.certificate_enabled')) ?></span></label>
      <label class="checkbox"><input type="checkbox" name="is_featured" value="1" <?= (int) $v('is_featured', 0) ? 'checked' : '' ?>> <span><?= e(__('admin.featured')) ?></span></label>
    </div>
    <p class="form-hint"><?= e(__('admin.completion_hint')) ?></p>
  </fieldset>
  <fieldset class="fieldset"><legend><?= e(__('admin.learning_paths')) ?></legend>
    <?php foreach ($paths as $p): ?><label class="checkbox"><input type="checkbox" name="paths[]" value="<?= (int) $p['id'] ?>" <?= in_array((int) $p['id'], $coursePaths, true) ? 'checked' : '' ?>> <span><?= e($p['title']) ?></span></label><?php endforeach; ?>
  </fieldset>
  <h2 class="h4"><?= e(__('admin.translations')) ?></h2>
  <?= partial('partials/translation-tabs', ['prefix' => 'course_', 'translations' => $translations, 'withStatus' => true, 'fields' => [
      'title' => [__('field.title'), 'text'], 'subtitle' => [__('field.subtitle'), 'text'], 'description' => [__('field.description'), 'textarea'],
      'outcomes' => [__('field.outcomes'), 'textarea'], 'audience' => [__('field.audience'), 'textarea'], 'certificate_title' => [__('field.certificate_title'), 'text']]]) ?>
  <button class="btn btn--primary" type="submit"><?= e(__('common.save')) ?></button>
</form>
<?php if (!$isNew): ?>
<section class="card">
  <h2 class="h4"><?= e(__('stats.modules')) ?></h2>
  <div class="table-wrap"><table class="table"><thead><tr><th scope="col">#</th><th scope="col"><?= e(__('field.title')) ?></th><th scope="col"><?= e(__('catalog.level')) ?></th><th scope="col"><?= e(__('admin.status')) ?></th><th scope="col"><?= e(__('stats.lessons')) ?></th></tr></thead>
    <tbody><?php foreach ($modules as $m): ?><tr><td><?= (int) $m['sort_order'] ?></td><td><a href="<?= e(route('admin.modules.edit', ['id' => $m['id']])) ?>"><?= e($m['t']['title'] ?? $m['slug']) ?></a></td><td><?= e(level_label($m['level'])) ?></td><td><span class="chip chip--xs status-<?= e($m['status']) ?>"><?= e(__('status.' . $m['status'])) ?></span></td><td><?= (int) $m['lesson_count'] ?></td></tr><?php endforeach; ?></tbody></table></div>
  <form class="form form--inline-grid" method="post" action="<?= e(route('admin.modules.store', ['id' => $course['id']])) ?>"><?= csrf_field() ?>
    <div class="form-group"><label class="form-label" for="m_slug"><?= e(__('field.slug')) ?></label><input class="input" id="m_slug" name="slug" required pattern="[a-z0-9-]+"></div>
    <div class="form-group"><label class="form-label" for="m_title"><?= e(__('field.title')) ?> (EN)</label><input class="input" id="m_title" name="t[en][title]" required></div>
    <input type="hidden" name="t[en][summary]" value="">
    <div class="form-group"><label class="form-label" for="m_level"><?= e(__('catalog.level')) ?></label><select class="input" id="m_level" name="level"><?php foreach (['beginner', 'intermediate', 'advanced'] as $l): ?><option value="<?= $l ?>"><?= e(level_label($l)) ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label class="form-label" for="m_order"><?= e(__('admin.sort_order')) ?></label><input class="input" type="number" id="m_order" name="sort_order" value="<?= count($modules) + 1 ?>"></div>
    <button class="btn btn--primary btn--sm" type="submit"><?= icon('plus', 'icon icon--sm') ?> <?= e(__('admin.add_module')) ?></button>
  </form>
</section>
<?php endif; ?>
