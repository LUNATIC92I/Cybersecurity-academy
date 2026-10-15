<?php
$done = $progress && $progress['status'] === 'completed';
$lessonUrl = fn ($l) => route('lessons.show', ['course' => $course['slug'], 'module' => $l['module_slug'], 'lesson' => $l['slug']]);
?>
<div class="container lesson-layout">
  <aside class="lesson-sidebar" aria-label="<?= e(__('lesson.course_navigation')) ?>">
    <a class="link-muted" href="<?= e(route('courses.show', ['slug' => $course['slug']])) ?>">← <?= e($course['t']['title']) ?></a>
    <nav class="lesson-nav">
      <?php foreach ($syllabus as $m): ?>
        <p class="lesson-nav__module"><?= e($m['t']['title']) ?></p>
        <ul>
          <?php foreach ($m['lessons'] as $l): $isDone = in_array((int) $l['id'], $completed, true); $current = (int) $l['id'] === (int) $lesson['id']; ?>
            <li><a href="<?= e(route('lessons.show', ['course' => $course['slug'], 'module' => $m['slug'], 'lesson' => $l['slug']])) ?>" class="<?= $current ? 'is-current' : '' ?> <?= $isDone ? 'is-done' : '' ?>" <?= $current ? 'aria-current="page"' : '' ?>><?= icon($isDone ? 'check-circle' : 'book', 'icon icon--sm') ?> <?= e($l['t']['title'] ?? $l['slug']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      <?php endforeach; ?>
    </nav>
  </aside>

  <article class="lesson" data-lesson data-heartbeat-url="<?= e(route('lessons.heartbeat', ['id' => $lesson['id']])) ?>">
    <header class="lesson__header">
      <p class="eyebrow"><?= e($module['t']['title']) ?><?php if (!empty($t['chapter_title'])): ?> · <?= e($t['chapter_title']) ?><?php endif; ?></p>
      <h1><?= e($t['title']) ?></h1>
      <p class="lead"><?= e($t['summary']) ?></p>
      <ul class="meta-list">
        <li><?= icon('clock', 'icon icon--sm') ?> <?= e(minutes_label((int) $lesson['duration_minutes'])) ?></li>
        <li><?= icon('chart', 'icon icon--sm') ?> <?= e(level_label($lesson['level'])) ?></li>
        <li><?= icon('globe', 'icon icon--sm') ?> <?= e(strtoupper($t['_locale'])) ?></li>
        <?php if ($done): ?><li class="text-success"><?= icon('check-circle', 'icon icon--sm') ?> <?= e(__('lesson.completed_badge')) ?></li><?php endif; ?>
      </ul>
      <?php if ($t['_fallback']): ?><div class="notice notice--info"><?= icon('globe') ?> <span><?= e(__('content.fallback')) ?></span></div><?php endif; ?>
      <?php if ($lesson['status'] !== 'published' || ($t['translation_status'] ?? 'published') !== 'published'): ?><div class="notice notice--warning"><?= icon('eye') ?> <span><?= e(__('content.preview_notice', ['status' => __('status.' . ($lesson['status'] !== 'published' ? $lesson['status'] : $t['translation_status']))])) ?></span></div><?php endif; ?>
      <?php if (!$enrolled): ?><div class="notice notice--info"><?= icon('info') ?> <span><?= e(__('lesson.preview_mode')) ?></span></div><?php endif; ?>
    </header>

    <div class="lesson__intro-grid">
      <?php if ($objectives): ?><section class="card"><h2 class="h4"><?= icon('target') ?> <?= e(__('lesson.objectives')) ?></h2><ul class="check-list check-list--compact"><?php foreach ($objectives as $o): ?><li><?= icon('check') ?> <?= e($o) ?></li><?php endforeach; ?></ul></section><?php endif; ?>
      <?php if ($prerequisites): ?><section class="card"><h2 class="h4"><?= icon('layers') ?> <?= e(__('lesson.prerequisites')) ?></h2><ul class="plain-list"><?php foreach ($prerequisites as $p): ?><li><?= e($p) ?></li><?php endforeach; ?></ul></section><?php endif; ?>
    </div>

    <?php if (count($toc) > 2): ?>
      <nav class="toc card" aria-label="<?= e(__('lesson.toc')) ?>"><p class="h4"><?= e(__('lesson.toc')) ?></p><ol><?php foreach ($toc as $item): ?><li><a href="#<?= e($item['id']) ?>"><?= e($item['text']) ?></a></li><?php endforeach; ?></ol></nav>
    <?php endif; ?>

    <div class="prose lesson__content"><?= $html ?></div>

    <?php if ($assignment): ?><section class="card lesson__section"><h2 class="h3"><?= icon('edit') ?> <?= e(__('lesson.assignment')) ?></h2><div class="prose"><?= $assignment ?></div></section><?php endif; ?>
    <?php if ($resources): ?>
      <section class="card lesson__section"><h2 class="h3"><?= icon('download') ?> <?= e(__('lesson.resources')) ?></h2>
        <ul class="link-list"><?php foreach ($resources as $r): ?><li><a href="<?= e(route('resources.download', ['id' => $r['id']])) ?>"><?= icon('file', 'icon icon--sm') ?> <?= e($r['title']) ?></a> <small class="muted"><?= e($r['original_name']) ?> · <?= e(number_format($r['size_bytes'] / 1024, 1)) ?> KB</small></li><?php endforeach; ?></ul>
      </section>
    <?php endif; ?>
    <?php if ($criteria): ?><section class="card lesson__section"><h2 class="h3"><?= icon('check-circle') ?> <?= e(__('lesson.criteria')) ?></h2><div class="prose"><?= $criteria ?></div></section><?php endif; ?>
    <?php if ($references): ?><section class="card lesson__section"><h2 class="h3"><?= icon('link') ?> <?= e(__('lesson.references')) ?></h2><div class="prose prose--links"><?= $references ?></div></section><?php endif; ?>

    <footer class="lesson__footer card">
      <?php if ($enrolled && !$done): ?>
        <form method="post" action="<?= e(route('lessons.complete', ['id' => $lesson['id']])) ?>" data-complete-form data-min-seconds="<?= (int) $minSeconds ?>">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn--primary"><?= icon('check', 'icon icon--sm') ?> <?= e(__('lesson.mark_complete')) ?></button>
          <p class="form-hint" data-complete-hint><?= e(__('lesson.complete_hint', ['seconds' => $minSeconds])) ?></p>
        </form>
      <?php elseif ($done): ?>
        <p class="text-success"><?= icon('check-circle') ?> <?= e(__('lesson.completed_on', ['date' => format_date($progress['completed_at'])])) ?></p>
      <?php else: ?>
        <form method="post" action="<?= e(route('courses.enroll', ['slug' => $course['slug']])) ?>"><?= csrf_field() ?><button class="btn btn--primary" type="submit"><?= e(__('course.enroll')) ?></button></form>
      <?php endif; ?>
      <?php if ($moduleQuiz): ?><a class="btn btn--outline" href="<?= e(route('assessments.show', ['slug' => $moduleQuiz['slug']])) ?>"><?= icon('clipboard', 'icon icon--sm') ?> <?= e(__('lesson.module_quiz')) ?></a><?php endif; ?>
      <div class="lesson__pager">
        <?php if ($prev): ?><a class="btn btn--ghost" href="<?= e($lessonUrl($prev)) ?>" rel="prev">← <?= e(__('lesson.previous')) ?></a><?php endif; ?>
        <?php if ($next): ?><a class="btn btn--ghost" href="<?= e($lessonUrl($next)) ?>" rel="next"><?= e(__('lesson.next')) ?> →</a><?php endif; ?>
      </div>
    </footer>
  </article>
</div>
