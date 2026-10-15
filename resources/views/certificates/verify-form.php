<section class="page-hero"><div class="container container--narrow center">
  <p class="eyebrow"><?= icon('qr', 'icon icon--sm') ?> <?= e(__('nav.verify')) ?></p>
  <h1><?= e(__('verify.title')) ?></h1>
  <p class="lead"><?= e(__('verify.lead')) ?></p>
  <form class="card verify-card" method="get" action="<?= e(route('verify.form')) ?>">
    <label class="form-label" for="number"><?= e(__('verify.number_label')) ?></label>
    <input class="input input--lg" id="number" name="number" placeholder="CA-SOC-2026-8F4A21C9" required autocomplete="off" autofocus>
    <p class="form-hint"><?= e(__('verify.hint')) ?></p>
    <button class="btn btn--primary btn--block" type="submit"><?= icon('search', 'icon icon--sm') ?> <?= e(__('verify.submit')) ?></button>
  </form>
  <p class="form-hint"><?= e(__('footer.certificate_notice')) ?></p>
</div></section>
