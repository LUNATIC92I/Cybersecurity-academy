<section class="page-hero"><div class="container">
  <p class="eyebrow"><?= e(__('nav.about')) ?></p>
  <h1><?= e(__('about.title')) ?></h1>
  <p class="lead"><?= e(__('about.lead')) ?></p>
</div></section>
<section class="section"><div class="container split">
  <div class="prose reveal">
    <h2><?= e(__('about.mission_title')) ?></h2>
    <p><?= e(__('about.mission_p1')) ?></p>
    <p><?= e(__('about.mission_p2')) ?></p>
    <h2><?= e(__('about.principles_title')) ?></h2>
    <ul>
      <li><strong><?= e(__('about.principle1_title')) ?></strong> — <?= e(__('about.principle1')) ?></li>
      <li><strong><?= e(__('about.principle2_title')) ?></strong> — <?= e(__('about.principle2')) ?></li>
      <li><strong><?= e(__('about.principle3_title')) ?></strong> — <?= e(__('about.principle3')) ?></li>
      <li><strong><?= e(__('about.principle4_title')) ?></strong> — <?= e(__('about.principle4')) ?></li>
    </ul>
    <h2><?= e(__('about.honesty_title')) ?></h2>
    <p><?= e(__('about.honesty')) ?></p>
  </div>
  <aside class="card reveal">
    <h2 class="h3"><?= e(__('about.facts_title')) ?></h2>
    <dl class="kv">
      <div><dt><?= e(__('stats.paths')) ?></dt><dd><?= (int) $stats['paths'] ?></dd></div>
      <div><dt><?= e(__('stats.courses')) ?></dt><dd><?= (int) $stats['courses'] ?></dd></div>
      <div><dt><?= e(__('stats.modules')) ?></dt><dd><?= (int) $stats['modules'] ?></dd></div>
      <div><dt><?= e(__('stats.lessons')) ?></dt><dd><?= (int) $stats['lessons'] ?></dd></div>
      <div><dt><?= e(__('stats.labs')) ?></dt><dd><?= (int) $stats['labs'] ?></dd></div>
      <div><dt><?= e(__('stats.questions')) ?></dt><dd><?= (int) $stats['questions'] ?></dd></div>
    </dl>
    <p class="form-hint"><?= e(__('home.stats_note')) ?></p>
    <a class="btn btn--primary btn--block" href="<?= e(route('catalog')) ?>"><?= e(__('cta.explore_training')) ?></a>
  </aside>
</div></section>
