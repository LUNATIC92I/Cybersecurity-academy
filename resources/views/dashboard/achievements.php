<header class="dash-header"><h1 class="h2"><?= e(__('dashboard.achievements')) ?></h1><p class="muted"><?= e(__('dashboard.achievements_lead')) ?></p></header>
<div class="grid grid--3">
  <?php foreach ($achievements as $a): $earned = $a['earned_at'] !== null; ?>
    <article class="card achievement <?= $earned ? 'achievement--earned' : 'achievement--locked' ?>">
      <span class="achievement__icon"><?= icon($earned ? $a['icon'] : 'lock', 'icon icon--lg') ?></span>
      <h2 class="h4"><?= e($a['t']['title'] ?? $a['code']) ?></h2>
      <p class="muted"><?= e($a['t']['description'] ?? '') ?></p>
      <?php if ($earned): ?>
        <p class="form-hint"><?= e(__('dashboard.earned_on', ['date' => format_date($a['earned_at'])])) ?></p>
        <form method="post" action="<?= e(route('achievements.visibility', ['id' => $a['user_achievement_id']])) ?>">
          <?= csrf_field() ?><input type="hidden" name="is_public" value="<?= (int) $a['is_public'] ? '0' : '1' ?>">
          <button class="btn btn--ghost btn--sm" type="submit"><?= icon((int) $a['is_public'] ? 'globe' : 'lock', 'icon icon--sm') ?> <?= e((int) $a['is_public'] ? __('share.make_private') : __('share.make_public')) ?></button>
        </form>
        <?php if ((int) $a['is_public']): ?><a class="btn btn--outline btn--sm" href="<?= e(route('share.badge', ['token' => $a['share_token']])) ?>"><?= icon('share', 'icon icon--sm') ?> <?= e(__('share.share_title')) ?></a><?php endif; ?>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
</div>
