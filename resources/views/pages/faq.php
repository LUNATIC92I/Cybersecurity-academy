<section class="page-hero"><div class="container">
  <p class="eyebrow"><?= e(__('nav.faq')) ?></p>
  <h1><?= e(__('faq.title')) ?></h1>
  <p class="lead"><?= e(__('faq.lead')) ?></p>
</div></section>
<section class="section"><div class="container container--narrow">
  <div class="accordion">
    <?php foreach ($faqs as $faq): ?>
      <details class="accordion__item reveal"><summary><?= e($faq['q']) ?></summary><p><?= e($faq['a']) ?></p></details>
    <?php endforeach; ?>
  </div>
  <div class="card center reveal">
    <h2 class="h3"><?= e(__('faq.more_title')) ?></h2>
    <p><?= e(__('faq.more_text')) ?></p>
    <a class="btn btn--primary" href="<?= e(route('contact')) ?>"><?= e(__('nav.contact')) ?></a>
  </div>
</div></section>
