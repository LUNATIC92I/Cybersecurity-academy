<?php
$t = $course['t'];
$accent = $course['path']['accent'] ?? 'blue';
$finalExam = null; $otherQuizzes = [];
foreach ($quizzes as $q) { if ($q['kind'] === 'final_exam') { $finalExam = $q; } else { $otherQuizzes[] = $q; } }
?>
<section class="page-hero accent-<?= e($accent) ?>"><div class="container course-hero">
  <div>
    <nav class="breadcrumb" aria-label="<?= e(__('common.breadcrumb')) ?>"><a href="<?= e(route('catalog')) ?>"><?= e(__('nav.catalog')) ?></a><?php if ($course['path']): ?> / <a href="<?= e(route('paths.show', ['slug' => $course['path']['slug']])) ?>"><?= e($course['path']['t']['title']) ?></a><?php endif; ?></nav>
    <h1><?= e($t['title']) ?></h1>
    <p class="lead"><?= e($t['subtitle']) ?></p>
    <?php if (!empty($t['_fallback'])): ?><div class="notice notice--info"><?= icon('globe') ?> <span><?= e(__('content.fallback')) ?></span></div><?php endif; ?>
    <?php if ($course['status'] !== 'published'): ?><div class="notice notice--warning"><?= icon('eye') ?> <span><?= e(__('content.preview_notice', ['status' => __('status.' . $course['status'])])) ?></span></div><?php endif; ?>
    <ul class="meta-list meta-list--hero">
      <li><?= icon('chart', 'icon icon--sm') ?> <?= e(level_label($course['level'])) ?></li>
      <li><?= icon('layers', 'icon icon--sm') ?> <?= e(trans_choice('course.modules_count', $course['module_count'])) ?></li>
      <li><?= icon('book', 'icon icon--sm') ?> <?= e(trans_choice('course.lessons_count', $course['lesson_count'])) ?> · <?= e(minutes_label($course['lesson_minutes'])) ?></li>
      <li><?= icon('flask', 'icon icon--sm') ?> <?= e(trans_choice('course.labs_count', $course['lab_count'])) ?></li>
      <li><?= icon('globe', 'icon icon--sm') ?> EN · FR</li>
    </ul>
  </div>
  <aside class="card enroll-card">
    <?php if ($certificate): ?>
      <span class="chip chip--success"><?= icon('award', 'icon icon--sm') ?> <?= e(__('course.certified')) ?></span>
      <p><?= e(__('course.certificate_number', ['number' => $certificate['certificate_number']])) ?></p>
      <a class="btn btn--primary btn--block" href="<?= e(route('dashboard.certificates')) ?>"><?= e(__('course.view_certificate')) ?></a>
    <?php elseif ($enrolled): ?>
      <?= partial('partials/progress', ['percent' => (int) ($progress['percent'] ?? 0)]) ?>
      <?php if ($next): ?><a class="btn btn--primary btn--block" href="<?= e(route('lessons.show', ['course' => $course['slug'], 'module' => $next['module_slug'], 'lesson' => $next['slug']])) ?>"><?= icon('play', 'icon icon--sm') ?> <?= e(__('course.continue')) ?></a>
      <?php elseif ($finalExam): ?><a class="btn btn--primary btn--block" href="<?= e(route('assessments.show', ['slug' => $finalExam['slug']])) ?>"><?= e(__('course.take_exam')) ?></a><?php endif; ?>
    <?php else: ?>
      <p class="enroll-card__price"><?= $course['access_level'] === 'free' ? e(__('pricing.free')) : e(format_money((int) $course['price_cents'], $course['currency'])) ?></p>
      <?php if (!auth()->check()): ?>
        <a class="btn btn--primary btn--block" href="<?= e(route('register')) ?>"><?= e(__('course.register_to_enroll')) ?></a>
        <a class="btn btn--ghost btn--block" href="<?= e(route('login')) ?>"><?= e(__('nav.login')) ?></a>
      <?php elseif ($accessSource !== null): ?>
        <form method="post" action="<?= e(route('courses.enroll', ['slug' => $course['slug']])) ?>"><?= csrf_field() ?><button class="btn btn--primary btn--block" type="submit"><?= e(__('course.enroll')) ?></button></form>
        <p class="form-hint"><?= e(__('course.access_via.' . $accessSource)) ?></p>
      <?php else: ?>
        <a class="btn btn--primary btn--block" href="<?= e(route('pricing')) ?>#course-<?= (int) $course['id'] ?>"><?= e(__('course.get_access')) ?></a>
        <p class="form-hint"><?= e(__('course.preview_available')) ?></p>
      <?php endif; ?>
    <?php endif; ?>
    <ul class="check-list check-list--compact">
      <li><?= icon('check') ?> <?= e(__('course.feature_lessons')) ?></li>
      <li><?= icon('check') ?> <?= e(__('course.feature_labs')) ?></li>
      <li><?= icon('check') ?> <?= e(__('course.feature_cert')) ?></li>
    </ul>
  </aside>
</div></section>

