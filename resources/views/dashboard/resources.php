<header class="dash-header"><h1 class="h2"><?= e(__('dashboard.resources')) ?></h1><p class="muted"><?= e(__('dashboard.resources_lead')) ?></p></header>
<?php if (!$resources && !$labResources && !$datasets): ?>
  <?= partial('partials/empty', ['icon' => 'download', 'title' => __('dashboard.no_resources_title'), 'text' => __('dashboard.no_resources_text')]) ?>
<?php endif; ?>
<?php if ($resources): ?>
  <section class="card"><h2 class="h4"><?= e(__('dashboard.lesson_resources')) ?></h2>
    <ul class="link-list"><?php foreach ($resources as $r): ?><li><a href="<?= e(route('resources.download', ['id' => $r['id']])) ?>"><?= icon('file', 'icon icon--sm') ?> <?= e($r['title']) ?></a> <small class="muted"><?= e($r['lesson_title'] ?? '') ?> · <?= e($r['original_name']) ?></small></li><?php endforeach; ?></ul></section>
<?php endif; ?>
<?php if ($labResources): ?>
  <section class="card"><h2 class="h4"><?= e(__('dashboard.lab_resources')) ?></h2>
    <ul class="link-list"><?php foreach ($labResources as $r): ?><li><a href="<?= e(route('labs.show', ['slug' => $r['lab_slug']])) ?>"><?= icon('flask', 'icon icon--sm') ?> <?= e($r['title']) ?></a> <small class="muted"><?= e($r['original_name']) ?></small></li><?php endforeach; ?></ul>
    <p class="form-hint"><?= e(__('dashboard.lab_resources_hint')) ?></p></section>
<?php endif; ?>
