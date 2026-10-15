<header class="admin-header"><h1 class="h2"><?= e(__('admin.nav.dashboard')) ?></h1>
  <?php if (!$paymentLive): ?><span class="chip chip--warning"><?= e(__('admin.payments_mode', ['provider' => $paymentProvider])) ?></span><?php endif; ?></header>
<dl class="stats-grid stats-grid--dash">
  <?php foreach (['users_total' => 'users', 'users_30d' => 'user', 'enrollments' => 'book', 'attempts_7d' => 'clipboard', 'certificates' => 'award', 'active_subscriptions' => 'building', 'open_tickets' => 'mail', 'fr_lessons_pending' => 'globe'] as $key => $ic): ?>
    <div class="stat"><dt><?= icon($ic, 'icon icon--sm') ?> <?= e(__('admin.stat.' . $key)) ?></dt><dd><?= e(format_number($stats[$key])) ?></dd></div>
  <?php endforeach; ?>
</dl>
<div class="dash-grid">
  <?php foreach (['signups' => $signups, 'enrolments' => $enrolments] as $label => $series): $m = max(1, ...array_column($series, 'count')); ?>
  <section class="card"><h2 class="h4"><?= e(__('admin.' . $label . '_30d')) ?></h2>
    <div class="spark-chart spark-chart--wide" role="img" aria-label="<?= e(__('admin.' . $label . '_30d')) ?>"><?php foreach ($series as $d): ?><span class="spark-chart__bar" data-progress="<?= (int) round($d['count'] * 100 / $m) ?>" title="<?= e($d['date'] . ': ' . $d['count']) ?>"></span><?php endforeach; ?></div></section>
  <?php endforeach; ?>
</div>
<div class="dash-grid">
  <section class="card"><h2 class="h4"><?= e(__('admin.content_inventory')) ?></h2>
    <dl class="kv"><?php foreach (['courses', 'modules', 'lessons', 'labs', 'questions'] as $k): ?><div><dt><?= e(__('stats.' . $k)) ?></dt><dd><?= (int) $stats[$k] ?></dd></div><?php endforeach; ?></dl></section>
  <section class="card"><h2 class="h4"><?= e(__('admin.revenue')) ?></h2>
    <p class="h2"><?= e(format_money($stats['revenue_cents'], (string) config('payments.currency'))) ?></p>
    <p class="form-hint"><?= e(__('admin.revenue_note', ['demo' => $stats['demo_orders']])) ?></p></section>
</div>
<div class="dash-grid">
  <?php if (auth()->can('audit.view')): ?>
  <section class="card"><h2 class="h4"><?= e(__('admin.recent_audit')) ?></h2>
    <ul class="activity-list"><?php foreach ($recentAudit as $a): ?><li><span><code><?= e($a['action']) ?></code> <?= e($a['display_name'] ?? __('admin.system')) ?></span><small><?= e(format_datetime($a['created_at'])) ?></small></li><?php endforeach; ?></ul>
    <a href="<?= e(route('admin.audit')) ?>"><?= e(__('common.view_all')) ?></a></section>
  <?php endif; ?>
  <?php if (auth()->can('support.manage')): ?>
  <section class="card"><h2 class="h4"><?= e(__('admin.open_tickets')) ?></h2>
    <?php if ($recentTickets): ?><ul class="activity-list"><?php foreach ($recentTickets as $t): ?><li><a href="<?= e(route('admin.support.show', ['id' => $t['id']])) ?>"><?= e($t['subject']) ?></a><small><?= e(format_datetime($t['created_at'])) ?></small></li><?php endforeach; ?></ul>
    <?php else: ?><p class="muted"><?= e(__('admin.no_tickets')) ?></p><?php endif; ?></section>
  <?php endif; ?>
</div>
