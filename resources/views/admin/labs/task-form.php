<?php $tr = $task['translations'] ?? []; $p = 'task' . ($task['id'] ?? 'new') . '_'; ?>
<form class="form" method="post" action="<?= e($action) ?>"><?= csrf_field() ?>
  <div class="form-row form-row--3">
    <div class="form-group"><label class="form-label" for="<?= $p ?>type"><?= e(__('admin.task_type')) ?></label><select class="input" id="<?= $p ?>type" name="task_type"><?php foreach (['text', 'number', 'choice', 'multi', 'query'] as $x): ?><option value="<?= $x ?>" <?= ($task['task_type'] ?? 'text') === $x ? 'selected' : '' ?>><?= e(__('admin.task.' . $x)) ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label class="form-label" for="<?= $p ?>points"><?= e(__('admin.points')) ?></label><input class="input" type="number" id="<?= $p ?>points" name="points" value="<?= (int) ($task['points'] ?? 10) ?>" min="1" max="100"></div>
    <div class="form-group"><label class="form-label" for="<?= $p ?>order"><?= e(__('admin.sort_order')) ?></label><input class="input" type="number" id="<?= $p ?>order" name="sort_order" value="<?= (int) ($task['sort_order'] ?? 1) ?>"></div>
  </div>
  <div class="form-group"><label class="form-label" for="<?= $p ?>spec"><?= e(__('admin.answer_spec')) ?></label><textarea class="input input--code" id="<?= $p ?>spec" name="answer_spec" rows="3"><?= e($task['answer_spec'] ?? '{"accepted": [""]}') ?></textarea></div>
  <div class="form-row">
    <?php foreach (['en', 'fr'] as $loc): $row = $tr[$loc] ?? []; ?>
      <fieldset class="fieldset"><legend><?= strtoupper($loc) ?></legend>
        <div class="form-group"><label class="form-label" for="<?= $p . $loc ?>prompt"><?= e(__('admin.prompt')) ?></label><textarea class="input" id="<?= $p . $loc ?>prompt" name="t[<?= $loc ?>][prompt]" rows="2"><?= e($row['prompt'] ?? '') ?></textarea></div>
        <div class="form-group"><label class="form-label" for="<?= $p . $loc ?>hint"><?= e(__('content.block.hint')) ?></label><input class="input" id="<?= $p . $loc ?>hint" name="t[<?= $loc ?>][hint]" value="<?= e($row['hint'] ?? '') ?>"></div>
        <div class="form-group"><label class="form-label" for="<?= $p . $loc ?>exp"><?= e(__('exams.explanation')) ?></label><textarea class="input" id="<?= $p . $loc ?>exp" name="t[<?= $loc ?>][explanation]" rows="2"><?= e($row['explanation'] ?? '') ?></textarea></div>
        <div class="form-group"><label class="form-label" for="<?= $p . $loc ?>opt"><?= e(__('admin.options_json')) ?></label><input class="input input--code" id="<?= $p . $loc ?>opt" name="t[<?= $loc ?>][options]" value="<?= e($row['options'] ?? '') ?>" placeholder='{"a": "...", "b": "..."}'></div>
      </fieldset>
    <?php endforeach; ?>
  </div>
  <button class="btn btn--primary btn--sm" type="submit"><?= e(__('common.save')) ?></button>
</form>
