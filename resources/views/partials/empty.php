<div class="empty-state">
  <span class="empty-state__icon" aria-hidden="true"><?= icon($icon ?? 'compass') ?></span>
  <h3><?= e($title) ?></h3>
  <?php if (!empty($text)): ?><p><?= e($text) ?></p><?php endif; ?>
  <?php if (!empty($action)): ?><a class="btn btn--primary" href="<?= e($action[1]) ?>"><?= e($action[0]) ?></a><?php endif; ?>
</div>
