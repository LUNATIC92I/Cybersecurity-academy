<?php /* $url, $subjectType, $subjectId, $imageUrl (optional), $canRecord */ ?>
<div class="share-panel" data-share data-subject-type="<?= e($subjectType) ?>" data-subject-id="<?= (int) $subjectId ?>" data-record-url="<?= e(route('share.record')) ?>" data-can-record="<?= !empty($canRecord) ? '1' : '0' ?>">
  <a class="btn btn--social btn--linkedin" data-network="linkedin" href="<?= e(\App\Services\ShareService::linkedInUrl($url)) ?>" target="_blank" rel="noopener noreferrer"><?= icon('linkedin') ?> LinkedIn</a>
  <a class="btn btn--social btn--facebook" data-network="facebook" href="<?= e(\App\Services\ShareService::facebookUrl($url)) ?>" target="_blank" rel="noopener noreferrer"><?= icon('facebook') ?> Facebook</a>
  <?php if (!empty($imageUrl)): ?>
    <a class="btn btn--social btn--tiktok" data-network="tiktok" href="<?= e($imageUrl . '?download=1') ?>" download data-copy-after="<?= e($url) ?>"><?= icon('tiktok') ?> TikTok</a>
  <?php endif; ?>
  <button type="button" class="btn btn--ghost" data-network="copy" data-copy="<?= e($url) ?>"><?= icon('link') ?> <?= e(__('share.copy_link')) ?></button>
  <?php if (!empty($imageUrl)): ?>
    <a class="btn btn--ghost" data-network="download" href="<?= e($imageUrl . '?download=1') ?>" download><?= icon('download') ?> <?= e(__('share.download_image')) ?></a>
  <?php endif; ?>
</div>
<?php if (!empty($imageUrl)): ?><p class="form-hint"><?= e(__('share.tiktok_hint')) ?></p><?php endif; ?>
