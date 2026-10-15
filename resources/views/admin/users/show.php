<header class="admin-header"><div><a class="link-muted" href="<?= e(route('admin.users')) ?>">← <?= e(__('admin.nav.users')) ?></a><h1 class="h2"><?= e($user['display_name']) ?></h1><p class="muted"><?= e($user['email']) ?> · #<?= (int) $user['id'] ?> · <?= e(__('admin.joined', ['date' => format_date($user['created_at'])])) ?></p></div>
  <span class="chip <?= $user['status'] === 'active' ? 'chip--success' : 'chip--danger' ?>"><?= e(__('admin.user_status.' . $user['status'])) ?></span></header>
<div class="dash-grid">
  <?php if (auth()->can('roles.manage')): ?>
  <form class="card form" method="post" action="<?= e(route('admin.users.roles', ['id' => $user['id']])) ?>"><?= csrf_field() ?>
    <h2 class="h4"><?= e(__('admin.roles')) ?></h2>
    <?php foreach ($roles as $r): ?><label class="checkbox"><input type="checkbox" name="roles[]" value="<?= e($r['code']) ?>" <?= in_array($r['code'], $userRoles, true) ? 'checked' : '' ?> <?= $r['code'] === 'super_admin' && !auth()->hasRole('super_admin') ? 'disabled' : '' ?>> <span><?= e(__('role.' . $r['code'])) ?></span></label><?php endforeach; ?>
    <button class="btn btn--primary btn--sm" type="submit"><?= e(__('common.save')) ?></button></form>
  <?php endif; ?>
  <?php if (auth()->can('users.manage') && (int) $user['id'] !== auth()->id()): ?>
  <form class="card form" method="post" action="<?= e(route('admin.users.status', ['id' => $user['id']])) ?>"><?= csrf_field() ?>
    <h2 class="h4"><?= e(__('admin.status')) ?></h2>
    <input type="hidden" name="status" value="<?= $user['status'] === 'active' ? 'suspended' : 'active' ?>">
    <p class="muted"><?= e(__('admin.suspend_hint')) ?></p>
    <button class="btn <?= $user['status'] === 'active' ? 'btn--danger' : 'btn--primary' ?> btn--sm" type="submit" data-confirm="<?= e(__('admin.confirm')) ?>"><?= e($user['status'] === 'active' ? __('admin.suspend') : __('admin.reactivate')) ?></button></form>
  <?php endif; ?>
</div>
<section class="card"><h2 class="h4"><?= e(__('admin.enrolments')) ?></h2>
  <?php if ($enrollments): ?><table class="table"><thead><tr><th scope="col"><?= e(__('admin.course')) ?></th><th scope="col"><?= e(__('admin.source')) ?></th><th scope="col"><?= e(__('course.progress')) ?></th><th scope="col"><?= e(__('admin.completed')) ?></th></tr></thead><tbody>
    <?php foreach ($enrollments as $en): ?><tr><td><?= e($en['title']) ?></td><td><?= e($en['access_source']) ?></td><td><?= (int) $en['percent'] ?>%</td><td><?= e(format_date($en['completed_at']) ?: '—') ?></td></tr><?php endforeach; ?></tbody></table>
  <?php else: ?><p class="muted"><?= e(__('admin.none')) ?></p><?php endif; ?></section>
<?php if (auth()->can('users.manage')): ?>
<section class="card"><h2 class="h4"><?= e(__('admin.access_grants')) ?></h2>
  <?php if ($grants): ?><ul class="link-list"><?php foreach ($grants as $g): $expired = $g['expires_at'] && strtotime($g['expires_at'] . ' UTC') <= time(); ?>
    <li><?= e($g['course_id'] ? __('admin.grant_course', ['id' => $g['course_id']]) : ($g['path_id'] ? __('admin.grant_path', ['id' => $g['path_id']]) : __('dashboard.grant_all'))) ?> · <?= e($g['source']) ?> · <?= e($g['reason'] ?? '') ?>
      <?php if ($expired): ?><span class="chip chip--muted chip--xs"><?= e(__('admin.expired')) ?></span><?php else: ?><form class="inline-form" method="post" action="<?= e(route('admin.grants.revoke', ['id' => $g['id']])) ?>"><?= csrf_field() ?><button class="btn btn--ghost btn--xs" type="submit"><?= e(__('admin.revoke')) ?></button></form><?php endif; ?></li>
  <?php endforeach; ?></ul><?php endif; ?>
  <form class="form form--inline-grid" method="post" action="<?= e(route('admin.users.grant', ['id' => $user['id']])) ?>"><?= csrf_field() ?>
    <div class="form-group"><label class="form-label" for="scope"><?= e(__('admin.scope')) ?></label><select class="input" id="scope" name="scope"><option value="all"><?= e(__('dashboard.grant_all')) ?></option><option value="course"><?= e(__('admin.course')) ?></option><option value="path"><?= e(__('catalog.path')) ?></option></select></div>
    <div class="form-group"><label class="form-label" for="course_id"><?= e(__('admin.course')) ?></label><select class="input" id="course_id" name="course_id"><option value=""></option><?php foreach ($courses as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['title']) ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label class="form-label" for="path_id"><?= e(__('catalog.path')) ?></label><select class="input" id="path_id" name="path_id"><option value=""></option><?php foreach ($paths as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e($p['title']) ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label class="form-label" for="reason"><?= e(__('admin.reason')) ?></label><input class="input" id="reason" name="reason" required maxlength="255"></div>
    <div class="form-group"><label class="form-label" for="expires_at"><?= e(__('admin.expires_at')) ?></label><input class="input" type="date" id="expires_at" name="expires_at"></div>
    <button class="btn btn--primary btn--sm" type="submit"><?= e(__('admin.grant')) ?></button>
  </form></section>
<?php endif; ?>
<div class="dash-grid">
  <section class="card"><h2 class="h4"><?= e(__('dashboard.certificates')) ?></h2>
    <?php if ($certificates): ?><ul class="link-list"><?php foreach ($certificates as $c): ?><li><code><?= e($c['certificate_number']) ?></code> <?= e($c['title_en']) ?> <span class="chip chip--xs"><?= e(__('verify.state.' . $c['status'])) ?></span></li><?php endforeach; ?></ul><?php else: ?><p class="muted"><?= e(__('admin.none')) ?></p><?php endif; ?></section>
  <section class="card"><h2 class="h4"><?= e(__('admin.recent_logins')) ?></h2>
    <ul class="activity-list"><?php foreach ($logins as $l): ?><li><span class="<?= (int) $l['succeeded'] ? 'text-success' : 'text-danger' ?>"><?= e((int) $l['succeeded'] ? __('admin.login_ok') : __('admin.login_failed')) ?> · <?= e($l['ip_address']) ?></span><small><?= e(format_datetime($l['created_at'])) ?></small></li><?php endforeach; ?></ul></section>
</div>
