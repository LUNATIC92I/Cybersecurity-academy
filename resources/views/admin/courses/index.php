<header class="admin-header"><h1 class="h2"><?= e(__('admin.nav.courses')) ?></h1><a class="btn btn--primary btn--sm" href="<?= e(route('admin.courses.create')) ?>"><?= icon('plus', 'icon icon--sm') ?> <?= e(__('admin.new_course')) ?></a></header>
<div class="table-wrap card"><table class="table">
  <thead><tr><th scope="col"><?= e(__('field.title')) ?></th><th scope="col"><?= e(__('admin.status')) ?></th><th scope="col"><?= e(__('catalog.access')) ?></th><th scope="col"><?= e(__('stats.modules')) ?></th><th scope="col"><?= e(__('admin.enrolments')) ?></th><th scope="col">FR</th></tr></thead>
  <tbody><?php foreach ($courses as $c): $fr = $c['fr_status'] ?? 'missing'; ?>
    <tr><td><a href="<?= e(route('admin.courses.edit', ['id' => $c['id']])) ?>"><?= e($c['title'] ?? $c['slug']) ?></a><br><small class="muted"><?= e($c['slug']) ?></small></td>
      <td><span class="chip chip--xs status-<?= e($c['status']) ?>"><?= e(__('status.' . $c['status'])) ?></span></td>
      <td><?= e(__('course.access.' . $c['access_level'])) ?></td><td><?= (int) $c['modules'] ?></td><td><?= (int) $c['enrolments'] ?></td>
      <td><span class="chip chip--xs status-<?= e($fr) ?>"><?= e(__('status.' . $fr)) ?></span></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
