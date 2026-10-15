<?php $t = $course['t']; $accent = $course['path']['accent'] ?? 'blue'; ?>
<article class="card course-card card--interactive reveal accent-<?= e($accent) ?>">
  <div class="course-card__top">
    <span class="chip chip--<?= e($accent) ?>"><?= icon($course['path']['icon'] ?? 'shield', 'icon icon--sm') ?> <?= e($course['path']['t']['title'] ?? '') ?></span>
    <span class="chip <?= $course['access_level'] === 'free' ? 'chip--success' : 'chip--muted' ?>"><?= e(__('course.access.' . $course['access_level'])) ?></span>
  </div>
  <h3 class="course-card__title"><a href="<?= e(route('courses.show', ['slug' => $course['slug']])) ?>" class="stretched-link"><?= e($t['title'] ?? '') ?></a></h3>
  <p class="course-card__subtitle"><?= e($t['subtitle'] ?? '') ?></p>
  <?php if (!empty($t['_fallback'])): ?><p class="translation-note"><?= icon('globe', 'icon icon--sm') ?> <?= e(__('content.fallback_short')) ?></p><?php endif; ?>
  <ul class="meta-list">
    <li><?= icon('layers', 'icon icon--sm') ?> <?= e(trans_choice('course.modules_count', $course['module_count'])) ?></li>
    <li><?= icon('book', 'icon icon--sm') ?> <?= e(trans_choice('course.lessons_count', $course['lesson_count'])) ?></li>
    <li><?= icon('flask', 'icon icon--sm') ?> <?= e(trans_choice('course.labs_count', $course['lab_count'])) ?></li>
    <li><?= icon('chart', 'icon icon--sm') ?> <?= e(level_label($course['level'])) ?></li>
  </ul>
  <?php if (isset($course['percent'])): ?>
    <?= partial('partials/progress', ['percent' => (int) $course['percent'], 'label' => __('course.progress')]) ?>
  <?php endif; ?>
</article>
