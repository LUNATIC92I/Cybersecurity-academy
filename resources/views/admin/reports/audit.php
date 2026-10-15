<header class="admin-header"><h1 class="h2"><?= e(__('admin.nav.audit')) ?></h1></header>
<form class="filter-bar card" method="get"><div class="form-group form-group--grow"><label class="form-label" for="action"><?= e(__('admin.action_prefix')) ?></label><input class="input" id="action" name="action" value="<?= e($action) ?>" placeholder="certificate."></div><button class="btn btn--primary" type="submit"><?= e(__('catalog.filter')) ?></button></form>
<div class="table-wrap card"><table class="table table--compact">
  <thead><tr><th scope="col"><?= e(__('exams.col.date')) ?></th><th scope="col"><?= e(__('admin.actor')) ?></th><th scope="col"><?= e(__('admin.action')) ?></th><th scope="col"><?= e(__('admin.entity')) ?></th><th scope="col"><?= e(__('admin.details')) ?></th><th scope="col">IP</th></tr></thead>
  <tbody><?php foreach ($result['items'] as $a): ?><tr><td><?= e(format_datetime($a['created_at'])) ?></td><td><?= e($a['display_name'] ?? __('admin.system')) ?></td><td><code><?= e($a['action']) ?></code></td><td><?= e(trim(($a['entity_type'] ?? '') . ' ' . ($a['entity_id'] ?? ''))) ?></td><td><code class="truncate"><?= e(mb_substr((string) $a['details'], 0, 160)) ?></code></td><td><?= e($a['ip_address'] ?? '') ?></td></tr><?php endforeach; ?></tbody>
</table></div>
<?= partial('partials/pagination', ['result' => $result]) ?>
