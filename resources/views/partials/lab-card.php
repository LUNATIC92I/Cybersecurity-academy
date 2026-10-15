<?php $t = $lab['t']; $passedLab = in_array((int) $lab['id'], $passed ?? [], true); ?>
<article class="card lab-card card--interactive reveal accent-<?= e($lab['path_accent'] ?? 'blue') ?>">
  <div class="course-card__top">
    <span class="chip chip--<?= e($lab['path_accent'] ?? 'blue') ?>"><?= e($lab['path_code'] ?? '') ?></span>
    <span class="chip chip--muted"><?= icon($lab['lab_type'] === 'virtual' ? 'server' : ($lab['lab_type'] === 'download' ? 'download' : 'terminal'), 'icon icon--sm') ?> <?= e(__('labs.type.' . $lab['lab_type'])) ?></span>
    <?php if ($passedLab): ?><span class="chip chip--success"><?= icon('check', 'icon icon--sm') ?> <?= e(__('labs.passed_short')) ?></span><?php endif; ?>
  </div>
  <h3 class="course-card__title"><a class="stretched-link" href="<?= e(route('labs.show', ['slug' => $lab['slug']])) ?>"><?= e($t['title'] ?? '') ?></a></h3>
  <p class="course-card__subtitle"><?= e($t['summary'] ?? '') ?></p>
  <ul class="meta-list">
    <li><?= icon('chart', 'icon icon--sm') ?> <?= e(level_label($lab['difficulty'])) ?></li>
    <li><?= icon('clock', 'icon icon--sm') ?> <?= e(minutes_label((int) $lab['duration_minutes'])) ?></li>
    <li><?= icon('check-circle', 'icon icon--sm') ?> <?= e(trans_choice('labs.tasks_count', (int) $lab['task_count'])) ?></li>
    <?php if ($lab['risk_level'] === 'elevated'): ?><li class="text-warning"><?= icon('alert', 'icon icon--sm') ?> <?= e(__('labs.elevated')) ?></li><?php endif; ?>
  </ul>
</article>
