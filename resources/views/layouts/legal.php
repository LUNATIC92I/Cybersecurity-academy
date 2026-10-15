<?php ob_start(); ?>
<section class="page-hero page-hero--compact"><div class="container">
  <p class="eyebrow"><?= e(__('footer.legal')) ?></p>
  <h1><?= e($meta['title']) ?></h1>
  <p class="lead"><?= e(__('legal.updated', ['date' => format_date('2026-10-01')])) ?></p>
</div></section>
<section class="section section--tight"><div class="container legal-layout">
  <nav class="legal-nav" aria-label="<?= e(__('footer.legal')) ?>">
    <?php foreach (['privacy' => 'legal.privacy', 'terms' => 'legal.terms', 'cookies' => 'legal.cookies', 'responsible' => 'legal.responsible'] as $key => $routeName): ?>
      <a href="<?= e(route($routeName)) ?>" class="<?= $page === $key ? 'is-active' : '' ?>" <?= $page === $key ? 'aria-current="page"' : '' ?>><?= e(__('legal.' . $key . '.title')) ?></a>
    <?php endforeach; ?>
  </nav>
  <article class="prose"><?= $content ?></article>
</div></section>
<?php $inner = ob_get_clean(); echo app('view')->render('layouts/app-raw', ['content' => $inner, 'meta' => $meta], 'layouts/app'); ?>
