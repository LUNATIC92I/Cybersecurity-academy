<?php
$shareUrl = url(app('request')->path);
$socials = array_filter(['linkedin' => setting('social_linkedin'), 'facebook' => setting('social_facebook'), 'tiktok' => setting('social_tiktok')]);
?>
<footer class="site-footer">
  <div class="container">
    <div class="site-footer__grid">
      <div class="site-footer__brand">
        <a class="brand" href="<?= e(route('home')) ?>"><span class="brand__mark" aria-hidden="true"><?= icon('shield-check') ?></span><span class="brand__name">Cybersecurity<span>-Academy</span></span></a>
        <p><?= e(__('footer.tagline')) ?></p>
        <?= partial('partials/lang-switch') ?>
      </div>
      <nav aria-label="<?= e(__('footer.training')) ?>">
        <h2 class="site-footer__title"><?= e(__('footer.training')) ?></h2>
        <ul>
          <li><a href="<?= e(route('catalog')) ?>"><?= e(__('nav.catalog')) ?></a></li>
          <li><a href="<?= e(route('paths.show', ['slug' => 'soc-analyst'])) ?>"><?= e(__('path.soc')) ?></a></li>
          <li><a href="<?= e(route('paths.show', ['slug' => 'penetration-testing'])) ?>"><?= e(__('path.pen')) ?></a></li>
          <li><a href="<?= e(route('paths.show', ['slug' => 'grc-professional'])) ?>"><?= e(__('path.grc')) ?></a></li>
          <li><a href="<?= e(route('labs.index')) ?>"><?= e(__('nav.labs')) ?></a></li>
          <li><a href="<?= e(route('assessments.index')) ?>"><?= e(__('nav.assessments')) ?></a></li>
        </ul>
      </nav>
      <nav aria-label="<?= e(__('footer.platform')) ?>">
        <h2 class="site-footer__title"><?= e(__('footer.platform')) ?></h2>
        <ul>
          <li><a href="<?= e(route('about')) ?>"><?= e(__('nav.about')) ?></a></li>
          <li><a href="<?= e(route('methodology')) ?>"><?= e(__('nav.methodology')) ?></a></li>
          <li><a href="<?= e(route('certifications')) ?>"><?= e(__('nav.certifications')) ?></a></li>
          <li><a href="<?= e(route('verify.form')) ?>"><?= e(__('nav.verify')) ?></a></li>
          <li><a href="<?= e(route('success')) ?>"><?= e(__('nav.success')) ?></a></li>
          <li><a href="<?= e(route('pricing')) ?>"><?= e(__('nav.pricing')) ?></a></li>
          <li><a href="<?= e(route('faq')) ?>"><?= e(__('nav.faq')) ?></a></li>
          <li><a href="<?= e(route('contact')) ?>"><?= e(__('nav.contact')) ?></a></li>
        </ul>
      </nav>
      <nav aria-label="<?= e(__('footer.legal')) ?>">
        <h2 class="site-footer__title"><?= e(__('footer.legal')) ?></h2>
        <ul>
          <li><a href="<?= e(route('legal.privacy')) ?>"><?= e(__('legal.privacy.title')) ?></a></li>
          <li><a href="<?= e(route('legal.terms')) ?>"><?= e(__('legal.terms.title')) ?></a></li>
          <li><a href="<?= e(route('legal.cookies')) ?>"><?= e(__('legal.cookies.title')) ?></a></li>
          <li><a href="<?= e(route('legal.responsible')) ?>"><?= e(__('legal.responsible.title')) ?></a></li>
        </ul>
      </nav>
      <div>
        <h2 class="site-footer__title"><?= e(__('footer.share')) ?></h2>
        <div class="share-row">
          <a class="icon-btn" href="<?= e(\App\Services\ShareService::linkedInUrl($shareUrl)) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e(__('share.on_linkedin')) ?>"><?= icon('linkedin') ?></a>
          <a class="icon-btn" href="<?= e(\App\Services\ShareService::facebookUrl($shareUrl)) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e(__('share.on_facebook')) ?>"><?= icon('facebook') ?></a>
          <button type="button" class="icon-btn" data-copy="<?= e($shareUrl) ?>" aria-label="<?= e(__('share.copy_link')) ?>"><?= icon('link') ?></button>
        </div>
        <?php if ($socials): ?>
          <h2 class="site-footer__title site-footer__title--spaced"><?= e(__('footer.follow')) ?></h2>
          <div class="share-row">
            <?php foreach ($socials as $network => $link): ?>
              <a class="icon-btn" href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e(ucfirst($network)) ?>"><?= icon($network) ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="site-footer__bottom">
      <p>© <?= gmdate('Y') ?> Cybersecurity-Academy. <?= e(__('footer.rights')) ?></p>
      <p class="muted"><?= e(__('footer.certificate_notice')) ?></p>
    </div>
  </div>
</footer>
<div class="toast" role="status" aria-live="polite" data-toast hidden></div>
