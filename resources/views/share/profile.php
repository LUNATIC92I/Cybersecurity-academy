<section class="page-hero page-hero--compact"><div class="container">
  <div class="profile-head"><span class="avatar avatar--lg"><?= e(mb_strtoupper(mb_substr($profile['display_name'], 0, 1))) ?></span>
    <div><h1><?= e($profile['display_name']) ?></h1><?php if ($profile['headline']): ?><p class="lead"><?= e($profile['headline']) ?></p><?php endif; ?></div></div>
</div></section>
<section class="section section--tight"><div class="container">
  <h2 class="subheading"><?= e(__('dashboard.certificates')) ?></h2>
  <?php if ($certs): ?><div class="grid grid--3"><?php foreach ($certs as $c): ?>
    <a class="card achievement-card card--interactive" href="<?= e(route('share.certificate', ['token' => $c['share_token']])) ?>"><?= icon('award', 'icon icon--lg') ?><strong><?= e(\App\Services\CertificateService::title($c)) ?></strong><small><?= e(format_date($c['issued_at'])) ?> · <?= e($c['certificate_number']) ?></small></a>
  <?php endforeach; ?></div><?php else: ?><p class="muted"><?= e(__('share.no_public_certs')) ?></p><?php endif; ?>
  <h2 class="subheading"><?= e(__('dashboard.achievements')) ?></h2>
  <?php if ($badges): ?><div class="grid grid--4"><?php foreach ($badges as $b): ?>
    <a class="card achievement-card card--interactive" href="<?= e(route('share.badge', ['token' => $b['share_token']])) ?>"><?= icon($b['icon'], 'icon icon--lg') ?><strong><?= e($b['t']['title']) ?></strong><small><?= e(format_date($b['earned_at'])) ?></small></a>
  <?php endforeach; ?></div><?php else: ?><p class="muted"><?= e(__('share.no_public_badges')) ?></p><?php endif; ?>
  <?php if ($progress): ?>
    <h2 class="subheading"><?= e(__('share.progress_title')) ?></h2>
    <div class="grid grid--3"><?php foreach ($progress as $course): ?><?= partial('partials/course-card', ['course' => $course]) ?><?php endforeach; ?></div>
  <?php endif; ?>
  <h2 class="subheading"><?= e(__('share.share_title')) ?></h2>
  <?= partial('partials/share', ['url' => $shareUrl, 'subjectType' => 'profile', 'subjectId' => $profile['id'], 'canRecord' => auth()->id() === (int) $profile['id']]) ?>
</div></section>
