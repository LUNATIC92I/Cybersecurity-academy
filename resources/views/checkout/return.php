<section class="section"><div class="container container--narrow">
  <article class="card center">
    <?= icon($order['status'] === 'paid' ? 'check-circle' : 'clock', 'icon icon--xl ' . ($order['status'] === 'paid' ? 'text-success' : 'text-warning')) ?>
    <h1 class="h2"><?= e(__('checkout.return.' . ($order['status'] === 'paid' ? 'paid' : 'pending'))) ?></h1>
    <p><?= e(__('checkout.return_text.' . ($order['status'] === 'paid' ? 'paid' : 'pending'))) ?></p>
    <p class="muted"><?= e(__('checkout.reference')) ?>: <code><?= e($order['reference']) ?></code><?= $order['provider'] === 'demo' ? ' · ' . e(__('checkout.demo_badge')) : '' ?></p>
    <div class="btn-row btn-row--center">
      <a class="btn btn--primary" href="<?= e(route('dashboard.courses')) ?>"><?= e(__('dashboard.my_courses')) ?></a>
      <a class="btn btn--ghost" href="<?= e(route('dashboard.billing')) ?>"><?= e(__('dashboard.billing')) ?></a>
    </div>
  </article>
</div></section>
