<section class="section"><div class="container container--narrow">
  <article class="card">
    <span class="chip chip--warning"><?= icon('alert', 'icon icon--sm') ?> <?= e(__('checkout.demo_badge')) ?></span>
    <h1 class="h2"><?= e(__('checkout.demo_title')) ?></h1>
    <p><?= e(__('checkout.demo_text')) ?></p>
    <dl class="kv">
      <div><dt><?= e(__('checkout.reference')) ?></dt><dd><code><?= e($order['reference']) ?></code></dd></div>
      <div><dt><?= e(__('checkout.subtotal')) ?></dt><dd><?= e(format_money((int) $order['subtotal_cents'], $order['currency'])) ?></dd></div>
      <?php if ((int) $order['discount_cents'] > 0): ?><div><dt><?= e(__('checkout.discount')) ?></dt><dd>−<?= e(format_money((int) $order['discount_cents'], $order['currency'])) ?></dd></div><?php endif; ?>
      <div><dt><?= e(__('checkout.total')) ?></dt><dd><strong><?= e(format_money((int) $order['total_cents'], $order['currency'])) ?></strong></dd></div>
      <div><dt><?= e(__('checkout.status')) ?></dt><dd><?= e(__('checkout.order_status.' . $order['status'])) ?></dd></div>
    </dl>
    <?php if ($order['status'] === 'pending'): ?>
      <form method="post" action="<?= e(route('checkout.demo.complete', ['reference' => $order['reference']])) ?>"><?= csrf_field() ?>
        <button class="btn btn--primary" type="submit" data-once><?= e(__('checkout.demo_complete')) ?></button>
        <a class="btn btn--ghost" href="<?= e(route('pricing')) ?>"><?= e(__('common.cancel')) ?></a>
      </form>
    <?php endif; ?>
    <p class="form-hint"><?= e(__('checkout.demo_footer')) ?></p>
  </article>
</div></section>
