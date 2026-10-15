<div class="form-group"><label class="form-label" for="<?= e($id ?? 'status') ?>"><?= e(__('admin.status')) ?></label>
  <select class="input" id="<?= e($id ?? 'status') ?>" name="status"><?php foreach ($options ?? ['draft', 'in_review', 'published', 'archived'] as $s): ?><option value="<?= $s ?>" <?= ($value ?? 'draft') === $s ? 'selected' : '' ?>><?= e(__('status.' . $s)) ?></option><?php endforeach; ?></select>
  <?php if (!auth()->can('content.publish')): ?><p class="form-hint"><?= e(__('admin.publish_hint')) ?></p><?php endif; ?></div>
