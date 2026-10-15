<?php $user = auth()->user(); ?>
<header class="site-header" data-header>
  <div class="container site-header__inner">
    <a class="brand" href="<?= e(route('home')) ?>" aria-label="Cybersecurity-Academy — <?= e(__('nav.home')) ?>">
      <span class="brand__mark" aria-hidden="true"><?= icon('shield-check') ?></span>
      <span class="brand__name">Cybersecurity<span>-Academy</span></span>
    </a>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="main-nav" data-nav-toggle>
      <?= icon('menu') ?><span class="sr-only"><?= e(__('nav.menu')) ?></span>
    </button>
    <nav id="main-nav" class="main-nav" aria-label="<?= e(__('nav.main')) ?>" data-nav>
      <ul class="main-nav__list">
        <li class="has-dropdown">
          <button type="button" class="main-nav__link <?= is_active_path('/catalog') || is_active_path('/paths') || is_active_path('/courses') ? 'is-active' : '' ?>" aria-expanded="false" data-dropdown><?= e(__('nav.training')) ?> <?= icon('chevron-down', 'icon icon--sm') ?></button>
          <div class="dropdown">
            <a href="<?= e(route('catalog')) ?>" class="dropdown__item"><?= icon('layers') ?><span><strong><?= e(__('nav.catalog')) ?></strong><small><?= e(__('nav.catalog_hint')) ?></small></span></a>
            <a href="<?= e(route('paths.show', ['slug' => 'soc-analyst'])) ?>" class="dropdown__item"><?= icon('radar') ?><span><strong><?= e(__('path.soc')) ?></strong><small><?= e(__('nav.soc_hint')) ?></small></span></a>
            <a href="<?= e(route('paths.show', ['slug' => 'penetration-testing'])) ?>" class="dropdown__item"><?= icon('target') ?><span><strong><?= e(__('path.pen')) ?></strong><small><?= e(__('nav.pen_hint')) ?></small></span></a>
            <a href="<?= e(route('paths.show', ['slug' => 'grc-professional'])) ?>" class="dropdown__item"><?= icon('scale') ?><span><strong><?= e(__('path.grc')) ?></strong><small><?= e(__('nav.grc_hint')) ?></small></span></a>
          </div>
        </li>
        <li><a class="main-nav__link <?= is_active_path('/labs') ? 'is-active' : '' ?>" href="<?= e(route('labs.index')) ?>"><?= e(__('nav.labs')) ?></a></li>
        <li><a class="main-nav__link <?= is_active_path('/assessments') ? 'is-active' : '' ?>" href="<?= e(route('assessments.index')) ?>"><?= e(__('nav.assessments')) ?></a></li>
        <li class="has-dropdown">
          <button type="button" class="main-nav__link <?= is_active_path('/certifications') || is_active_path('/verify') ? 'is-active' : '' ?>" aria-expanded="false" data-dropdown><?= e(__('nav.certifications')) ?> <?= icon('chevron-down', 'icon icon--sm') ?></button>
          <div class="dropdown">
            <a href="<?= e(route('certifications')) ?>" class="dropdown__item"><?= icon('award') ?><span><strong><?= e(__('nav.certifications')) ?></strong><small><?= e(__('nav.certs_hint')) ?></small></span></a>
            <a href="<?= e(route('verify.form')) ?>" class="dropdown__item"><?= icon('qr') ?><span><strong><?= e(__('nav.verify')) ?></strong><small><?= e(__('nav.verify_hint')) ?></small></span></a>
          </div>
        </li>
        <li><a class="main-nav__link <?= is_active_path('/pricing') ? 'is-active' : '' ?>" href="<?= e(route('pricing')) ?>"><?= e(__('nav.pricing')) ?></a></li>
        <li class="has-dropdown">
          <button type="button" class="main-nav__link" aria-expanded="false" data-dropdown><?= e(__('nav.more')) ?> <?= icon('chevron-down', 'icon icon--sm') ?></button>
          <div class="dropdown dropdown--compact">
            <a href="<?= e(route('about')) ?>" class="dropdown__item"><?= e(__('nav.about')) ?></a>
            <a href="<?= e(route('methodology')) ?>" class="dropdown__item"><?= e(__('nav.methodology')) ?></a>
            <a href="<?= e(route('success')) ?>" class="dropdown__item"><?= e(__('nav.success')) ?></a>
            <a href="<?= e(route('faq')) ?>" class="dropdown__item"><?= e(__('nav.faq')) ?></a>
            <a href="<?= e(route('contact')) ?>" class="dropdown__item"><?= e(__('nav.contact')) ?></a>
          </div>
        </li>
      </ul>
      <div class="main-nav__actions">
        <?= partial('partials/lang-switch') ?>
        <button type="button" class="icon-btn" data-theme-toggle aria-label="<?= e(__('nav.toggle_theme')) ?>"><?= icon('sun', 'icon theme-icon-light') ?><?= icon('moon', 'icon theme-icon-dark') ?></button>
        <?php if ($user): ?>
          <?php if (auth()->isStaff()): ?><a class="btn btn--ghost btn--sm" href="<?= e(route('admin.dashboard')) ?>"><?= e(__('nav.admin')) ?></a><?php endif; ?>
          <a class="btn btn--primary btn--sm" href="<?= e(route('dashboard')) ?>"><?= icon('user', 'icon icon--sm') ?> <?= e(__('nav.dashboard')) ?></a>
        <?php else: ?>
          <a class="btn btn--ghost btn--sm" href="<?= e(route('login')) ?>"><?= e(__('nav.login')) ?></a>
          <a class="btn btn--primary btn--sm" href="<?= e(route('register')) ?>"><?= e(__('cta.start_learning')) ?></a>
        <?php endif; ?>
      </div>
    </nav>
  </div>
</header>
<?php $banner = (string) setting('maintenance_banner'); if ($banner !== ''): ?>
<div class="site-banner" role="status"><div class="container"><?= icon('info', 'icon icon--sm') ?> <?= e($banner) ?></div></div>
<?php endif; ?>
