<header class="admin-header"><h1 class="h2"><?= e(__('admin.nav.labs')) ?></h1><a class="btn btn--primary btn--sm" href="<?= e(route('admin.labs.create')) ?>"><?= icon('plus', 'icon icon--sm') ?> <?= e(__('admin.new_lab')) ?></a></header>
<div class="table-wrap card"><table class="table">
  <thead><tr><th scope="col"><?= e(__('field.title')) ?></th><th scope="col"><?= e(__('labs.filter_type')) ?></th><th scope="col"><?= e(__('admin.status')) ?></th><th scope="col"><?= e(__('labs.tasks')) ?></th><th scope="col"><?= e(__('admin.attempts')) ?></th><th scope="col">FR</th></tr></thead>
  <tbody><?php foreach ($labs as $lab): $fr = $lab['fr_status'] ?? 'missing'; ?>
    <tr><td><a href="<?= e(route('admin.labs.edit', ['id' => $lab['id']])) ?>"><?= e($lab['title'] ?? $lab['slug']) ?></a><?php if ($lab['risk_level'] === 'elevated'): ?> <span class="chip chip--warning chip--xs"><?= e(__('labs.elevated')) ?></span><?php endif; ?></td>
      <td><?= e(__('labs.type.' . $lab['lab_type'])) ?></td><td><span class="chip chip--xs status-<?= e($lab['status']) ?>"><?= e(__('status.' . $lab['status'])) ?></span></td>
      <td><?= (int) $lab['tasks'] ?></td><td><?= (int) $lab['attempts'] ?></td><td><span class="chip chip--xs status-<?= e($fr) ?>"><?= e(__('status.' . $fr)) ?></span></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
