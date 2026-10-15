<?php $t = $lab['t']; $passedAny = (bool) array_filter($attempts, fn ($a) => (int) $a['passed'] === 1); ?>
<section class="page-hero accent-<?= e($lab['path_accent'] ?? 'blue') ?>"><div class="container">
  <nav class="breadcrumb" aria-label="<?= e(__('common.breadcrumb')) ?>"><a href="<?= e(route('labs.index')) ?>"><?= e(__('nav.labs')) ?></a><?php if ($course): ?> / <a href="<?= e(route('courses.show', ['slug' => $course['slug']])) ?>"><?= e($course['t']['title']) ?></a><?php endif; ?></nav>
  <h1><?= e($t['title']) ?></h1>
  <p class="lead"><?= e($t['summary']) ?></p>
  <ul class="meta-list meta-list--hero">
    <li><?= icon('terminal', 'icon icon--sm') ?> <?= e(__('labs.type.' . $lab['lab_type'])) ?></li>
    <li><?= icon('chart', 'icon icon--sm') ?> <?= e(level_label($lab['difficulty'])) ?></li>
    <li><?= icon('clock', 'icon icon--sm') ?> <?= e(minutes_label((int) $lab['duration_minutes'])) ?></li>
    <li><?= icon('target', 'icon icon--sm') ?> <?= e(__('labs.pass_mark', ['pass' => $lab['pass_percent'], 'points' => $lab['max_points']])) ?></li>
    <?php if ($lab['max_attempts']): ?><li><?= icon('clipboard', 'icon icon--sm') ?> <?= e(__('labs.attempts_limit', ['n' => $lab['max_attempts']])) ?></li><?php endif; ?>
  </ul>
  <?php if (!empty($t['_fallback'])): ?><div class="notice notice--info"><?= icon('globe') ?> <span><?= e(__('content.fallback')) ?></span></div><?php endif; ?>
  <?php if ($lab['lab_type'] === 'virtual'): ?><div class="notice notice--warning"><?= icon('server') ?> <span><?= e(__('labs.virtual_notice')) ?></span></div><?php endif; ?>
</div></section>

