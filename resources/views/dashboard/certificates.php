<header class="dash-header"><h1 class="h2"><?= e(__('dashboard.certificates')) ?></h1><p class="muted"><?= e(__('footer.certificate_notice')) ?></p></header>
<?php if ($certificates): ?>
  <div class="stack">
    <?php foreach ($certificates as $c): $shareUrl = absolute_route('share.certificate', ['token' => $c['share_token']]); ?>
      <article class="card cert-card <?= $c['status'] === 'revoked' ? 'cert-card--revoked' : '' ?>">
        <div class="cert-card__head">
          <?= icon('award', 'icon icon--lg') ?>
          <div><h2 class="h3"><?= e(\App\Services\CertificateService::title($c)) ?></h2>
            <p class="muted"><code><?= e($c['certificate_number']) ?></code> · <?= e(__('verify.issued')) ?> <?= e(format_date($c['issued_at'])) ?></p></div>
          <span class="chip <?= $c['status'] === 'valid' ? 'chip--success' : 'chip--danger' ?>"><?= e(__('verify.state.' . $c['status'])) ?></span>
        </div>
        <?php if ($c['status'] === 'valid'): ?>
          <div class="btn-row">
            <a class="btn btn--primary btn--sm" href="<?= e(route('certificates.pdf', ['id' => $c['id']])) ?>?locale=en"><?= icon('download', 'icon icon--sm') ?> PDF (EN)</a>
            <a class="btn btn--primary btn--sm" href="<?= e(route('certificates.pdf', ['id' => $c['id']])) ?>?locale=fr"><?= icon('download', 'icon icon--sm') ?> PDF (FR)</a>
            <a class="btn btn--ghost btn--sm" href="<?= e(route('verify.show', ['number' => $c['certificate_number']])) ?>"><?= icon('shield-check', 'icon icon--sm') ?> <?= e(__('share.verify_certificate')) ?></a>
            <a class="btn btn--ghost btn--sm" href="<?= e(route('verify.qr', ['number' => $c['certificate_number']])) ?>" download><?= icon('qr', 'icon icon--sm') ?> QR</a>
          </div>
          <form class="visibility-form" method="post" action="<?= e(route('certificates.visibility', ['id' => $c['id']])) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="is_public" value="<?= (int) $c['is_public'] ? '0' : '1' ?>">
            <p><?= icon((int) $c['is_public'] ? 'globe' : 'lock', 'icon icon--sm') ?> <?= e((int) $c['is_public'] ? __('share.is_public') : __('share.is_private')) ?></p>
            <button class="btn btn--outline btn--sm" type="submit"><?= e((int) $c['is_public'] ? __('share.make_private') : __('share.make_public')) ?></button>
          </form>
          <?php if ((int) $c['is_public']): ?>
            <?= partial('partials/share', ['url' => $shareUrl, 'subjectType' => 'certificate', 'subjectId' => $c['id'], 'imageUrl' => route('share.certificate.image', ['token' => $c['share_token']]), 'canRecord' => true]) ?>
          <?php else: ?><p class="form-hint"><?= e(__('share.private_hint')) ?></p><?php endif; ?>
        <?php else: ?>
          <p class="text-danger"><?= e(__('certs.revoked_notice', ['date' => format_date($c['revoked_at'])])) ?></p>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <?= partial('partials/empty', ['icon' => 'award', 'title' => __('dashboard.no_certificates_title'), 'text' => __('dashboard.no_certificates')]) ?>
<?php endif; ?>
<?php if ($inProgress): ?>
  <h2 class="subheading"><?= e(__('dashboard.in_progress_certs')) ?></h2>
  <div class="grid grid--2"><?php foreach ($inProgress as $course): ?><?= partial('partials/course-card', ['course' => $course]) ?><?php endforeach; ?></div>
<?php endif; ?>
