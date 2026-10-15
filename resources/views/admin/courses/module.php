<header class="admin-header"><div><a class="link-muted" href="<?= e(route('admin.courses.edit', ['id' => $course['id']])) ?>">← <?= e($course['title'] ?? $course['slug']) ?></a><h1 class="h2"><?= e($translations['en']['title'] ?? $module['slug']) ?></h1></div>
  <a class="btn btn--primary btn--sm" href="<?= e(route('admin.lessons.create', ['id' => $module['id']])) ?>"><?= icon('plus', 'icon icon--sm') ?> <?= e(__('admin.new_lesson')) ?></a></header>
<form class="card form" method="post" action="<?= e(route('admin.modules.update', ['id' => $module['id']])) ?>"><?= csrf_field() ?>
  <div class="form-row form-row--3">
    <div class="form-group"><label class="form-label" for="level"><?= e(__('catalog.level')) ?></label><select class="input" id="level" name="level"><?php foreach (['beginner', 'intermediate', 'advanced'] as $l): ?><option value="<?= $l ?>" <?= $module['level'] === $l ? 'selected' : '' ?>><?= e(level_label($l)) ?></option><?php endforeach; ?></select></div>
    <?= partial('partials/field', ['name' => 'sort_order', 'type' => 'number', 'label' => __('admin.sort_order'), 'value' => $module['sort_order']]) ?>
    <?= partial('partials/status-select', ['value' => $module['status']]) ?>
  </div>
  <?= partial('partials/translation-tabs', ['prefix' => 'module_', 'translations' => $translations, 'withStatus' => true, 'fields' => ['title' => [__('field.title'), 'text'], 'summary' => [__('field.summary'), 'textarea'], 'topics' => [__('field.topics'), 'textarea']]]) ?>
  <button class="btn btn--primary" type="submit"><?= e(__('common.save')) ?></button>
</form>
<section class="card"><h2 class="h4"><?= e(__('stats.lessons')) ?></h2>
  <?php if ($lessons): ?><div class="table-wrap"><table class="table"><thead><tr><th scope="col">#</th><th scope="col"><?= e(__('field.title')) ?></th><th scope="col"><?= e(__('admin.status')) ?></th><th scope="col">FR</th><th scope="col"><?= e(__('exams.col.duration')) ?></th></tr></thead>
    <tbody><?php foreach ($lessons as $l): ?><tr><td><?= (int) $l['sort_order'] ?></td><td><a href="<?= e(route('admin.lessons.edit', ['id' => $l['id']])) ?>"><?= e($l['t']['title'] ?? $l['slug']) ?></a></td><td><span class="chip chip--xs status-<?= e($l['status']) ?>"><?= e(__('status.' . $l['status'])) ?></span></td><td><span class="chip chip--xs status-<?= e($l['fr_status']) ?>"><?= e(__('status.' . $l['fr_status'])) ?></span></td><td><?= e(minutes_label((int) $l['duration_minutes'])) ?></td></tr><?php endforeach; ?></tbody></table></div>
  <?php else: ?><p class="muted"><?= e(__('admin.no_lessons')) ?></p><?php endif; ?></section>
