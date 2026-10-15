<header class="admin-header"><h1 class="h2"><?= e(__('admin.nav.questions')) ?></h1><a class="btn btn--primary btn--sm" href="<?= e(route('admin.questions.create')) ?>"><?= icon('plus', 'icon icon--sm') ?> <?= e(__('admin.new_question')) ?></a></header>
<div class="dash-grid">
  <section class="card"><h2 class="h4"><?= e(__('admin.banks')) ?></h2>
    <ul class="link-list"><?php foreach ($banks as $b): ?><li><a href="?bank=<?= (int) $b['id'] ?>"><?= e($b['name']) ?></a> <small class="muted"><code><?= e($b['code']) ?></code> · <?= e(trans_choice('admin.active_questions', (int) $b['active_count'])) ?></small></li><?php endforeach; ?></ul></section>
  <form class="card form" method="post" action="<?= e(route('admin.banks.store')) ?>"><?= csrf_field() ?>
    <h2 class="h4"><?= e(__('admin.new_bank')) ?></h2>
    <div class="form-group"><label class="form-label" for="b_course"><?= e(__('admin.course')) ?></label><select class="input" id="b_course" name="course_id"><?php foreach ($courses as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['title']) ?></option><?php endforeach; ?></select></div>
    <div class="form-row"><div class="form-group"><label class="form-label" for="b_code"><?= e(__('admin.code')) ?></label><input class="input" id="b_code" name="code" required pattern="[a-z0-9-]+"></div>
      <div class="form-group"><label class="form-label" for="b_name"><?= e(__('field.title')) ?></label><input class="input" id="b_name" name="name" required></div></div>
    <button class="btn btn--primary btn--sm" type="submit"><?= e(__('common.create')) ?></button></form>
</div>
<form class="filter-bar card" method="get">
  <div class="form-group"><label class="form-label" for="bank"><?= e(__('admin.bank')) ?></label><select class="input" id="bank" name="bank"><option value=""><?= e(__('common.all')) ?></option><?php foreach ($banks as $b): ?><option value="<?= (int) $b['id'] ?>" <?= $bankId === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
  <div class="form-group"><label class="form-label" for="type"><?= e(__('admin.question_type')) ?></label><select class="input" id="type" name="type"><option value=""><?= e(__('common.all')) ?></option><?php foreach ($types as $ty): ?><option value="<?= $ty ?>" <?= $type === $ty ? 'selected' : '' ?>><?= e(__('exams.qtype.' . $ty)) ?></option><?php endforeach; ?></select></div>
  <button class="btn btn--primary" type="submit"><?= e(__('catalog.filter')) ?></button>
</form>
<div class="table-wrap card"><table class="table">
  <thead><tr><th scope="col">#</th><th scope="col"><?= e(__('admin.stem')) ?></th><th scope="col"><?= e(__('admin.bank')) ?></th><th scope="col"><?= e(__('admin.question_type')) ?></th><th scope="col"><?= e(__('admin.difficulty')) ?></th><th scope="col">FR</th></tr></thead>
  <tbody><?php foreach ($result['items'] as $q): ?>
    <tr class="<?= $q['status'] === 'retired' ? 'is-muted' : '' ?>"><td><?= (int) $q['id'] ?></td><td><a href="<?= e(route('admin.questions.edit', ['id' => $q['id']])) ?>"><?= e(mb_substr((string) $q['stem'], 0, 110)) ?></a></td><td><?= e($q['bank_name']) ?></td><td><?= e(__('exams.qtype.' . $q['question_type'])) ?></td><td><?= e(__('admin.diff.' . $q['difficulty'])) ?></td><td><span class="chip chip--xs status-<?= e($q['fr_status']) ?>"><?= e(__('status.' . $q['fr_status'])) ?></span></td></tr>
  <?php endforeach; ?></tbody>
</table></div>
<?= partial('partials/pagination', ['result' => $result]) ?>
