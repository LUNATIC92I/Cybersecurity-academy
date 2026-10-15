<section class="page-hero"><div class="container">
  <p class="eyebrow"><?= e(__('nav.pricing')) ?></p>
  <h1><?= e(__('pricing.title')) ?></h1>
  <p class="lead"><?= e(__('pricing.lead')) ?></p>
  <?php if (!$live): ?>
    <div class="notice notice--warning"><?= icon('info') ?> <span><?= e(__('pricing.demo_notice')) ?></span></div>
  <?php endif; ?>
</div></section>
<section class="section"><div class="container">
  <div class="grid grid--4 pricing-grid">
    <?php foreach ($plans as $plan): $t = $plan['t']; $featured = $plan['code'] === 'annual'; ?>
      <article class="card plan-card reveal <?= $featured ? 'plan-card--featured' : '' ?>">
        <?php if ($featured): ?><span class="chip chip--accent plan-card__flag"><?= e(__('pricing.best_value')) ?></span><?php endif; ?>
        <h2 class="h3"><?= e($t['name']) ?></h2>
        <p class="muted"><?= e($t['description']) ?></p>
        <p class="plan-card__price">
          <?php if ($plan['is_contact_sales']): ?><?= e(__('pricing.custom')) ?>
          <?php elseif ((int) $plan['price_cents'] === 0): ?><?= e(__('pricing.free')) ?>
          <?php else: ?><?= e(format_money((int) $plan['price_cents'], $plan['currency'])) ?><small> / <?= e(__('pricing.per.' . $plan['billing_interval'])) ?></small><?php endif; ?>
        </p>
        <ul class="check-list">
          <?php foreach (\App\Services\Localizer::lines($t['features']) as $f): ?><li><?= icon('check') ?> <?= e($f) ?></li><?php endforeach; ?>
        </ul>
        <?php if ($plan['is_contact_sales']): ?>
          <a class="btn btn--outline btn--block" href="<?= e(route('contact')) ?>?category=enterprise"><?= e(__('pricing.contact_sales')) ?></a>
        <?php elseif ((int) $plan['price_cents'] === 0): ?>
          <a class="btn btn--outline btn--block" href="<?= e(auth()->check() ? route('dashboard.courses') : route('register')) ?>"><?= e(__('cta.start_learning')) ?></a>
        <?php elseif (auth()->check()): ?>
          <form method="post" action="<?= e(route('checkout.create')) ?>" class="stack">
            <?= csrf_field() ?>
            <input type="hidden" name="item_type" value="plan"><input type="hidden" name="item_id" value="<?= (int) $plan['id'] ?>">
            <label class="sr-only" for="coupon-<?= (int) $plan['id'] ?>"><?= e(__('pricing.coupon')) ?></label>
            <input class="input input--sm" id="coupon-<?= (int) $plan['id'] ?>" name="coupon" placeholder="<?= e(__('pricing.coupon')) ?>" maxlength="40" autocomplete="off">
            <button class="btn <?= $featured ? 'btn--primary' : 'btn--outline' ?> btn--block" type="submit"><?= e(__('pricing.subscribe')) ?></button>
          </form>
        <?php else: ?>
          <a class="btn <?= $featured ? 'btn--primary' : 'btn--outline' ?> btn--block" href="<?= e(route('register')) ?>"><?= e(__('pricing.create_account')) ?></a>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>

  <?php if ($premiumCourses): ?>
    <h2 class="subheading reveal"><?= e(__('pricing.individual_title')) ?></h2>
    <div class="grid grid--2">
      <?php foreach ($premiumCourses as $course): ?>
        <article class="card plan-row reveal" id="course-<?= (int) $course['id'] ?>">
          <div><h3><?= e($course['t']['title']) ?></h3><p class="muted"><?= e($course['t']['subtitle']) ?></p></div>
          <div class="plan-row__buy">
            <strong><?= e(format_money((int) $course['price_cents'], $course['currency'])) ?></strong>
            <?php if (auth()->check()): ?>
              <form method="post" action="<?= e(route('checkout.create')) ?>">
                <?= csrf_field() ?><input type="hidden" name="item_type" value="course"><input type="hidden" name="item_id" value="<?= (int) $course['id'] ?>">
                <button class="btn btn--outline btn--sm" type="submit"><?= e(__('pricing.buy_course')) ?></button>
              </form>
            <?php else: ?><a class="btn btn--outline btn--sm" href="<?= e(route('login')) ?>"><?= e(__('nav.login')) ?></a><?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <p class="form-hint center"><?= e(__('pricing.no_card_storage')) ?></p>
</div></section>
