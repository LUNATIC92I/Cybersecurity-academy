<section class="section"><div class="container container--narrow">
  <?php if ($state === 'valid' || $state === 'revoked'): $c = $cert; ?>
    <article class="card verify-result verify-result--<?= e($state) ?>">
      <div class="verify-result__status">
        <?= icon($state === 'valid' ? 'shield-check' : 'x-circle', 'icon icon--xl') ?>
        <div><p class="eyebrow"><?= e(__('verify.result')) ?></p><h1 class="h2"><?= e(__('verify.state.' . $state)) ?></h1></div>
      </div>
      <dl class="kv kv--wide">
        <div><dt><?= e(__('verify.number')) ?></dt><dd><code><?= e($c['certificate_number']) ?></code></dd></div>
        <div><dt><?= e(__('verify.holder')) ?></dt><dd><?= e($c['holder_name']) ?></dd></div>
        <div><dt><?= e(__('verify.certification')) ?></dt><dd><?= e(\App\Services\CertificateService::title($c)) ?></dd></div>
        <?php if ($p = \App\Services\CertificateService::pathName($c)): ?><div><dt><?= e(__('verify.path')) ?></dt><dd><?= e($p) ?></dd></div><?php endif; ?>
        <div><dt><?= e(__('verify.issued')) ?></dt><dd><?= e(format_date($c['issued_at'])) ?></dd></div>
        <?php if ($state === 'revoked'): ?><div><dt><?= e(__('verify.revoked_on')) ?></dt><dd><?= e(format_date($c['revoked_at'])) ?></dd></div><?php endif; ?>
        <?php if ($criteria): ?><div><dt><?= e(__('verify.criteria')) ?></dt><dd><?= e(__('verify.criteria_text', [
            'lessons' => $criteria['lessons_completed'] ?? 0, 'labs' => $criteria['labs_passed'] ?? 0,
            'score' => $criteria['exam_score'] ?? 0, 'required' => $criteria['exam_required'] ?? 0])) ?></dd></div><?php endif; ?>
      </dl>
      <div class="verify-result__qr">
        <img src="<?= e(route('verify.qr', ['number' => $c['certificate_number']])) ?>" alt="<?= e(__('verify.qr_alt')) ?>" width="140" height="140" loading="lazy">
        <p class="form-hint"><?= e(__('verify.privacy')) ?></p>
      </div>
      <p class="form-hint"><?= e(__('footer.certificate_notice')) ?></p>
    </article>
  <?php else: ?>
    <article class="card verify-result verify-result--invalid">
      <div class="verify-result__status"><?= icon('alert', 'icon icon--xl') ?>
        <div><p class="eyebrow"><?= e(__('verify.result')) ?></p><h1 class="h2"><?= e(__('verify.state.invalid')) ?></h1></div></div>
      <p><?= e(__('verify.invalid_text', ['number' => mb_substr($number, 0, 40)])) ?></p>
      <a class="btn btn--primary" href="<?= e(route('verify.form')) ?>"><?= e(__('verify.try_again')) ?></a>
    </article>
  <?php endif; ?>
</div></section>
