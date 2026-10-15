<?php $percent = max(0, min(100, (int) $percent)); ?>
<div class="progress" role="progressbar" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= e($label ?? __('course.progress')) ?>">
  <div class="progress__bar" data-progress="<?= $percent ?>"></div>
</div>
<p class="progress__label"><span><?= e($label ?? __('course.progress')) ?></span><strong><?= $percent ?>%</strong></p>
