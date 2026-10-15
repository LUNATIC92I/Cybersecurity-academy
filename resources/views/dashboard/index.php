<?php $user = auth()->user(); $hours = intdiv($studySeconds, 3600); $mins = intdiv($studySeconds % 3600, 60); ?>
<header class="dash-header">
  <div><p class="eyebrow"><?= e(__('dashboard.overview')) ?></p><h1 class="h2"><?= e(__('dashboard.welcome', ['name' => $user['display_name']])) ?></h1></div>
  <?php if ($subscription): ?><span class="chip chip--accent"><?= icon('award', 'icon icon--sm') ?> <?= e(__('dashboard.subscription_active', ['date' => format_date($subscription['current_period_end'])])) ?></span><?php endif; ?>
</header>
<?php foreach ($announcements as $a): ?>
  <div class="notice notice--<?= e($a['level']) ?>"><?= icon('bell') ?> <span><strong><?= e($a['t']['title'] ?? '') ?></strong> — <?= e($a['t']['body'] ?? '') ?></span></div>
<?php endforeach; ?>

<?php if ($next): ?>
  <section class="card continue-card">
    <div><p class="eyebrow"><?= e(__('dashboard.continue')) ?></p><h2 class="h3"><?= e($next['lesson']['t']['title'] ?? '') ?></h2><p class="muted"><?= e($next['course']['t']['title']) ?> · <?= e(minutes_label((int) $next['lesson']['duration_minutes'])) ?></p></div>
    <a class="btn btn--primary" href="<?= e(route('lessons.show', ['course' => $next['course']['slug'], 'module' => $next['lesson']['module_slug'], 'lesson' => $next['lesson']['slug']])) ?>"><?= icon('play', 'icon icon--sm') ?> <?= e(__('dashboard.resume')) ?></a>
  </section>
<?php elseif (!$enrolled): ?>
  <?= partial('partials/empty', ['icon' => 'compass', 'title' => __('dashboard.no_courses_title'), 'text' => __('dashboard.no_courses_text'), 'action' => [__('cta.explore_training'), route('dashboard.courses')]]) ?>
<?php endif; ?>

<dl class="stats-grid stats-grid--dash">
  <div class="stat"><dt><?= icon('fire', 'icon icon--sm') ?> <?= e(__('dashboard.streak')) ?></dt><dd><?= e(trans_choice('dashboard.days', $streak)) ?></dd></div>
  <div class="stat"><dt><?= icon('clock', 'icon icon--sm') ?> <?= e(__('dashboard.study_time')) ?></dt><dd><?= e(__('common.hours_minutes', ['h' => $hours, 'm' => $mins])) ?></dd><small class="muted"><?= e(__('dashboard.study_time_note')) ?></small></div>
  <div class="stat"><dt><?= icon('clipboard', 'icon icon--sm') ?> <?= e(__('dashboard.avg_score')) ?></dt><dd><?= (int) $quizStats['attempts'] ? (int) $quizStats['avg_score'] . '%' : '—' ?></dd><small class="muted"><?= e(trans_choice('dashboard.attempts', (int) $quizStats['attempts'])) ?></small></div>
  <div class="stat"><dt><?= icon('flask', 'icon icon--sm') ?> <?= e(__('dashboard.labs_passed')) ?></dt><dd><?= (int) $labStats['passed'] ?></dd><small class="muted"><?= e(trans_choice('dashboard.attempts', (int) $labStats['attempts'])) ?></small></div>
</dl>

<div class="dash-grid">
  <section class="card">
    <h2 class="h4"><?= e(__('dashboard.activity_chart')) ?></h2>
    <div class="spark-chart" role="img" aria-label="<?= e(__('dashboard.activity_aria')) ?>">
      <?php $max = max(1, ...array_column($activity, 'count')); foreach ($activity as $d): ?>
        <span class="spark-chart__bar" data-progress="<?= (int) round($d['count'] * 100 / $max) ?>" title="<?= e($d['date'] . ': ' . $d['count']) ?>"></span>
      <?php endforeach; ?>
    </div>
    <p class="form-hint"><?= e(__('dashboard.activity_note')) ?></p>
  </section>
  <section class="card">
    <h2 class="h4"><?= e(__('dashboard.recent_activity')) ?></h2>
    <?php if ($recentActivity): ?><ul class="activity-list"><?php foreach ($recentActivity as $a): ?><li><span><?= e($a['label']) ?></span><small><?= e(format_datetime($a['created_at'])) ?></small></li><?php endforeach; ?></ul>
    <?php else: ?><p class="muted"><?= e(__('dashboard.no_activity')) ?></p><?php endif; ?>
  </section>
