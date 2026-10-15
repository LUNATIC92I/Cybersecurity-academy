<?php
$type = $type ?? 'text';
$id = $id ?? str_replace(['[', ']', '.'], '_', $name);
$error = error_for($errorKey ?? $name);
$describedBy = trim(($error ? $id . '-error ' : '') . (!empty($hint) ? $id . '-hint' : ''));
?>
<div class="form-group <?= $error ? 'has-error' : '' ?>">
  <label class="form-label" for="<?= e($id) ?>"><?= e($label) ?><?php if (!empty($required)): ?> <span class="req" aria-hidden="true">*</span><?php endif; ?></label>
  <?php if ($type === 'textarea'): ?>
    <textarea class="input" id="<?= e($id) ?>" name="<?= e($name) ?>" rows="<?= (int) ($rows ?? 5) ?>" <?= !empty($required) ? 'required' : '' ?> <?= $describedBy ? 'aria-describedby="' . e($describedBy) . '"' : '' ?> <?= $error ? 'aria-invalid="true"' : '' ?> <?= !empty($mono) ? 'spellcheck="false"' : '' ?>><?= e($value ?? '') ?></textarea>
  <?php else: ?>
    <input class="input" type="<?= e($type) ?>" id="<?= e($id) ?>" name="<?= e($name) ?>" value="<?= e($value ?? '') ?>" <?= !empty($required) ? 'required' : '' ?> <?= !empty($autocomplete) ? 'autocomplete="' . e($autocomplete) . '"' : '' ?> <?= isset($min) ? 'min="' . e($min) . '"' : '' ?> <?= isset($max) ? 'max="' . e($max) . '"' : '' ?> <?= isset($minlength) ? 'minlength="' . (int) $minlength . '"' : '' ?> <?= $describedBy ? 'aria-describedby="' . e($describedBy) . '"' : '' ?> <?= $error ? 'aria-invalid="true"' : '' ?> <?= !empty($placeholder) ? 'placeholder="' . e($placeholder) . '"' : '' ?>>
  <?php endif; ?>
  <?php if (!empty($hint)): ?><p class="form-hint" id="<?= e($id) ?>-hint"><?= e($hint) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="form-error" id="<?= e($id) ?>-error"><?= e($error) ?></p><?php endif; ?>
</div>
