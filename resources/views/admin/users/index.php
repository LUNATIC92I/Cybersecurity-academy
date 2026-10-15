<header class="admin-header"><h1 class="h2"><?= e(__('admin.nav.users')) ?></h1>
  <?php if (auth()->can('reports.export')): ?><a class="btn btn--ghost btn--sm" href="<?= e(route('admin.export', ['type' => 'users'])) ?>"><?= icon('download', 'icon icon--sm') ?> CSV</a><?php endif; ?></header>
<form class="filter-bar card" method="get">
  <div class="form-group form-group--grow"><label class="form-label" for="q"><?= e(__('catalog.search')) ?></label><input class="input" id="q" name="q" value="<?= e($q) ?>" type="search"></div>
  <div class="form-group"><label class="form-label" for="role"><?= e(__('admin.role')) ?></label><select class="input" id="role" name="role"><option value=""><?= e(__('common.all')) ?></option><?php foreach ($roles as $r): ?><option value="<?= e($r['code']) ?>" <?= $role === $r['code'] ? 'selected' : '' ?>><?= e(__('role.' . $r['code'])) ?></option><?php endforeach; ?></select></div>
  <button class="btn btn--primary" type="submit"><?= e(__('catalog.filter')) ?></button>
</form>
<div class="table-wrap card"><table class="table">
  <thead><tr><th scope="col">#</th><th scope="col"><?= e(__('field.display_name')) ?></th><th scope="col"><?= e(__('field.email')) ?></th><th scope="col"><?= e(__('admin.roles')) ?></th><th scope="col"><?= e(__('admin.status')) ?></th><th scope="col"><?= e(__('admin.last_login')) ?></th></tr></thead>
  <tbody><?php foreach ($result['items'] as $u): ?>
    <tr><td><?= (int) $u['id'] ?></td><td><a href="<?= e(route('admin.users.show', ['id' => $u['id']])) ?>"><?= e($u['display_name']) ?></a></td><td><?= e($u['email']) ?></td>
      <td><?php foreach (array_filter(explode(',', (string) $u['roles'])) as $r): ?><span class="chip chip--muted chip--xs"><?= e(__('role.' . $r)) ?></span> <?php endforeach; ?></td>
      <td><span class="chip chip--xs <?= $u['status'] === 'active' ? 'chip--success' : 'chip--danger' ?>"><?= e(__('admin.user_status.' . $u['status'])) ?></span></td>
      <td><?= e(format_datetime($u['last_login_at']) ?: '—') ?></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
<?= partial('partials/pagination', ['result' => $result]) ?>
