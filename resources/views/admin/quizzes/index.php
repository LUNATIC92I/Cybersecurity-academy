<header class="admin-header"><h1 class="h2"><?= e(__('admin.nav.quizzes')) ?></h1></header>
<div class="table-wrap card"><table class="table">
  <thead><tr><th scope="col"><?= e(__('field.title')) ?></th><th scope="col"><?= e(__('admin.course')) ?></th><th scope="col"><?= e(__('exams.col.type')) ?></th><th scope="col"><?= e(__('admin.status')) ?></th><th scope="col"><?= e(__('admin.attempts')) ?></th><th scope="col"><?= e(__('dashboard.avg_score')) ?></th></tr></thead>
  <tbody><?php foreach ($quizzes as $q): ?>
    <tr><td><a href="<?= e(route('admin.quizzes.edit', ['id' => $q['id']])) ?>"><?= e($q['title'] ?? $q['slug']) ?></a></td><td><?= e($q['course_title']) ?></td><td><?= e(__('exams.kind.' . $q['kind'])) ?></td><td><span class="chip chip--xs status-<?= e($q['status']) ?>"><?= e(__('status.' . $q['status'])) ?></span></td><td><?= (int) $q['attempts'] ?></td><td><?= $q['avg_score'] !== null ? (int) $q['avg_score'] . '%' : '—' ?></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
<form class="card form form--inline-grid" method="post" action="<?= e(route('admin.quizzes.store')) ?>"><?= csrf_field() ?>
  <div class="form-group"><label class="form-label" for="qc"><?= e(__('admin.course')) ?></label><select class="input" id="qc" name="course_id"><?php foreach ($courses as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['title']) ?></option><?php endforeach; ?></select></div>
  <div class="form-group"><label class="form-label" for="qs"><?= e(__('field.slug')) ?></label><input class="input" id="qs" name="slug" required pattern="[a-z0-9-]+"></div>
  <div class="form-group"><label class="form-label" for="qk"><?= e(__('exams.col.type')) ?></label><select class="input" id="qk" name="kind"><?php foreach (['knowledge_check', 'module_quiz', 'final_exam'] as $k): ?><option value="<?= $k ?>"><?= e(__('exams.kind.' . $k)) ?></option><?php endforeach; ?></select></div>
  <div class="form-group"><label class="form-label" for="qt"><?= e(__('field.title')) ?> (EN)</label><input class="input" id="qt" name="title_en" required></div>
  <button class="btn btn--primary btn--sm" type="submit"><?= icon('plus', 'icon icon--sm') ?> <?= e(__('common.create')) ?></button>
</form>
