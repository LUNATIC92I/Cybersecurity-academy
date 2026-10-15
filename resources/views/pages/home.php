<section class="hero">
  <canvas class="network-canvas" data-network aria-hidden="true"></canvas>
  <div class="container hero__inner">
    <div class="hero__copy">
      <p class="eyebrow reveal"><?= icon('shield-check', 'icon icon--sm') ?> <?= e(__('home.eyebrow')) ?></p>
      <h1 class="hero__title reveal"><?= e(__('home.title')) ?></h1>
      <p class="hero__lead reveal"><?= e(__('home.lead')) ?></p>
      <div class="hero__actions reveal">
        <a class="btn btn--primary btn--lg" href="<?= e(route('catalog')) ?>"><?= e(__('cta.explore_training')) ?> <?= icon('arrow-right') ?></a>
        <a class="btn btn--outline btn--lg" href="<?= e(auth()->check() ? route('dashboard') : route('register')) ?>"><?= e(__('cta.start_learning')) ?></a>
      </div>
      <ul class="hero__points reveal">
        <li><?= icon('check') ?> <?= e(__('home.point1')) ?></li>
        <li><?= icon('check') ?> <?= e(__('home.point2')) ?></li>
        <li><?= icon('check') ?> <?= e(__('home.point3')) ?></li>
      </ul>
    </div>
    <div class="hero__visual reveal" aria-hidden="true">
      <div class="console">
        <div class="console__bar"><span></span><span></span><span></span><em>soc-triage.kql</em></div>
        <pre class="console__body"><code><span class="c-key">SecurityEvent</span>
| <span class="c-op">where</span> TimeGenerated &gt; ago(1h)
| <span class="c-op">where</span> EventID == <span class="c-num">4625</span>
| <span class="c-op">summarize</span> Failures = count()
    <span class="c-op">by</span> IpAddress, bin(TimeGenerated, <span class="c-num">10m</span>)
| <span class="c-op">where</span> Failures &gt;= <span class="c-num">20</span></code></pre>
        <div class="console__alert"><?= icon('alert', 'icon icon--sm') ?> <span><?= e(__('home.console_alert')) ?></span></div>
      </div>
      <div class="hero__badge"><?= icon('award') ?><div><strong>CA-SOC-2026-••••••••</strong><small><?= e(__('home.badge_verified')) ?></small></div></div>
    </div>
  </div>
</section>

<section class="section" aria-labelledby="paths-title">
  <div class="container">
    <header class="section__header reveal">
      <p class="eyebrow"><?= e(__('home.paths_eyebrow')) ?></p>
      <h2 id="paths-title"><?= e(__('home.paths_title')) ?></h2>
      <p class="lead"><?= e(__('home.paths_lead')) ?></p>
    </header>
    <div class="grid grid--3">
      <?php foreach ($paths as $path): ?>
        <article class="card path-card card--interactive reveal accent-<?= e($path['accent']) ?>">
          <span class="path-card__icon"><?= icon($path['icon']) ?></span>
          <h3><a class="stretched-link" href="<?= e(route('paths.show', ['slug' => $path['slug']])) ?>"><?= e($path['t']['title']) ?></a></h3>
          <p><?= e($path['t']['tagline']) ?></p>
          <ul class="meta-list">
            <li><?= icon('layers', 'icon icon--sm') ?> <?= e(trans_choice('course.modules_count', $path['module_count'])) ?></li>
            <li><?= icon('book', 'icon icon--sm') ?> <?= e(trans_choice('course.lessons_count', $path['lesson_count'])) ?></li>
            <li><?= icon('flask', 'icon icon--sm') ?> <?= e(trans_choice('course.labs_count', $path['lab_count'])) ?></li>
          </ul>
          <span class="path-card__link"><?= e(__('home.explore_path')) ?> <?= icon('arrow-right', 'icon icon--sm') ?></span>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section--alt" aria-labelledby="stats-title">
  <div class="container">
    <header class="section__header reveal">
      <p class="eyebrow"><?= e(__('home.stats_eyebrow')) ?></p>
      <h2 id="stats-title"><?= e(__('home.stats_title')) ?></h2>
      <p class="lead"><?= e(__('home.stats_lead')) ?></p>
    </header>
    <dl class="stats-grid reveal">
      <?php foreach (['courses', 'modules', 'lessons', 'labs', 'questions', 'learners', 'certificates', 'labs_passed'] as $key): ?>
        <div class="stat"><dt><?= e(__('stats.' . $key)) ?></dt><dd data-count="<?= (int) $stats[$key] ?>"><?= e(format_number($stats[$key])) ?></dd></div>
      <?php endforeach; ?>
    </dl>
    <p class="form-hint center"><?= e(__('home.stats_note')) ?></p>
  </div>
