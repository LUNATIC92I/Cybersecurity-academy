<section class="page-hero"><div class="container">
  <p class="eyebrow"><?= e(__('nav.success')) ?></p>
  <h1><?= e(__('success.title')) ?></h1>
  <p class="lead"><?= e(__('success.lead')) ?></p>
</div></section>
<section class="section"><div class="container">
  <dl class="stats-grid reveal">
    <?php foreach (['learners', 'lessons_completed', 'labs_passed', 'certificates'] as $key): ?>
      <div class="stat"><dt><?= e(__('stats.' . $key)) ?></dt><dd data-count="<?= (int) $stats[$key] ?>"><?= e(format_number($stats[$key])) ?></dd></div>
    <?php endforeach; ?>
  </dl>
  <p class="form-hint center"><?= e(__('success.live_note')) ?></p>

  <h2 class="subheading"><?= e(__('success.by_path')) ?></h2>
  <div class="card reveal">
    <div class="bar-chart" data-bar-chart>
      <?php $max = max(1, ...array_map(fn ($r) => (int) $r['learners'], $byPath)); foreach ($byPath as $row): ?>
        <div class="bar-chart__row">
          <span class="bar-chart__label"><?= e(__('path.' . strtolower($row['code']))) ?></span>
          <span class="bar-chart__track"><span class="bar-chart__bar" data-progress="<?= (int) round($row['learners'] * 100 / $max) ?>"></span></span>
          <span class="bar-chart__value"><?= e(trans_choice('success.learners_count', (int) $row['learners'])) ?> · <?= e(trans_choice('success.certs_count', (int) $row['certificates'])) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <h2 class="subheading"><?= e(__('success.public_achievements')) ?></h2>
  <?php if ($certs): ?>
    <div class="grid grid--3">
      <?php foreach ($certs as $c): ?>
        <a class="card achievement-card card--interactive reveal" href="<?= e(route('share.certificate', ['token' => $c['share_token']])) ?>">
          <?= icon('award', 'icon icon--lg') ?><strong><?= e(locale() === 'fr' ? $c['title_fr'] : $c['title_en']) ?></strong>
          <span><?= e($c['holder_name']) ?></span><small><?= e(format_date($c['issued_at'])) ?></small>
        </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <?= partial('partials/empty', ['icon' => 'award', 'title' => __('success.none_title'), 'text' => __('success.none_text')]) ?>
  <?php endif; ?>

  <?php if ($profiles): ?>
    <h2 class="subheading"><?= e(__('success.profiles')) ?></h2>
    <div class="grid grid--4">
      <?php foreach ($profiles as $p): ?>
        <a class="card profile-chip card--interactive" href="<?= e(route('share.profile', ['slug' => $p['public_slug']])) ?>">
          <span class="avatar"><?= e(mb_strtoupper(mb_substr($p['display_name'], 0, 1))) ?></span>
          <span><strong><?= e($p['display_name']) ?></strong><small><?= e($p['headline'] ?? '') ?></small></span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <p class="form-hint center"><?= e(__('success.privacy_note')) ?></p>
</div></section>
