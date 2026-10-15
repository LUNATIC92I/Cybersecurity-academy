<header class="admin-header"><h1 class="h2"><?= e(__('admin.nav.support')) ?></h1></header>
<nav class="pill-nav"><?php foreach (['open', 'pending', 'closed', 'all'] as $s): ?><a href="?status=<?= $s ?>" class="<?= $status === $s ? 'is-active' : '' ?>"><?= e(__('admin.ticket_status.' . $s)) ?></a><?php endforeach; ?></nav>
<div class="table-wrap card"><table class="table">
  <thead><tr><th scope="col"><?= e(__('checkout.reference')) ?></th><th scope="col"><?= e(__('field.subject')) ?></th><th scope="col"><?= e(__('field.category')) ?></th><th scope="col"><?= e(__('field.email')) ?></th><th scope="col"><?= e(__('admin.status')) ?></th><th scope="col"><?= e(__('exams.col.date')) ?></th></tr></thead>
  <tbody><?php foreach ($result['items'] as $t): ?><tr><td><code><?= e($t['reference']) ?></code></td><td><a href="<?= e(route('admin.support.show', ['id' => $t['id']])) ?>"><?= e($t['subject']) ?></a></td><td><?= e(__('contact.category.' . $t['category'])) ?></td><td><?= e($t['email']) ?></td><td><?= e(__('admin.ticket_status.' . $t['status'])) ?></td><td><?= e(format_datetime($t['created_at'])) ?></td></tr><?php endforeach; ?></tbody>
</table></div>
<?= partial('partials/pagination', ['result' => $result]) ?>
