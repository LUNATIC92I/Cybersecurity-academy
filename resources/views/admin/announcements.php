<header class="admin-header"><h1 class="h2"><?= e(__('admin.nav.announcements')) ?></h1></header>
<form class="card form" method="post" action="<?= e(route('admin.announcements.store')) ?>"><?= csrf_field() ?>
  <div class="form-row form-row--3">
    <div class="form-group"><label class="form-label" for="level"><?= e(__('admin.level')) ?></label><select class="input" id="level" name="level"><?php foreach (['info', 'success', 'warning'] as $l): ?><option value="<?= $l ?>"><?= e(__('admin.ann_level.' . $l)) ?></option><?php endforeach; ?></select></div>
    <?= partial('partials/field', ['name' => 'starts_at', 'type' => 'date', 'label' => __('admin.starts_at'), 'value' => old('starts_at')]) ?>
    <?= partial('partials/field', ['name' => 'ends_at', 'type' => 'date', 'label' => __('admin.ends_at'), 'value' => old('ends_at')]) ?>
  </div>
  <div class="form-row">
    <fieldset class="fieldset"><legend>EN</legend><?= partial('partials/field', ['name' => 'title_en', 'label' => __('field.title'), 'value' => old('title_en'), 'required' => true]) ?><?= partial('partials/field', ['name' => 'body_en', 'type' => 'textarea', 'rows' => 3, 'label' => __('field.message'), 'value' => old('body_en'), 'required' => true]) ?></fieldset>
    <fieldset class="fieldset"><legend>FR</legend><?= partial('partials/field', ['name' => 'title_fr', 'label' => __('field.title'), 'value' => old('title_fr'), 'required' => true]) ?><?= partial('partials/field', ['name' => 'body_fr', 'type' => 'textarea', 'rows' => 3, 'label' => __('field.message'), 'value' => old('body_fr'), 'required' => true]) ?></fieldset>
  </div>
  <button class="btn btn--primary btn--sm" type="submit"><?= e(__('admin.publish_announcement')) ?></button>
</form>
<section class="card"><ul class="link-list"><?php foreach ($announcements as $a): ?>
  <li><span class="chip chip--xs <?= (int) $a['is_active'] ? 'chip--success' : 'chip--muted' ?>"><?= e((int) $a['is_active'] ? __('admin.active') : __('admin.inactive')) ?></span> <?= e($a['title_en']) ?> / <?= e($a['title_fr']) ?> <small class="muted"><?= e(format_date($a['created_at'])) ?></small>
    <form class="inline-form" method="post" action="<?= e(route('admin.announcements.toggle', ['id' => $a['id']])) ?>"><?= csrf_field() ?><button class="btn btn--ghost btn--xs" type="submit"><?= e((int) $a['is_active'] ? __('admin.deactivate') : __('admin.activate')) ?></button></form></li>
<?php endforeach; ?></ul></section>
