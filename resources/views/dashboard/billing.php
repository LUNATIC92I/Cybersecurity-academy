<header class="dash-header"><h1 class="h2"><?= e(__('dashboard.billing')) ?></h1></header>
<section class="card">
  <h2 class="h4"><?= e(__('dashboard.subscription')) ?></h2>
  <?php if ($subscription): ?>
    <p><?= e(__('dashboard.subscription_active', ['date' => format_date($subscription['current_period_end'])])) ?> (<?= e($subscription['plan_code']) ?><?= $subscription['provider'] === 'demo' ? ' · ' . e(__('checkout.demo_badge')) : '' ?>)</p>
  <?php else: ?><p class="muted"><?= e(__('dashboard.no_subscription')) ?></p><a class="btn btn--primary btn--sm" href="<?= e(route('pricing')) ?>"><?= e(__('nav.pricing')) ?></a><?php endif; ?>
</section>
<?php if ($grants): ?>
<section class="card"><h2 class="h4"><?= e(__('dashboard.access_grants')) ?></h2>
  <ul class="link-list"><?php foreach ($grants as $g): ?><li><?= e($g['course_title'] ?? ($g['path_id'] ? __('dashboard.grant_path') : __('dashboard.grant_all'))) ?> <small class="muted"><?= e(__('dashboard.grant_source.' . $g['source'])) ?><?= $g['expires_at'] ? ' · ' . e(__('dashboard.expires', ['date' => format_date($g['expires_at'])])) : '' ?></small></li><?php endforeach; ?></ul></section>
<?php endif; ?>
<section class="card"><h2 class="h4"><?= e(__('dashboard.orders')) ?></h2>
  <?php if ($orders): ?><div class="table-wrap"><table class="table">
    <thead><tr><th scope="col"><?= e(__('checkout.reference')) ?></th><th scope="col"><?= e(__('checkout.total')) ?></th><th scope="col"><?= e(__('checkout.status')) ?></th><th scope="col"><?= e(__('exams.col.date')) ?></th></tr></thead>
    <tbody><?php foreach ($orders as $o): ?><tr><td><code><?= e($o['reference']) ?></code><?= $o['provider'] === 'demo' ? ' <span class="chip chip--warning chip--xs">' . e(__('checkout.demo_badge')) . '</span>' : '' ?></td><td><?= e(format_money((int) $o['total_cents'], $o['currency'])) ?></td><td><?= e(__('checkout.order_status.' . $o['status'])) ?></td><td><?= e(format_date($o['created_at'])) ?></td></tr><?php endforeach; ?></tbody>
  </table></div><?php else: ?><p class="muted"><?= e(__('dashboard.no_orders')) ?></p><?php endif; ?>
  <p class="form-hint"><?= e(__('pricing.no_card_storage')) ?></p>
</section>