</section>

<section class="section" aria-labelledby="featured-title">
  <div class="container">
    <header class="section__header section__header--row reveal">
      <div><p class="eyebrow"><?= e(__('home.featured_eyebrow')) ?></p><h2 id="featured-title"><?= e(__('home.featured_title')) ?></h2></div>
      <a class="btn btn--ghost" href="<?= e(route('catalog')) ?>"><?= e(__('home.view_catalog')) ?> <?= icon('arrow-right', 'icon icon--sm') ?></a>
    </header>
    <div class="grid grid--3">
      <?php foreach ($courses as $course): ?><?= partial('partials/course-card', ['course' => $course]) ?><?php endforeach; ?>
    </div>
    <?php if ($labs): ?>
      <h3 class="subheading reveal"><?= e(__('home.featured_labs')) ?></h3>
      <div class="grid grid--3">
        <?php foreach ($labs as $lab): ?><?= partial('partials/lab-card', ['lab' => $lab]) ?><?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section section--alt" aria-labelledby="roadmap-title">
  <div class="container">
    <header class="section__header reveal">
      <p class="eyebrow"><?= e(__('home.roadmap_eyebrow')) ?></p>
      <h2 id="roadmap-title"><?= e(__('home.roadmap_title')) ?></h2>
      <p class="lead"><?= e(__('home.roadmap_lead')) ?></p>
    </header>
    <div class="roadmap" data-roadmap>
      <div class="roadmap__tabs" role="tablist" aria-label="<?= e(__('home.roadmap_title')) ?>">
        <?php foreach ($paths as $i => $path): ?>
          <button type="button" role="tab" id="rm-tab-<?= e($path['code']) ?>" aria-controls="rm-panel-<?= e($path['code']) ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" tabindex="<?= $i === 0 ? '0' : '-1' ?>" class="roadmap__tab accent-<?= e($path['accent']) ?>"><?= icon($path['icon'], 'icon icon--sm') ?> <?= e($path['t']['title']) ?></button>
        <?php endforeach; ?>
      </div>
      <?php foreach ($paths as $i => $path):
          $mods = \App\Services\Localizer::attach(db()->fetchAll(
              'SELECT m.* FROM modules m JOIN learning_path_courses lpc ON lpc.course_id = m.course_id WHERE lpc.path_id = ? AND m.status <> "archived" ORDER BY m.sort_order',
              [(int) $path['id']]
          ), 'module_translations', 'module_id'); ?>
        <ol class="roadmap__panel accent-<?= e($path['accent']) ?>" role="tabpanel" id="rm-panel-<?= e($path['code']) ?>" aria-labelledby="rm-tab-<?= e($path['code']) ?>" <?= $i === 0 ? '' : 'hidden' ?>>
          <?php foreach ($mods as $n => $mod): ?>
            <li class="roadmap__step reveal">
              <span class="roadmap__num"><?= $n + 1 ?></span>
              <div><strong><?= e($mod['t']['title']) ?></strong><small><?= e(level_label($mod['level'])) ?> · <?= e($mod['t']['summary']) ?></small></div>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" aria-labelledby="method-title">
  <div class="container">
    <header class="section__header reveal">
      <p class="eyebrow"><?= e(__('home.method_eyebrow')) ?></p>
      <h2 id="method-title"><?= e(__('home.method_title')) ?></h2>
    </header>
    <div class="grid grid--4">
      <?php foreach ([['book', 'learn'], ['terminal', 'practise'], ['clipboard', 'assess'], ['award', 'certify']] as $n => [$ic, $step]): ?>
        <article class="card step-card reveal">
          <span class="step-card__num"><?= sprintf('%02d', $n + 1) ?></span>
          <span class="step-card__icon"><?= icon($ic) ?></span>
          <h3><?= e(__('home.method.' . $step . '.title')) ?></h3>
          <p><?= e(__('home.method.' . $step . '.text')) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
    <p class="center"><a class="btn btn--ghost" href="<?= e(route('methodology')) ?>"><?= e(__('home.method_more')) ?> <?= icon('arrow-right', 'icon icon--sm') ?></a></p>
  </div>
</section>

