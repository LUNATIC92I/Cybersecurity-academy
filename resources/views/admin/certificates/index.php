<header class="admin-header"><h1 class="h2"><?= e(__('admin.nav.certificates')) ?></h1>
  <?php if (auth()->can('reports.export')): ?><a class="btn btn--ghost btn--sm" href="<?= e(route('admin.export', ['type' => 'certificates'])) ?>"><?= icon('download', 'icon icon--sm') ?> CSV</a><?php endif; ?></header>
<div class="dash-grid">
  <form class="card form" method="post" action="<?= e(route('admin.certificates.issue')) ?>"><?= csrf_field() ?>
    <h2 class="h4"><?= e(__('admin.certs.issue_title')) ?></h2><p class="form-hint"><?= e(__('admin.certs.issue_hint')) ?></p>
    <div class="form-group"><label class="form-label" for="ci_email"><?= e(__('field.email')) ?></label><input class="input" id="ci_email" name="email" type="email" required></div>
    <div class="form-group"><label class="form-label" for="ci_course"><?= e(__('admin.course')) ?></label><select class="input" id="ci_course" name="course_id"><?php foreach ($courses as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['title']) ?></option><?php endforeach; ?></select></div>
    <button class="btn btn--primary btn--sm" type="submit"><?= e(__('admin.certs.check_issue')) ?></button></form>
  <section class="card"><h2 class="h4"><?= e(__('admin.certs.events')) ?></h2>
    <ul class="activity-list"><?php foreach ($events as $ev): ?><li><span><code><?= e($ev['certificate_number']) ?></code> <?= e(__('admin.certs.event.' . $ev['event'])) ?> <?= e($ev['actor'] ?? '') ?><?= $ev['notes'] ? ' — ' . e($ev['notes']) : '' ?></span><small><?= e(format_datetime($ev['created_at'])) ?></small></li><?php endforeach; ?></ul></section>
</div>
<form class="filter-bar card" method="get">
  <div class="form-group form-group--grow"><label class="form-label" for="q"><?= e(__('catalog.search')) ?></label><input class="input" id="q" name="q" value="<?= e($q) ?>" type="search"></div>
  <div class="form-group"><label class="form-label" for="status"><?= e(__('admin.status')) ?></label><select class="input" id="status" name="status"><option value=""><?= e(__('common.all')) ?></option><?php foreach (['valid', 'revoked'] as $s): ?><option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(__('verify.state.' . $s)) ?></option><?php endforeach; ?></select></div>
  <button class="btn btn--primary" type="submit"><?= e(__('catalog.filter')) ?></button>
</form>
<div class="table-wrap card"><table class="table">
  <thead><tr><th scope="col"><?= e(__('verify.number')) ?></th><th scope="col"><?= e(__('verify.holder')) ?></th><th scope="col"><?= e(__('verify.certification')) ?></th><th scope="col"><?= e(__('verify.issued')) ?></th><th scope="col"><?= e(__('admin.status')) ?></th><th scope="col"><?= e(__('common.actions')) ?></th></tr></thead>
  <tbody><?php foreach ($result['items'] as $c): ?>
    <tr><td><a href="<?= e(route('verify.show', ['number' => $c['certificate_number']])) ?>"><code><?= e($c['certificate_number']) ?></code></a></td><td><?= e($c['holder_name']) ?><br><small class="muted"><?= e($c['email'] ?? __('admin.erased_account')) ?></small></td><td><?= e($c['title_en']) ?></td><td><?= e(format_date($c['issued_at'])) ?></td>
      <td><span class="chip chip--xs <?= $c['status'] === 'valid' ? 'chip--success' : 'chip--danger' ?>"><?= e(__('verify.state.' . $c['status'])) ?></span></td>
      <td><form class="inline-form" method="post" action="<?= e(route($c['status'] === 'valid' ? 'admin.certificates.revoke' : 'admin.certificates.reinstate', ['id' => $c['id']])) ?>"><?= csrf_field() ?>
        <label class="sr-only" for="reason-<?= (int) $c['id'] ?>"><?= e(__('admin.reason')) ?></label><input class="input input--sm" id="reason-<?= (int) $c['id'] ?>" name="reason" required minlength="5" maxlength="255" placeholder="<?= e(__('admin.reason')) ?>">
        <button class="btn btn--xs <?= $c['status'] === 'valid' ? 'btn--danger' : 'btn--outline' ?>" type="submit" data-confirm="<?= e(__('admin.confirm')) ?>"><?= e($c['status'] === 'valid' ? __('admin.revoke') : __('admin.reinstate')) ?></button></form></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
<?= partial('partials/pagination', ['result' => $result]) ?>