<section class="section"><div class="container split split--wide">
  <div>
    <div class="prose reveal">
      <h2><?= e(__('course.about')) ?></h2>
      <p><?= e($t['description']) ?></p>
      <h2><?= e(__('course.outcomes')) ?></h2>
      <ul class="check-list"><?php foreach (\App\Services\Localizer::lines($t['outcomes']) as $o): ?><li><?= icon('check') ?> <?= e($o) ?></li><?php endforeach; ?></ul>
      <?php if (!empty($t['audience'])): ?><h2><?= e(__('course.audience')) ?></h2><p><?= e($t['audience']) ?></p><?php endif; ?>
    </div>

    <h2 class="subheading"><?= e(__('course.syllabus')) ?></h2>
    <div class="syllabus">
      <?php foreach ($modules as $n => $module): $mt = $module['t']; ?>
        <details class="syllabus__module reveal" <?= $n === 0 ? 'open' : '' ?>>
          <summary>
            <span class="syllabus__num"><?= sprintf('%02d', $n + 1) ?></span>
            <span class="syllabus__title"><strong><?= e($mt['title']) ?></strong><small><?= e(level_label($module['level'])) ?> · <?= $module['lessons'] ? e(trans_choice('course.lessons_count', count($module['lessons']))) : e(__('course.in_development')) ?></small></span>
          </summary>
          <p class="muted"><?= e($mt['summary']) ?></p>
          <?php if ($module['lessons']): ?>
            <ul class="lesson-list">
              <?php foreach ($module['lessons'] as $lesson): $done = in_array((int) $lesson['id'], $completed, true); ?>
                <li class="<?= $done ? 'is-done' : '' ?>">
                  <?= icon($done ? 'check-circle' : ((int) $lesson['is_preview'] === 1 ? 'eye' : 'book'), 'icon icon--sm') ?>
                  <?php if (auth()->check()): ?>
                    <a href="<?= e(route('lessons.show', ['course' => $course['slug'], 'module' => $module['slug'], 'lesson' => $lesson['slug']])) ?>"><?= e($lesson['t']['title'] ?? $lesson['slug']) ?></a>
                  <?php else: ?><span><?= e($lesson['t']['title'] ?? $lesson['slug']) ?></span><?php endif; ?>
                  <span class="lesson-list__meta"><?php if ((int) $lesson['is_preview'] === 1): ?><span class="chip chip--muted chip--xs"><?= e(__('course.preview')) ?></span><?php endif; ?> <?= e(minutes_label((int) $lesson['duration_minutes'])) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <?php if ($module['topics']): ?>
            <p class="syllabus__topics-title"><?= e(__('course.topics')) ?></p>
            <ul class="tag-list"><?php foreach ($module['topics'] as $topic): ?><li><?= e($topic) ?></li><?php endforeach; ?></ul>
          <?php endif; ?>
        </details>
      <?php endforeach; ?>
    </div>
  </div>

  <aside class="stack">
    <?php if ($status): ?>
      <div class="card reveal">
        <h2 class="h4"><?= e(__('course.completion_title')) ?></h2>
        <ul class="requirement-list">
          <li class="<?= $status['lessons']['done'] >= $status['lessons']['total'] ? 'is-met' : '' ?>"><?= icon($status['lessons']['done'] >= $status['lessons']['total'] ? 'check-circle' : 'clock') ?> <?= e(__('course.req_lessons', ['done' => $status['lessons']['done'], 'total' => $status['lessons']['total']])) ?></li>
          <?php if ($status['labs']['total'] > 0): ?><li class="<?= $status['labs']['done'] >= $status['labs']['total'] ? 'is-met' : '' ?>"><?= icon($status['labs']['done'] >= $status['labs']['total'] ? 'check-circle' : 'clock') ?> <?= e(__('course.req_labs', ['done' => $status['labs']['done'], 'total' => $status['labs']['total']])) ?></li><?php endif; ?>
          <li class="<?= $status['exam']['passed'] ? 'is-met' : '' ?>"><?= icon($status['exam']['passed'] ? 'check-circle' : 'clock') ?> <?= e(__('course.req_exam', ['score' => $status['exam']['required'], 'best' => $status['exam']['best']])) ?></li>
        </ul>
      </div>
    <?php else: ?>
      <div class="card reveal">
        <h2 class="h4"><?= e(__('course.completion_title')) ?></h2>
        <p class="muted"><?= e(__('course.completion_rules', ['score' => $course['min_exam_score']])) ?></p>
      </div>
    <?php endif; ?>
    <?php if ($labs): ?>
      <div class="card reveal"><h2 class="h4"><?= icon('flask') ?> <?= e(__('course.labs')) ?></h2>
        <ul class="link-list"><?php foreach ($labs as $lab): ?><li><a href="<?= e(route('labs.show', ['slug' => $lab['slug']])) ?>"><?= e($lab['t']['title']) ?></a> <small class="muted"><?= e(level_label($lab['difficulty'])) ?><?= (int) $lab['is_required'] ? ' · ' . e(__('common.required')) : '' ?></small></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>
    <?php if ($quizzes): ?>
      <div class="card reveal"><h2 class="h4"><?= icon('clipboard') ?> <?= e(__('course.assessments')) ?></h2>
        <ul class="link-list">
          <?php foreach ($otherQuizzes as $q): ?><li><a href="<?= e(route('assessments.show', ['slug' => $q['slug']])) ?>"><?= e($q['t']['title']) ?></a></li><?php endforeach; ?>
          <?php if ($finalExam): ?><li><a href="<?= e(route('assessments.show', ['slug' => $finalExam['slug']])) ?>"><strong><?= e($finalExam['t']['title']) ?></strong></a> <span class="chip chip--accent chip--xs"><?= e(__('exams.kind.final_exam')) ?></span></li><?php endif; ?>
        </ul>
      </div>
    <?php endif; ?>
  </aside>
</div></section>