</div>

<?php if ($enrolled): ?>
<section>
  <div class="section__header section__header--row"><h2 class="h3"><?= e(__('dashboard.my_courses')) ?></h2><a class="btn btn--ghost btn--sm" href="<?= e(route('dashboard.courses')) ?>"><?= e(__('common.view_all')) ?></a></div>
  <div class="grid grid--2"><?php foreach (array_slice($enrolled, 0, 4) as $course): ?><?= partial('partials/course-card', ['course' => $course]) ?><?php endforeach; ?></div>
</section>
<?php endif; ?>

<div class="dash-grid">
  <section class="card">
    <h2 class="h4"><?= e(__('dashboard.quiz_scores')) ?></h2>
    <?php if ($recentQuizzes): ?><ul class="attempt-list"><?php foreach ($recentQuizzes as $q): ?>
      <li><a href="<?= e(route('attempts.results', ['id' => $q['id']])) ?>"><?= e($q['t']['title'] ?? $q['slug']) ?></a><strong class="<?= (int) $q['passed'] ? 'text-success' : 'text-warning' ?>"><?= (int) $q['percent'] ?>%</strong><small><?= e(format_date($q['submitted_at'])) ?></small></li>
    <?php endforeach; ?></ul><?php else: ?><p class="muted"><?= e(__('dashboard.no_quizzes')) ?></p><?php endif; ?>
  </section>
  <section class="card">
    <h2 class="h4"><?= e(__('dashboard.lab_results')) ?></h2>
    <?php if ($recentLabs): ?><ul class="attempt-list"><?php foreach ($recentLabs as $l): ?>
      <li><a href="<?= e(route('labs.show', ['slug' => $l['slug']])) ?>"><?= e($l['t']['title'] ?? $l['slug']) ?></a><strong class="<?= (int) $l['passed'] ? 'text-success' : 'text-warning' ?>"><?= (int) $l['percent'] ?>%</strong><small><?= e(format_date($l['submitted_at'])) ?></small></li>
    <?php endforeach; ?></ul><?php else: ?><p class="muted"><?= e(__('dashboard.no_labs')) ?></p><?php endif; ?>
  </section>
</div>

<div class="dash-grid">
  <section class="card">
    <div class="section__header section__header--row"><h2 class="h4"><?= e(__('dashboard.achievements')) ?></h2><a class="btn btn--ghost btn--sm" href="<?= e(route('dashboard.achievements')) ?>"><?= e(__('common.view_all')) ?></a></div>
    <?php if ($achievements): ?><ul class="badge-row"><?php foreach ($achievements as $a): ?><li class="badge-pill" title="<?= e($a['t']['description'] ?? '') ?>"><?= icon($a['icon'], 'icon icon--sm') ?> <?= e($a['t']['title'] ?? $a['code']) ?></li><?php endforeach; ?></ul>
    <?php else: ?><p class="muted"><?= e(__('dashboard.no_achievements')) ?></p><?php endif; ?>
  </section>
  <section class="card">
    <div class="section__header section__header--row"><h2 class="h4"><?= e(__('dashboard.certificates')) ?></h2><a class="btn btn--ghost btn--sm" href="<?= e(route('dashboard.certificates')) ?>"><?= e(__('common.view_all')) ?></a></div>
    <?php if ($certificates): ?><ul class="link-list"><?php foreach ($certificates as $c): ?><li><?= icon('award', 'icon icon--sm') ?> <?= e(\App\Services\CertificateService::title($c)) ?> <small class="muted"><?= e($c['certificate_number']) ?></small></li><?php endforeach; ?></ul>
    <?php else: ?><p class="muted"><?= e(__('dashboard.no_certificates')) ?></p><?php endif; ?>
  </section>
</div>

<section class="card">
  <h2 class="h4"><?= e(__('dashboard.paths_title')) ?></h2>
  <div class="grid grid--3">
    <?php foreach ($paths as $p): ?>
      <a class="path-mini accent-<?= e($p['accent']) ?>" href="<?= e(route('paths.show', ['slug' => $p['slug']])) ?>"><?= icon($p['icon']) ?><span><strong><?= e($p['t']['title']) ?></strong><small><?= e(trans_choice('course.lessons_count', $p['lesson_count'])) ?></small></span></a>
    <?php endforeach; ?>
  </div>
</section>
