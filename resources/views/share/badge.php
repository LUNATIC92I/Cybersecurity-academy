<section class="section"><div class="container container--narrow">
  <article class="card share-hero">
    <img class="share-hero__image" src="<?= e(route('share.badge.image', ['token' => $badge['share_token']])) ?>" alt="<?= e(__('share.image_alt', ['title' => $badge['t']['title']])) ?>" width="1200" height="630" loading="lazy">
    <p class="eyebrow"><?= icon($badge['icon'], 'icon icon--sm') ?> <?= e(__('share.card_headline_badge')) ?></p>
    <h1 class="h2"><?= e($badge['t']['title']) ?></h1>
    <p class="lead"><?= e($badge['display_name']) ?> · <?= e(format_date($badge['earned_at'])) ?></p>
    <p><?= e($badge['t']['description']) ?></p>
    <?= partial('partials/share', ['url' => $shareUrl, 'subjectType' => 'achievement', 'subjectId' => 0, 'imageUrl' => route('share.badge.image', ['token' => $badge['share_token']]), 'canRecord' => false]) ?>
  </article>
</div></section>
