<?php
$user = auth()->user();
$unread = \App\Services\NotificationService::unreadCount((int) $user['id']);
$items = [
    ['dashboard', 'home', 'dashboard.overview', '/dashboard', true],
    ['dashboard.courses', 'book', 'dashboard.my_courses', '/dashboard/courses', false],
    ['dashboard.resources', 'download', 'dashboard.resources', '/dashboard/resources', false],
    ['dashboard.certificates', 'award', 'dashboard.certificates', '/dashboard/certificates', false],
    ['dashboard.achievements', 'fire', 'dashboard.achievements', '/dashboard/achievements', false],
    ['dashboard.notifications', 'bell', 'dashboard.notifications', '/dashboard/notifications', false],
    ['dashboard.billing', 'clipboard', 'dashboard.billing', '/dashboard/billing', false],
    ['dashboard.settings', 'settings', 'dashboard.settings', '/dashboard/settings', false],
];
ob_start();
?>
<div class="container app-shell">
  <aside class="app-sidebar" aria-label="<?= e(__('dashboard.navigation')) ?>">
    <div class="app-sidebar__user">
      <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($user['display_name'], 0, 1))) ?></span>
      <div><strong><?= e($user['display_name']) ?></strong><small><?= e($user['email']) ?></small></div>
    </div>
    <nav>
      <?php foreach ($items as [$name, $ic, $label, $path, $exact]):
          $active = $exact ? app('request')->path === $path : str_starts_with(app('request')->path, $path); ?>
        <a href="<?= e(route($name)) ?>" class="app-sidebar__link <?= $active ? 'is-active' : '' ?>" <?= $active ? 'aria-current="page"' : '' ?>>
          <?= icon($ic) ?><span><?= e(__($label)) ?></span>
          <?php if ($name === 'dashboard.notifications' && $unread > 0): ?><span class="badge badge--accent" aria-label="<?= e(__('dashboard.unread', ['count' => $unread])) ?>"><?= $unread ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
      <form method="post" action="<?= e(route('logout')) ?>">
        <?= csrf_field() ?>
        <button type="submit" class="app-sidebar__link app-sidebar__link--button"><?= icon('logout') ?><span><?= e(__('nav.logout')) ?></span></button>
      </form>
    </nav>
  </aside>
  <div class="app-content"><?= $content ?></div>
</div>
<?php $inner = ob_get_clean(); echo app('view')->render('layouts/app-raw', ['content' => $inner, 'meta' => $meta ?? []], 'layouts/app'); ?>
