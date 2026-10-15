<section class="page-hero"><div class="container">
  <p class="eyebrow"><?= e(__('nav.assessments')) ?></p>
  <h1><?= e(__('exams.title')) ?></h1>
  <p class="lead"><?= e(__('exams.lead')) ?></p>
</div></section>
<section class="section section--tight"><div class="container">
  <div class="grid grid--4">
    <?php foreach (['random', 'timed', 'server', 'review'] as $f): ?>
      <div class="card reveal"><h2 class="h4"><?= e(__('exams.feature.' . $f . '.title')) ?></h2><p class="muted"><?= e(__('exams.feature.' . $f . '.text')) ?></p></div>
    <?php endforeach; ?>
  </div>
  <?php if ($typeCounts): ?>
    <p class="muted"><?= e(__('exams.question_types')) ?>
      <?php foreach ($typeCounts as $type => $n): ?><span class="chip chip--muted chip--xs"><?= e(__('exams.qtype.' . $type)) ?> · <?= (int) $n ?></span> <?php endforeach; ?></p>
  <?php endif; ?>
  <?php $byCourse = []; foreach ($quizzes as $q) { $byCourse[$q['course_id']][] = $q; } ?>
  <?php if (!$byCourse): ?>
    <?= partial('partials/empty', ['icon' => 'clipboard', 'title' => __('exams.empty_title'), 'text' => __('exams.empty_text')]) ?>
  <?php endif; ?>
  <?php foreach ($byCourse as $courseId => $list): $course = $courses[$courseId] ?? null; if (!$course) { continue; } ?>
    <h2 class="subheading"><?= e($course['t']['title']) ?></h2>
    <div class="table-wrap card">
      <table class="table">
        <thead><tr><th scope="col"><?= e(__('exams.col.assessment')) ?></th><th scope="col"><?= e(__('exams.col.type')) ?></th><th scope="col"><?= e(__('exams.col.questions')) ?></th><th scope="col"><?= e(__('exams.col.duration')) ?></th><th scope="col"><?= e(__('exams.col.pass')) ?></th><th scope="col"><?= e(__('exams.col.best')) ?></th></tr></thead>
        <tbody>
          <?php foreach ($list as $q): $b = $best[(int) $q['id']] ?? null; ?>
            <tr>
              <td><a href="<?= e(route('assessments.show', ['slug' => $q['slug']])) ?>"><?= e($q['t']['title']) ?></a></td>
              <td><span class="chip <?= $q['kind'] === 'final_exam' ? 'chip--accent' : 'chip--muted' ?> chip--xs"><?= e(__('exams.kind.' . $q['kind'])) ?></span></td>
              <td><?= (int) $q['question_count'] ?></td>
              <td><?= e(minutes_label((int) $q['duration_minutes'])) ?></td>
              <td><?= (int) $q['pass_percent'] ?>%</td>
              <td><?= $b ? '<span class="' . ((int) $b['passed'] ? 'text-success' : 'text-warning') . '">' . (int) $b['best'] . '%</span>' : '—' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endforeach; ?>
</div></section>
