<section class="section"><div class="container container--narrow center error-page">
  <p class="error-page__code"><?= (int) $status ?></p>
  <h1><?= e(__('error.' . $status . '.title')) ?></h1>
  <p class="lead"><?= e(__('error.' . $status . '.text')) ?></p>
  <div class="btn-row btn-row--center">
    <a class="btn btn--primary" href="<?= e(route('home')) ?>"><?= e(__('nav.home')) ?></a>
    <a class="btn btn--ghost" href="<?= e(route('contact')) ?>"><?= e(__('nav.contact')) ?></a>
  </div>
  <?php if (!empty($debug)): ?><pre class="debug"><?= e(get_class($debug) . ': ' . $debug->getMessage() . "\n" . $debug->getFile() . ':' . $debug->getLine() . "\n\n" . $debug->getTraceAsString()) ?></pre><?php endif; ?>
</div></section>
