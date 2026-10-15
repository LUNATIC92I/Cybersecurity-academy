<section class="page-hero"><div class="container">
  <p class="eyebrow"><?= e(__('nav.catalog')) ?></p>
  <h1><?= e(__('catalog.title')) ?></h1>
  <p class="lead"><?= e(__('catalog.lead')) ?></p>
</div></section>
<section class="section section--tight"><div class="container">
  <form class="filter-bar card" method="get" action="<?= e(route('catalog')) ?>" role="search">
    <div class="form-group form-group--grow">
      <label class="form-label" for="q"><?= e(__('catalog.search')) ?></label>
      <input class="input" type="search" id="q" name="q" value="<?= e($filters['q']) ?>" placeholder="<?= e(__('catalog.search_placeholder')) ?>">
    </div>
    <div class="form-group">
      <label class="form-label" for="path"><?= e(__('catalog.path')) ?></label>
      <select class="input" id="path" name="path"><option value=""><?= e(__('common.all')) ?></option>
        <?php foreach ($paths as $p): ?><option value="<?= e($p['slug']) ?>" <?= $filters['path'] === $p['slug'] ? 'selected' : '' ?>><?= e($p['t']['title']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label" for="level"><?= e(__('catalog.level')) ?></label>
      <select class="input" id="level" name="level"><option value=""><?= e(__('common.all')) ?></option>
        <?php foreach (['beginner', 'intermediate', 'advanced', 'all'] as $l): ?><option value="<?= $l ?>" <?= $filters['level'] === $l ? 'selected' : '' ?>><?= e(level_label($l)) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label" for="access"><?= e(__('catalog.access')) ?></label>
      <select class="input" id="access" name="access"><option value=""><?= e(__('common.all')) ?></option>
        <?php foreach (['free', 'premium'] as $a): ?><option value="<?= $a ?>" <?= $filters['access'] === $a ? 'selected' : '' ?>><?= e(__('course.access.' . $a)) ?></option><?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn--primary" type="submit"><?= icon('search', 'icon icon--sm') ?> <?= e(__('catalog.filter')) ?></button>
  </form>
  <p class="muted" aria-live="polite"><?= e(trans_choice('catalog.results', $result['total'])) ?></p>
  <?php if ($result['items']): ?>
    <div class="grid grid--3">
      <?php foreach ($result['items'] as $course): ?><?= partial('partials/course-card', ['course' => $course]) ?><?php endforeach; ?>
    </div>
    <?= partial('partials/pagination', ['result' => $result]) ?>
  <?php else: ?>
    <?= partial('partials/empty', ['icon' => 'search', 'title' => __('catalog.empty_title'), 'text' => __('catalog.empty_text'), 'action' => [__('catalog.reset'), route('catalog')]]) ?>
  <?php endif; ?>
</div></section>
