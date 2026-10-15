<header class="admin-header"><h1 class="h2"><?= e(__('admin.nav.commerce')) ?></h1>
  <span class="chip <?= $provider === 'demo' ? 'chip--warning' : ($configured ? 'chip--success' : 'chip--danger') ?>"><?= e(__('admin.provider_status', ['provider' => $provider, 'state' => $configured ? __('admin.configured') : __('admin.unconfigured')])) ?></span></header>
<?php if ($provider === 'demo'): ?><div class="notice notice--warning"><?= icon('alert') ?> <span><?= e(__('admin.demo_payments_notice')) ?></span></div><?php endif; ?>
<section class="card"><h2 class="h4"><?= e(__('admin.plans')) ?></h2>
  <div class="table-wrap"><table class="table"><thead><tr><th scope="col"><?= e(__('field.title')) ?></th><th scope="col"><?= e(__('admin.interval')) ?></th><th scope="col"><?= e(__('admin.price_cents')) ?></th><th scope="col"><?= e(__('admin.active')) ?></th><th scope="col"></th></tr></thead><tbody>
    <?php foreach ($plans as $p): ?><tr><td><?= e($p['name'] ?? $p['code']) ?></td><td><?= e($p['billing_interval']) ?></td>
      <td colspan="3"><form class="inline-form" method="post" action="<?= e(route('admin.plans.update', ['id' => $p['id']])) ?>"><?= csrf_field() ?>
        <label class="sr-only" for="price-<?= (int) $p['id'] ?>"><?= e(__('admin.price_cents')) ?></label><input class="input input--short" type="number" min="0" id="price-<?= (int) $p['id'] ?>" name="price_cents" value="<?= (int) $p['price_cents'] ?>">
        <label class="checkbox"><input type="checkbox" name="is_active" value="1" <?= (int) $p['is_active'] ? 'checked' : '' ?>> <span><?= e(__('admin.active')) ?></span></label>
        <button class="btn btn--outline btn--xs" type="submit"><?= e(__('common.save')) ?></button></form></td></tr><?php endforeach; ?>
  </tbody></table></div></section>
<div class="dash-grid">
  <section class="card"><h2 class="h4"><?= e(__('admin.coupons')) ?></h2>
    <?php if ($coupons): ?><ul class="link-list"><?php foreach ($coupons as $c): ?><li><code><?= e($c['code']) ?></code> <?= $c['percent_off'] ? (int) $c['percent_off'] . '%' : e(format_money((int) $c['amount_off_cents'])) ?> · <?= (int) $c['redeemed_count'] ?>/<?= $c['max_redemptions'] ?? '∞' ?><?= $c['expires_at'] ? ' · ' . e(format_date($c['expires_at'])) : '' ?>
      <form class="inline-form" method="post" action="<?= e(route('admin.coupons.toggle', ['id' => $c['id']])) ?>"><?= csrf_field() ?><button class="btn btn--ghost btn--xs" type="submit"><?= e((int) $c['is_active'] ? __('admin.deactivate') : __('admin.activate')) ?></button></form></li><?php endforeach; ?></ul>
    <?php else: ?><p class="muted"><?= e(__('admin.none')) ?></p><?php endif; ?></section>
  <form class="card form" method="post" action="<?= e(route('admin.coupons.store')) ?>"><?= csrf_field() ?>
    <h2 class="h4"><?= e(__('admin.new_coupon')) ?></h2>
    <div class="form-row"><?= partial('partials/field', ['name' => 'code', 'label' => __('admin.code'), 'value' => old('code'), 'required' => true]) ?>
      <?= partial('partials/field', ['name' => 'percent_off', 'type' => 'number', 'label' => __('admin.percent_off'), 'value' => old('percent_off'), 'min' => 1, 'max' => 100]) ?></div>
    <div class="form-row"><?= partial('partials/field', ['name' => 'amount_off_cents', 'type' => 'number', 'label' => __('admin.amount_off_cents'), 'value' => old('amount_off_cents'), 'min' => 0]) ?>
      <?= partial('partials/field', ['name' => 'max_redemptions', 'type' => 'number', 'label' => __('admin.max_redemptions'), 'value' => old('max_redemptions'), 'min' => 1]) ?></div>
    <?= partial('partials/field', ['name' => 'expires_at', 'type' => 'date', 'label' => __('admin.expires_at'), 'value' => old('expires_at')]) ?>
    <button class="btn btn--primary btn--sm" type="submit"><?= e(__('common.create')) ?></button></form>
</div>
<section class="card"><h2 class="h4"><?= e(__('dashboard.orders')) ?></h2>
  <?php if (auth()->can('reports.export')): ?><a class="btn btn--ghost btn--sm" href="<?= e(route('admin.export', ['type' => 'orders'])) ?>"><?= icon('download', 'icon icon--sm') ?> CSV</a><?php endif; ?>
  <div class="table-wrap"><table class="table table--compact"><thead><tr><th scope="col"><?= e(__('checkout.reference')) ?></th><th scope="col"><?= e(__('field.email')) ?></th><th scope="col"><?= e(__('admin.item')) ?></th><th scope="col"><?= e(__('checkout.total')) ?></th><th scope="col"><?= e(__('checkout.status')) ?></th><th scope="col"><?= e(__('admin.provider')) ?></th><th scope="col"><?= e(__('exams.col.date')) ?></th></tr></thead><tbody>
    <?php foreach ($orders as $o): ?><tr><td><code><?= e($o['reference']) ?></code></td><td><?= e($o['email']) ?></td><td><?= e($o['item_type'] . ' #' . $o['item_id']) ?></td><td><?= e(format_money((int) $o['total_cents'], $o['currency'])) ?></td><td><?= e(__('checkout.order_status.' . $o['status'])) ?></td><td><?= e($o['provider']) ?></td><td><?= e(format_date($o['created_at'])) ?></td></tr><?php endforeach; ?>
  </tbody></table></div></section>
<section class="card"><h2 class="h4"><?= e(__('admin.subscriptions')) ?></h2>
  <div class="table-wrap"><table class="table table--compact"><thead><tr><th scope="col"><?= e(__('field.email')) ?></th><th scope="col"><?= e(__('admin.plan')) ?></th><th scope="col"><?= e(__('checkout.status')) ?></th><th scope="col"><?= e(__('admin.period_end')) ?></th><th scope="col"><?= e(__('admin.provider')) ?></th></tr></thead><tbody>
    <?php foreach ($subscriptions as $s): ?><tr><td><?= e($s['email']) ?></td><td><?= e($s['code']) ?></td><td><?= e($s['status']) ?></td><td><?= e(format_date($s['current_period_end'])) ?></td><td><?= e($s['provider']) ?></td></tr><?php endforeach; ?>
  </tbody></table></div></section>
