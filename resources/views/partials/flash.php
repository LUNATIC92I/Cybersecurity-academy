<?php $flash = session()->getFlash('message'); $errs = errors(); ?>
<?php if ($flash): ?>
  <div class="flash flash--<?= e($flash['type']) ?>" role="<?= $flash['type'] === 'error' ? 'alert' : 'status' ?>">
    <?= icon($flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'x-circle' : 'info')) ?>
    <span><?= e($flash['text']) ?></span>
    <button type="button" class="flash__close" data-dismiss aria-label="<?= e(__('common.dismiss')) ?>"><?= icon('close') ?></button>
  </div>
<?php endif; ?>
<?php if ($errs && empty($hideErrorSummary)): ?>
  <div class="flash flash--error" role="alert">
    <?= icon('alert') ?>
    <div>
      <strong><?= e(__('validation.summary')) ?></strong>
      <ul class="flash__list"><?php foreach ($errs as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?></ul>
    </div>
  </div>
<?php endif; ?>
