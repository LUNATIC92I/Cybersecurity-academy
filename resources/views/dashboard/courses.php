<header class="dash-header"><h1 class="h2"><?= e(__('dashboard.my_courses')) ?></h1></header>
<?php if ($enrolled): ?>
  <div class="grid grid--2">
    <?php foreach ($enrolled as $course): ?>
      <div class="stack">
        <?= partial('partials/course-card', ['course' => $course]) ?>
        <p class="form-hint"><?= e(__('dashboard.course_breakdown', ['lessons' => (int) $course['lessons_completed'] . '/' . (int) $course['lessons_total'], 'labs' => (int) $course['labs_passed'] . '/' . (int) $course['labs_total'], 'exam' => (int) $course['exam_passed'] ? __('common.yes') : __('common.no')])) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <?= partial('partials/empty', ['icon' => 'book', 'title' => __('dashboard.no_courses_title'), 'text' => __('dashboard.no_courses_text')]) ?>
<?php endif; ?>
<?php if ($available): ?>
  <h2 class="subheading"><?= e(__('dashboard.available_courses')) ?></h2>
  <div class="grid grid--2">
    <?php foreach ($available as $course): ?>
      <div class="stack">
        <?= partial('partials/course-card', ['course' => $course]) ?>
        <?php if ($course['access_source']): ?>
          <form method="post" action="<?= e(route('courses.enroll', ['slug' => $course['slug']])) ?>"><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit"><?= e(__('course.enroll')) ?></button></form>
        <?php else: ?><a class="btn btn--outline btn--sm" href="<?= e(route('pricing')) ?>#course-<?= (int) $course['id'] ?>"><?= e(__('course.get_access')) ?></a><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
