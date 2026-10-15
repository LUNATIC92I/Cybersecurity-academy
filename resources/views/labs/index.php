<section class="page-hero"><div class="container">
  <p class="eyebrow"><?= e(__('nav.labs')) ?></p>
  <h1><?= e(__('labs.title')) ?></h1>
  <p class="lead"><?= e(__('labs.lead')) ?></p>
</div></section>
<section class="section section--tight"><div class="container">
  <div class="grid grid--3 lab-types">
    <?php foreach (['simulation' => 'terminal', 'download' => 'download', 'virtual' => 'server'] as $type => $ic): ?>
      <div class="card reveal"><h2 class="h4"><?= icon($ic) ?> <?= e(__('labs.type.' . $type)) ?></h2><p class="muted"><?= e(__('labs.type_desc.' . $type)) ?></p></div>
    <?php endforeach; ?>
  </div>
  <form class="filter-bar card" method="get">
    <div class="form-group"><label class="form-label" for="type"><?= e(__('labs.filter_type')) ?></label>
      <select class="input" id="type" name="type"><option value=""><?= e(__('common.all')) ?></option>
        <?php foreach (['simulation', 'download', 'virtual'] as $ty): ?><option value="<?= $ty ?>" <?= $filters['type'] === $ty ? 'selected' : '' ?>><?= e(__('labs.type.' . $ty)) ?></option><?php endforeach; ?>
      </select></div>
    <div class="form-group"><label class="form-label" for="path"><?= e(__('catalog.path')) ?></label>
      <select class="input" id="path" name="path"><option value=""><?= e(__('common.all')) ?></option>
        <?php foreach ($paths as $p): ?><option value="<?= e($p['slug']) ?>" <?= $filters['path'] === $p['slug'] ? 'selected' : '' ?>><?= e($p['t']['title']) ?></option><?php endforeach; ?>
      </select></div>
    <button class="btn btn--primary" type="submit"><?= e(__('catalog.filter')) ?></button>
  </form>
  <?php if ($labs): ?>
    <div class="grid grid--3"><?php foreach ($labs as $lab): ?><?= partial('partials/lab-card', ['lab' => $lab, 'passed' => $passed]) ?><?php endforeach; ?></div>
  <?php else: ?>
    <?= partial('partials/empty', ['icon' => 'flask', 'title' => __('labs.empty_title'), 'text' => __('labs.empty_text')]) ?>
  <?php endif; ?>
  <div class="notice notice--info"><?= icon('shield') ?> <span><?= e(__('labs.safety_notice')) ?> <a href="<?= e(route('legal.responsible')) ?>"><?= e(__('legal.responsible.title')) ?></a></span></div>
</div></section>
