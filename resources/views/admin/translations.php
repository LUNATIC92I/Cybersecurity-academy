<header class="admin-header"><h1 class="h2"><?= e(__('admin.nav.translations')) ?></h1></header>
<dl class="stats-grid stats-grid--dash">
  <?php foreach ($summary as $name => $s): ?><div class="stat"><dt><?= e(__('admin.tr.' . $name)) ?></dt><dd><?= (int) $s['published'] ?>/<?= (int) $s['total'] ?></dd><small class="muted"><?= e(__('admin.fr_published')) ?></small></div><?php endforeach; ?>
  <div class="stat"><dt><?= e(__('admin.ui_keys')) ?></dt><dd><?= (int) $uiKeys - count($missingKeys) ?>/<?= (int) $uiKeys ?></dd><small class="muted"><?= e(__('admin.fr_published')) ?></small></div>
</dl>
<?php if ($missingKeys): ?><div class="notice notice--warning"><?= icon('alert') ?> <span><?= e(__('admin.missing_keys', ['count' => count($missingKeys)])) ?> <code><?= e(implode(', ', array_slice($missingKeys, 0, 20))) ?></code></span></div><?php endif; ?>
<p class="muted"><?= e(__('admin.translations_hint')) ?></p>
<?php foreach ($sections as $name => $rows): ?>
  <section class="card"><h2 class="h4"><?= e(__('admin.tr.' . $name)) ?></h2>
    <div class="table-wrap"><table class="table table--compact"><thead><tr><th scope="col">#</th><th scope="col"><?= e(__('field.title')) ?></th><th scope="col">EN</th><th scope="col">FR</th></tr></thead>
      <tbody><?php foreach ($rows as $r): ?><tr><td><?= (int) $r['id'] ?></td><td><?= e($r['title']) ?></td><td><span class="chip chip--xs status-<?= e($r['en_status']) ?>"><?= e(__('status.' . $r['en_status'])) ?></span></td><td><span class="chip chip--xs status-<?= e($r['fr_status']) ?>"><?= e(__('status.' . $r['fr_status'])) ?></span></td></tr><?php endforeach; ?></tbody></table></div>
  </section>
<?php endforeach; ?>
