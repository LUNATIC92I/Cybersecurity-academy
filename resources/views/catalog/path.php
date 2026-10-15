<?php $t = $path['t']; ?>
<section class="page-hero accent-<?= e($path['accent']) ?>">
  <canvas class="network-canvas network-canvas--soft" data-network aria-hidden="true"></canvas>
  <div class="container">
    <p class="eyebrow"><?= icon($path['icon'], 'icon icon--sm') ?> <?= e(__('path.learning_path')) ?></p>
    <h1><?= e($t['title']) ?></h1>
    <p class="lead"><?= e($t['tagline']) ?></p>
    <ul class="meta-list meta-list--hero">
      <li><?= icon('layers', 'icon icon--sm') ?> <?= e(trans_choice('course.modules_count', $path['module_count'])) ?></li>
      <li><?= icon('book', 'icon icon--sm') ?> <?= e(trans_choice('course.lessons_count', $path['lesson_count'])) ?></li>
      <li><?= icon('flask', 'icon icon--sm') ?> <?= e(trans_choice('course.labs_count', $path['lab_count'])) ?></li>
    </ul>
  </div>
</section>
<section class="section"><div class="container split split--wide">
  <div class="prose reveal">
    <h2><?= e(__('path.about')) ?></h2>
    <p><?= e($t['description']) ?></p>
    <h2><?= e(__('path.outcomes')) ?></h2>
    <ul class="check-list"><?php foreach (\App\Services\Localizer::lines($t['career_outcomes']) as $o): ?><li><?= icon('check') ?> <?= e($o) ?></li><?php endforeach; ?></ul>
  </div>
  <aside class="stack">
    <?php foreach ($courses as $course): ?><?= partial('partials/course-card', ['course' => $course]) ?><?php endforeach; ?>
  </aside>
</div></section>
<?php foreach ($courses as $course): ?>
<section class="section section--alt"><div class="container">
  <header class="section__header reveal"><p class="eyebrow"><?= e(__('path.curriculum')) ?></p><h2><?= e($course['t']['title']) ?></h2></header>
  <?php $published = array_column($course['syllabus'], null, 'id'); ?>
  <ol class="roadmap__panel roadmap__panel--static accent-<?= e($path['accent']) ?>">
    <?php foreach ($course['all_modules'] as $n => $m): $lessons = $published[$m['id']]['lessons'] ?? []; ?>
      <li class="roadmap__step reveal">
        <span class="roadmap__num"><?= $n + 1 ?></span>
        <div>
          <strong><?= e($m['t']['title']) ?></strong>
          <small><?= e(level_label($m['level'])) ?> · <?= $lessons ? e(trans_choice('course.lessons_count', count($lessons))) : e(__('course.in_development')) ?></small>
        </div>
      </li>
    <?php endforeach; ?>
  </ol>
  <p class="center"><a class="btn btn--primary" href="<?= e(route('courses.show', ['slug' => $course['slug']])) ?>"><?= e(__('path.view_course')) ?></a></p>
</div></section>
<?php endforeach; ?>
<?php if ($labs): ?>
<section class="section"><div class="container">
  <header class="section__header reveal"><p class="eyebrow"><?= e(__('nav.labs')) ?></p><h2><?= e(__('path.labs_title')) ?></h2></header>
  <div class="grid grid--3"><?php foreach ($labs as $lab): ?><?= partial('partials/lab-card', ['lab' => $lab]) ?><?php endforeach; ?></div>
</div></section>
<?php endif; ?>
