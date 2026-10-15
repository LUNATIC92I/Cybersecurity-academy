<section class="section"><div class="container container--narrow">
  <article class="card share-hero">
    <img class="share-hero__image" src="<?= e(route('share.certificate.image', ['token' => $cert['share_token']])) ?>" alt="<?= e(__('share.image_alt', ['title' => \App\Services\CertificateService::title($cert)])) ?>" width="1200" height="630" loading="lazy">
    <p class="eyebrow"><?= icon('award', 'icon icon--sm') ?> <?= e(__('share.card_headline_cert')) ?></p>
    <h1 class="h2"><?= e(\App\Services\CertificateService::title($cert)) ?></h1>
    <p class="lead"><?= e($cert['holder_name']) ?> · <?= e(format_date($cert['issued_at'])) ?></p>
    <div class="btn-row">
      <a class="btn btn--primary" href="<?= e($verifyUrl) ?>"><?= icon('shield-check', 'icon icon--sm') ?> <?= e(__('share.verify_certificate')) ?></a>
    </div>
    <h2 class="h4"><?= e(__('share.share_title')) ?></h2>
    <?= partial('partials/share', ['url' => $shareUrl, 'subjectType' => 'certificate', 'subjectId' => $cert['id'], 'imageUrl' => route('share.certificate.image', ['token' => $cert['share_token']]), 'canRecord' => auth()->id() === (int) $cert['user_id']]) ?>
    <p class="form-hint"><?= e(__('footer.certificate_notice')) ?></p>
  </article>
</div></section>
