<section class="page-hero"><div class="container">
  <p class="eyebrow"><?= e(__('nav.methodology')) ?></p>
  <h1><?= e(__('methodology.title')) ?></h1>
  <p class="lead"><?= e(__('methodology.lead')) ?></p>
</div></section>
<section class="section"><div class="container">
  <ol class="timeline">
    <?php foreach (['learn', 'practise', 'assess', 'certify', 'share'] as $i => $step): ?>
      <li class="timeline__item reveal">
        <span class="timeline__dot"><?= $i + 1 ?></span>
        <div class="card">
          <h2 class="h3"><?= e(__('methodology.' . $step . '.title')) ?></h2>
          <p><?= e(__('methodology.' . $step . '.text')) ?></p>
        </div>
      </li>
    <?php endforeach; ?>
  </ol>
  <div class="card reveal">
    <h2 class="h3"><?= e(__('methodology.completion_title')) ?></h2>
    <p><?= e(__('methodology.completion_text')) ?></p>
    <ul class="check-list">
      <li><?= icon('check') ?> <?= e(__('methodology.rule_lessons')) ?></li>
      <li><?= icon('check') ?> <?= e(__('methodology.rule_labs')) ?></li>
      <li><?= icon('check') ?> <?= e(__('methodology.rule_exam')) ?></li>
    </ul>
  </div>
</div></section>
