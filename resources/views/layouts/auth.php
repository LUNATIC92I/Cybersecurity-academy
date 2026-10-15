<!doctype html>
<html lang="<?= e(locale()) ?>" data-theme="dark">
<head>
<?= partial('partials/head', ['meta' => $meta ?? []]) ?>
</head>
<body class="auth-body">
<a class="skip-link" href="#main"><?= e(__('nav.skip')) ?></a>
<div class="auth-shell">
  <aside class="auth-aside" aria-hidden="true">
    <canvas class="network-canvas" data-network></canvas>
    <div class="auth-aside__content">
      <a class="brand" href="<?= e(route('home')) ?>"><span class="brand__mark"><?= icon('shield-check') ?></span><span class="brand__name">Cybersecurity<span>-Academy</span></span></a>
      <p class="auth-aside__quote"><?= e(__('auth.aside')) ?></p>
      <ul class="check-list">
        <li><?= icon('check') ?> <?= e(__('auth.aside_point1')) ?></li>
        <li><?= icon('check') ?> <?= e(__('auth.aside_point2')) ?></li>
        <li><?= icon('check') ?> <?= e(__('auth.aside_point3')) ?></li>
      </ul>
    </div>
  </aside>
  <main id="main" class="auth-main" tabindex="-1">
    <div class="auth-top">
      <a class="link-muted" href="<?= e(route('home')) ?>">← <?= e(__('nav.home')) ?></a>
      <?= partial('partials/lang-switch') ?>
    </div>
    <div class="auth-card">
      <?= partial('partials/flash') ?>
      <?= $content ?>
    </div>
  </main>
</div>
<div class="toast" role="status" aria-live="polite" data-toast hidden></div>
</body>
</html>
