<?php /* EN/FR tabbed editor. $fields: name => [label, text|textarea|markdown]; $translations; $prefix; $withStatus */ ?>
<div class="tabs" data-tabs>
  <div class="tabs__list" role="tablist">
    <?php foreach (['en' => 'English', 'fr' => 'Français'] as $loc => $label): $st = $translations[$loc]['translation_status'] ?? 'missing'; ?>
      <button type="button" role="tab" class="tabs__tab" id="tab-<?= e($prefix . $loc) ?>" aria-controls="panel-<?= e($prefix . $loc) ?>" aria-selected="<?= $loc === 'en' ? 'true' : 'false' ?>" tabindex="<?= $loc === 'en' ? '0' : '-1' ?>"><?= e($label) ?> <span class="chip chip--xs status-<?= e($st) ?>"><?= e(__('status.' . $st)) ?></span></button>
    <?php endforeach; ?>
  </div>
  <?php foreach (['en', 'fr'] as $loc): $row = $translations[$loc] ?? []; $oldT = old('t', []); ?>
    <div class="tabs__panel" role="tabpanel" id="panel-<?= e($prefix . $loc) ?>" aria-labelledby="tab-<?= e($prefix . $loc) ?>" <?= $loc === 'en' ? '' : 'hidden' ?>>
      <?php foreach ($fields as $name => [$label, $type]): $fid = $prefix . $loc . '_' . $name; $value = $oldT[$loc][$name] ?? ($row[$name] ?? ''); ?>
        <div class="form-group"><label class="form-label" for="<?= e($fid) ?>"><?= e($label) ?></label>
          <?php if ($type === 'text'): ?><input class="input" id="<?= e($fid) ?>" name="t[<?= $loc ?>][<?= e($name) ?>]" value="<?= e($value) ?>">
          <?php else: ?><textarea class="input <?= $type === 'markdown' ? 'input--code' : '' ?>" id="<?= e($fid) ?>" name="t[<?= $loc ?>][<?= e($name) ?>]" rows="<?= $type === 'markdown' ? 18 : 4 ?>"><?= e($value) ?></textarea><?php endif; ?>
        </div>
      <?php endforeach; ?>
      <?php if (!empty($withStatus)): $st = $row['translation_status'] ?? ($loc === 'en' ? 'draft' : 'missing'); ?>
        <div class="form-group"><label class="form-label" for="<?= e($prefix . $loc) ?>_status"><?= e(__('admin.translation_status')) ?></label>
          <select class="input" id="<?= e($prefix . $loc) ?>_status" name="t[<?= $loc ?>][translation_status]"><?php foreach (['missing', 'draft', 'in_review', 'published'] as $s): ?><option value="<?= $s ?>" <?= $st === $s ? 'selected' : '' ?>><?= e(__('status.' . $s)) ?></option><?php endforeach; ?></select>
          <p class="form-hint"><?= e(__('admin.translation_status_hint')) ?></p></div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