<section class="section section--alt" aria-labelledby="cert-title">
  <div class="container split">
    <div class="reveal">
      <p class="eyebrow"><?= e(__('home.cert_eyebrow')) ?></p>
      <h2 id="cert-title"><?= e(__('home.cert_title')) ?></h2>
      <p class="lead"><?= e(__('home.cert_lead')) ?></p>
      <ul class="check-list">
        <li><?= icon('check') ?> <?= e(__('home.cert_point1')) ?></li>
        <li><?= icon('check') ?> <?= e(__('home.cert_point2')) ?></li>
        <li><?= icon('check') ?> <?= e(__('home.cert_point3')) ?></li>
      </ul>
      <p class="form-hint"><?= e(__('footer.certificate_notice')) ?></p>
      <div class="btn-row">
        <a class="btn btn--primary" href="<?= e(route('certifications')) ?>"><?= e(__('nav.certifications')) ?></a>
        <a class="btn btn--outline" href="<?= e(route('verify.form')) ?>"><?= icon('qr', 'icon icon--sm') ?> <?= e(__('nav.verify')) ?></a>
      </div>
    </div>
    <form class="card verify-card reveal" method="get" action="<?= e(route('verify.form')) ?>">
      <h3><?= e(__('verify.quick_title')) ?></h3>
      <label class="form-label" for="home-verify"><?= e(__('verify.number_label')) ?></label>
      <input class="input" id="home-verify" name="number" placeholder="CA-SOC-2026-8F4A21C9" pattern="[A-Za-z]{2}-[A-Za-z]{2,5}-\d{4}-[A-Fa-f0-9]{8}" required autocomplete="off">
      <button class="btn btn--primary btn--block" type="submit"><?= icon('search', 'icon icon--sm') ?> <?= e(__('verify.submit')) ?></button>
    </form>
  </div>
</section>

<section class="section" aria-labelledby="showcase-title">
  <div class="container">
    <header class="section__header reveal">
      <p class="eyebrow"><?= e(__('home.showcase_eyebrow')) ?></p>
      <h2 id="showcase-title"><?= e(__('home.showcase_title')) ?></h2>
    </header>
    <?php if ($publicCertificates): ?>
      <div class="grid grid--3">
        <?php foreach ($publicCertificates as $c): ?>
          <a class="card achievement-card card--interactive reveal" href="<?= e(route('share.certificate', ['token' => $c['share_token']])) ?>">
            <?= icon('award', 'icon icon--lg') ?>
            <strong><?= e(locale() === 'fr' ? $c['title_fr'] : $c['title_en']) ?></strong>
            <span><?= e($c['holder_name']) ?></span>
            <small><?= e(format_date($c['issued_at'])) ?> · <?= e($c['certificate_number']) ?></small>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="card demo-card reveal">
        <span class="chip chip--warning"><?= e(__('home.demo_label')) ?></span>
        <p><?= e(__('home.showcase_empty')) ?></p>
        <div class="demo-card__sample" aria-hidden="true">
          <?= icon('award', 'icon icon--lg') ?>
          <div><strong><?= e(__('home.demo_cert_title')) ?></strong><small><?= e(__('home.demo_cert_meta')) ?></small></div>
        </div>
      </div>
    <?php endif; ?>
    <p class="center"><a class="btn btn--ghost" href="<?= e(route('success')) ?>"><?= e(__('nav.success')) ?> <?= icon('arrow-right', 'icon icon--sm') ?></a></p>
  </div>
</section>

<section class="section section--alt" aria-labelledby="faq-title">
  <div class="container container--narrow">
    <header class="section__header reveal"><p class="eyebrow"><?= e(__('nav.faq')) ?></p><h2 id="faq-title"><?= e(__('home.faq_title')) ?></h2></header>
    <div class="accordion">
      <?php foreach ($faqs as $faq): ?>
        <details class="accordion__item reveal"><summary><?= e($faq['q']) ?></summary><p><?= e($faq['a']) ?></p></details>
      <?php endforeach; ?>
    </div>
    <p class="center"><a class="btn btn--ghost" href="<?= e(route('faq')) ?>"><?= e(__('home.faq_all')) ?></a></p>
  </div>
</section>

<section class="section cta-band">
  <div class="container cta-band__inner reveal">
    <div><h2><?= e(__('home.cta_title')) ?></h2><p><?= e(__('home.cta_lead')) ?></p></div>
    <div class="btn-row">
      <a class="btn btn--primary btn--lg" href="<?= e(route('catalog')) ?>"><?= e(__('cta.explore_training')) ?></a>
      <a class="btn btn--outline btn--lg" href="<?= e(auth()->check() ? route('dashboard') : route('register')) ?>"><?= e(__('cta.start_learning')) ?></a>
    </div>
  </div>
</section>
