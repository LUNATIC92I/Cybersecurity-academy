<header class="dash-header dash-header--row"><h1 class="h2"><?= e(__('dashboard.notifications')) ?></h1>
  <?php if ($notifications): ?><form method="post" action="<?= e(route('dashboard.notifications.read')) ?>"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit"><?= e(__('dashboard.mark_all_read')) ?></button></form><?php endif; ?></header>
<?php if ($notifications): ?>
  <ul class="notification-list card">
    <?php foreach ($notifications as $n): ?>
      <li class="<?= $n['read_at'] ? '' : 'is-unread' ?>"><?= icon('bell', 'icon icon--sm') ?>
        <div><?php if ($n['link']): ?><a href="<?= e(safe_redirect_path($n['link'])) ?>"><?= e($n['text']) ?></a><?php else: ?><?= e($n['text']) ?><?php endif; ?><small><?= e(format_datetime($n['created_at'])) ?></small></div></li>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <?= partial('partials/empty', ['icon' => 'bell', 'title' => __('dashboard.no_notifications')]) ?>
<?php endif; ?>
