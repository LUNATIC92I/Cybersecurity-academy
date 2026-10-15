<section class="page-hero page-hero--compact"><div class="container">
  <nav class="breadcrumb"><a href="<?= e(route('assessments.index')) ?>"><?= e(__('nav.assessments')) ?></a> / <a href="<?= e(route('courses.show', ['slug' => $course['slug']])) ?>"><?= e($course['t']['title']) ?></a></nav>
  <h1><?= e($quiz['t']['title']) ?></h1>
  <?php if (!empty($quiz['t']['description'])): ?><p class="lead"><?= e($quiz['t']['description']) ?></p><?php endif; ?>
</div></section>
<section class="section section--tight"><div class="container split">
  <div class="card">
    <h2 class="h3"><?= e(__('exams.rules')) ?></h2>
    <dl class="kv">
      <div><dt><?= e(__('exams.col.type')) ?></dt><dd><?= e(__('exams.kind.' . $quiz['kind'])) ?></dd></div>
      <div><dt><?= e(__('exams.col.questions')) ?></dt><dd><?= (int) $quiz['question_count'] ?></dd></div>
      <div><dt><?= e(__('exams.col.duration')) ?></dt><dd><?= e(minutes_label((int) $quiz['duration_minutes'])) ?></dd></div>
      <div><dt><?= e(__('exams.col.pass')) ?></dt><dd><?= (int) ($quiz['kind'] === 'final_exam' ? max($quiz['pass_percent'], $course['min_exam_score']) : $quiz['pass_percent']) ?>%</dd></div>
      <div><dt><?= e(__('exams.attempts_allowed')) ?></dt><dd><?= $quiz['max_attempts'] ? (int) $quiz['max_attempts'] : e(__('exams.unlimited')) ?></dd></div>
      <?php if ((int) $quiz['retake_cooldown_minutes'] > 0): ?><div><dt><?= e(__('exams.cooldown')) ?></dt><dd><?= e(minutes_label((int) $quiz['retake_cooldown_minutes'])) ?></dd></div><?php endif; ?>
      <div><dt><?= e(__('exams.feedback')) ?></dt><dd><?= e(__('exams.explanations.' . $quiz['show_explanations'])) ?></dd></div>
    </dl>
    <ul class="check-list check-list--compact">
      <li><?= icon('info') ?> <?= e(__('exams.rule_random')) ?></li>
      <li><?= icon('clock') ?> <?= e(__('exams.rule_timer')) ?></li>
      <li><?= icon('shield') ?> <?= e(__('exams.rule_server')) ?></li>
    </ul>
    <?php if ($active): ?>
      <a class="btn btn--primary" href="<?= e(route('attempts.take', ['id' => $active['id']])) ?>"><?= e(__('exams.resume')) ?></a>
    <?php elseif ($eligibility['allowed']): ?>
      <form method="post" action="<?= e(route('assessments.start', ['slug' => $quiz['slug']])) ?>"><?= csrf_field() ?><button class="btn btn--primary" type="submit" data-once><?= e(__('exams.start')) ?></button></form>
    <?php else: ?>
      <div class="notice notice--warning"><?= icon('lock') ?> <span><?= e(__('exams.cannot_start.' . $eligibility['reason'], ['minutes' => $eligibility['wait_minutes'] ?? 0])) ?></span></div>
      <?php if ($eligibility['reason'] === 'not_enrolled' || $eligibility['reason'] === 'no_access'): ?><a class="btn btn--outline" href="<?= e(route('courses.show', ['slug' => $course['slug']])) ?>"><?= e(__('certs.view_course')) ?></a><?php endif; ?>
    <?php endif; ?>
  </div>
  <div class="card">
    <h2 class="h3"><?= e(__('exams.history')) ?></h2>
    <?php if ($attempts): ?>
      <div class="table-wrap"><table class="table">
        <thead><tr><th scope="col">#</th><th scope="col"><?= e(__('exams.col.status')) ?></th><th scope="col"><?= e(__('exams.col.score')) ?></th><th scope="col"><?= e(__('exams.col.date')) ?></th><th scope="col"><span class="sr-only"><?= e(__('common.actions')) ?></span></th></tr></thead>
        <tbody>
          <?php foreach ($attempts as $a): ?>
            <tr>
              <td><?= (int) $a['attempt_no'] ?></td>
              <td><?= e(__('exams.status.' . $a['status'])) ?></td>
              <td><?= $a['status'] === 'in_progress' ? '—' : '<span class="' . ((int) $a['passed'] ? 'text-success' : 'text-warning') . '">' . (int) $a['percent'] . '%</span>' ?></td>
              <td><?= e(format_datetime($a['submitted_at'] ?? $a['started_at'])) ?></td>
              <td><?php if ($a['status'] !== 'in_progress'): ?><a href="<?= e(route('attempts.results', ['id' => $a['id']])) ?>"><?= e(__('exams.review')) ?></a><?php endif; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
    <?php else: ?><p class="muted"><?= e(__('exams.no_attempts')) ?></p><?php endif; ?>
  </div>
</div></section>