<section class="section section--tight"><div class="container split split--wide">
  <div class="stack">
    <div class="grid grid--3">
      <?php foreach (['objectives' => ['target', $objectives], 'tools' => ['terminal', $tools], 'prerequisites' => ['layers', $prerequisites]] as $key => [$ic, $items]): if (!$items) { continue; } ?>
        <section class="card"><h2 class="h4"><?= icon($ic) ?> <?= e(__('labs.section.' . $key)) ?></h2><ul class="plain-list"><?php foreach ($items as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul></section>
      <?php endforeach; ?>
    </div>
    <?php foreach (['scenario', 'setup', 'instructions'] as $key): if ($sections[$key] === '') { continue; } ?>
      <section class="card"><h2 class="h3"><?= e(__('labs.section.' . $key)) ?></h2><div class="prose"><?= $sections[$key] ?></div></section>
    <?php endforeach; ?>

    <?php if (!auth()->check()): ?>
      <?= partial('partials/empty', ['icon' => 'lock', 'title' => __('labs.login_title'), 'text' => __('labs.login_text'), 'action' => [__('nav.login'), route('login')]]) ?>
    <?php elseif (!$canAttempt): ?>
      <?= partial('partials/empty', ['icon' => 'lock', 'title' => __('labs.no_access_title'), 'text' => __('labs.no_access_text'), 'action' => [__('course.get_access'), route('pricing')]]) ?>
    <?php elseif (!$acknowledged): ?>
      <form class="card ack-card" method="post" action="<?= e(route('labs.acknowledge', ['slug' => $lab['slug']])) ?>">
        <?= csrf_field() ?>
        <h2 class="h3"><?= icon('alert') ?> <?= e(__('labs.ack_title')) ?></h2>
        <p><?= e(__('labs.ack_text')) ?></p>
        <label class="checkbox"><input type="checkbox" name="confirm_authorized" value="1" required> <span><?= e(__('labs.ack_authorized')) ?></span></label>
        <label class="checkbox"><input type="checkbox" name="confirm_isolated" value="1" required> <span><?= e(__('labs.ack_isolated')) ?></span></label>
        <p class="form-hint"><a href="<?= e(route('legal.responsible')) ?>"><?= e(__('legal.responsible.title')) ?></a></p>
        <button class="btn btn--primary" type="submit"><?= e(__('labs.ack_submit')) ?></button>
      </form>
    <?php else: ?>
      <?php if ($dataset): ?>
        <section class="card log-viewer" data-log-viewer data-src="<?= e(route('labs.dataset.json', ['slug' => $lab['slug']])) ?>">
          <div class="log-viewer__head">
            <h2 class="h3"><?= icon('terminal') ?> <?= e(__('labs.dataset')) ?> <small class="muted"><?= e($dataset) ?></small></h2>
            <a class="btn btn--ghost btn--sm" href="<?= e(route('labs.dataset', ['slug' => $lab['slug']])) ?>"><?= icon('download', 'icon icon--sm') ?> <?= e(__('labs.download_dataset')) ?></a>
          </div>
          <label class="sr-only" for="log-filter"><?= e(__('labs.filter_rows')) ?></label>
          <input class="input" id="log-filter" type="search" placeholder="<?= e(__('labs.filter_placeholder')) ?>" data-log-filter>
          <p class="form-hint" data-log-count aria-live="polite"></p>
          <div class="log-viewer__table" data-log-table><div class="skeleton skeleton--table" aria-hidden="true"></div><noscript><?= e(__('labs.noscript')) ?></noscript></div>
        </section>
      <?php endif; ?>
      <?php if ($resources): ?>
        <section class="card"><h2 class="h4"><?= icon('download') ?> <?= e(__('lesson.resources')) ?></h2>
          <ul class="link-list"><?php foreach ($resources as $r): ?><li><a href="<?= e(route('resources.download', ['id' => $r['id']])) ?>"><?= e($r['title']) ?></a> <small class="muted"><?= e($r['original_name']) ?></small></li><?php endforeach; ?></ul>
        </section>
      <?php endif; ?>

      <form class="card lab-tasks" method="post" action="<?= e(route('labs.submit', ['slug' => $lab['slug']])) ?>" id="tasks">
        <?= csrf_field() ?>
        <h2 class="h3"><?= icon('check-circle') ?> <?= e(__('labs.tasks')) ?></h2>
        <ol class="task-list">
          <?php foreach ($tasks as $task): $tt = $task['t']; $res = $lastResults[$task['id']] ?? null; $prev = $lastAnswers[$task['id']] ?? null; $fid = 'task-' . $task['id']; $options = json_decode((string) ($tt['options'] ?? ''), true) ?: []; ?>
            <li class="task <?= $res ? ($res['correct'] ? 'task--correct' : 'task--incorrect') : '' ?>">
              <div class="task__head"><label for="<?= e($fid) ?>" class="task__prompt"><?= e($tt['prompt']) ?></label><span class="chip chip--muted chip--xs"><?= e(trans_choice('labs.points', (int) $task['points'])) ?></span></div>
              <?php if ($task['task_type'] === 'choice'): ?>
                <div class="option-list" role="radiogroup" aria-labelledby="<?= e($fid) ?>">
                  <?php foreach ($options as $val => $label): ?><label class="option"><input type="radio" name="answers[<?= (int) $task['id'] ?>]" value="<?= e($val) ?>" <?= (string) $prev === (string) $val ? 'checked' : '' ?> <?= $val === array_key_first($options) ? 'id="' . e($fid) . '"' : '' ?>> <span><?= e($label) ?></span></label><?php endforeach; ?>
                </div>
              <?php elseif ($task['task_type'] === 'multi'): ?>
                <div class="option-list">
                  <?php foreach ($options as $val => $label): ?><label class="option"><input type="checkbox" name="answers[<?= (int) $task['id'] ?>][]" value="<?= e($val) ?>" <?= is_array($prev) && in_array((string) $val, array_map('strval', $prev), true) ? 'checked' : '' ?> <?= $val === array_key_first($options) ? 'id="' . e($fid) . '"' : '' ?>> <span><?= e($label) ?></span></label><?php endforeach; ?>
                </div>
              <?php elseif ($task['task_type'] === 'query'): ?>
                <textarea class="input input--code" id="<?= e($fid) ?>" name="answers[<?= (int) $task['id'] ?>]" rows="7" spellcheck="false" maxlength="4000"><?= e(is_string($prev) ? $prev : '') ?></textarea>
                <p class="form-hint"><?= e(__('labs.query_hint')) ?></p>
              <?php else: ?>
                <input class="input <?= $task['task_type'] === 'number' ? 'input--short' : '' ?>" id="<?= e($fid) ?>" name="answers[<?= (int) $task['id'] ?>]" value="<?= e(is_string($prev) ? $prev : '') ?>" <?= $task['task_type'] === 'number' ? 'inputmode="decimal"' : '' ?> maxlength="500" autocomplete="off">
              <?php endif; ?>
              <?php if (!empty($tt['hint'])): ?><details class="content-block content-block--hint"><summary><?= e(__('content.block.hint')) ?></summary><div class="content-block__body"><p><?= e($tt['hint']) ?></p></div></details><?php endif; ?>
              <?php if ($res): ?><p class="task__result"><?= icon($res['correct'] ? 'check-circle' : 'x-circle', 'icon icon--sm') ?> <?= e($res['correct'] ? __('labs.task_correct') : __('labs.task_incorrect')) ?></p><?php endif; ?>
              <?php if ($res && $solution !== null && !empty($tt['explanation'])): ?><p class="task__explanation"><?= e($tt['explanation']) ?></p><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ol>
        <button class="btn btn--primary" type="submit" data-once><?= e(__('labs.submit')) ?></button>
        <p class="form-hint"><?= e(__('labs.server_scored')) ?></p>
      </form>
    <?php endif; ?>

    <?php if ($expected !== ''): ?><section class="card"><h2 class="h3"><?= e(__('labs.section.expected')) ?></h2><div class="prose"><?= $expected ?></div></section><?php endif; ?>
    <?php if ($solution !== null && $solution !== ''): ?>
      <details class="card content-block content-block--solution"><summary><?= e(__('labs.section.solution')) ?></summary><div class="prose content-block__body"><?= $solution ?></div></details>
    <?php elseif (auth()->check()): ?>
      <div class="notice notice--info"><?= icon('lock') ?> <span><?= e(__('labs.solution_locked', ['n' => \App\Services\LabService::SOLUTION_AFTER_ATTEMPTS])) ?></span></div>
    <?php endif; ?>
    <?php if ($cleanup !== ''): ?><section class="card"><h2 class="h3"><?= e(__('labs.section.cleanup')) ?></h2><div class="prose"><?= $cleanup ?></div></section><?php endif; ?>
    <?php if ($lessonsLearned !== ''): ?><section class="card"><h2 class="h3"><?= icon('shield') ?> <?= e(__('labs.section.lessons_learned')) ?></h2><div class="prose"><?= $lessonsLearned ?></div></section><?php endif; ?>
  </div>

  <aside class="stack">
    <section class="card" id="results">
      <h2 class="h4"><?= e(__('labs.your_attempts')) ?></h2>
      <?php if ($attempts): ?>
        <ul class="attempt-list">
          <?php foreach ($attempts as $a): ?>
            <li><span><?= e(__('labs.attempt_no', ['n' => $a['attempt_no']])) ?></span><strong class="<?= (int) $a['passed'] ? 'text-success' : 'text-warning' ?>"><?= (int) $a['percent'] ?>%</strong><small><?= e(format_datetime($a['submitted_at'])) ?></small></li>
          <?php endforeach; ?>
        </ul>
        <?php if ($passedAny): ?><p class="text-success"><?= icon('check-circle', 'icon icon--sm') ?> <?= e(__('labs.passed_short')) ?></p><?php endif; ?>
      <?php else: ?><p class="muted"><?= e(__('labs.no_attempts')) ?></p><?php endif; ?>
    </section>
    <?php if ($scoring !== ''): ?><section class="card"><h2 class="h4"><?= e(__('labs.section.scoring')) ?></h2><div class="prose prose--sm"><?= $scoring ?></div></section><?php endif; ?>
  </aside>
</div></section>
