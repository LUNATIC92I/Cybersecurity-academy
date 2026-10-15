<!doctype html>
<html lang="<?= e(locale()) ?>" data-theme="dark">
<head>
<?= partial('partials/head', ['meta' => $meta ?? []]) ?>
</head>
<body>
<a class="skip-link" href="#main"><?= e(__('nav.skip')) ?></a>
<?= partial('partials/header') ?>
<main id="main" tabindex="-1">
  <div class="container flash-zone"><?= partial('partials/flash', ['hideErrorSummary' => $hideErrorSummary ?? false]) ?></div>
  <?= $content ?>
</main>
<?= partial('partials/footer') ?>
</body>
</html>
