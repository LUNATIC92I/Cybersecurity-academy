<section class="page-hero"><div class="container">
  <p class="eyebrow"><?= e(__('nav.certifications')) ?></p>
  <h1><?= e(__('certs.title')) ?></h1>
  <p class="lead"><?= e(__('certs.lead')) ?></p>
  <div class="notice notice--info"><?= icon('info') ?> <span><?= e(__('footer.certificate_notice')) ?></span></div>
</div></section>
<section class="section"><div class="container">
  <div class="grid grid--3">
    <?php foreach ($courses as $course): ?>
      <article class="card cert-program reveal accent-<?= e($course['path']['accent'] ?? 'blue') ?>">
        <span class="path-card__icon"><?= icon('award') ?></span>
        <h2 class="h3"><?= e($course['t']['certificate_title'] ?: $course['t']['title']) ?></h2>
        <p class="muted"><?= e($course['path']['t']['title'] ?? '') ?></p>
        <h3 class="h4"><?= e(__('certs.requirements')) ?></h3>
        <ul class="check-list">
          <li><?= icon('check') ?> <?= e(__('certs.req_lessons', ['count' => $course['lesson_count']])) ?></li>
          <?php if ((int) $course['require_all_labs'] === 1): ?><li><?= icon('check') ?> <?= e(__('certs.req_labs', ['count' => $course['lab_count']])) ?></li><?php endif; ?>
          <li><?= icon('check') ?> <?= e(__('certs.req_exam', ['score' => $course['min_exam_score']])) ?></li>
        </ul>
        <a class="btn btn--outline btn--block" href="<?= e(route('courses.show', ['slug' => $course['slug']])) ?>"><?= e(__('certs.view_course')) ?></a>
      </article>
    <?php endforeach; ?>
  </div>
  <div class="grid grid--3 section--tight">
    <?php foreach (['number', 'qr', 'revocation'] as $f): ?>
      <div class="card reveal"><h2 class="h4"><?= e(__('certs.feature.' . $f . '.title')) ?></h2><p><?= e(__('certs.feature.' . $f . '.text')) ?></p></div>
    <?php endforeach; ?>
  </div>
  <p class="center"><a class="btn btn--primary" href="<?= e(route('verify.form')) ?>"><?= icon('qr', 'icon icon--sm') ?> <?= e(__('nav.verify')) ?></a></p>
</div></section>
