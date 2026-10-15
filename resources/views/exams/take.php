<section class="section section--tight"><div class="container container--narrow">
  <div class="exam-bar card" data-exam-timer data-remaining="<?= (int) $remaining ?>" data-form="exam-form">
    <div><strong><?= e($quiz['t']['title']) ?></strong><small class="muted"><?= e(__('exams.attempt_no', ['n' => $attempt['attempt_no']])) ?></small></div>
    <div class="exam-bar__timer" role="timer" aria-live="off"><?= icon('clock', 'icon icon--sm') ?> <span data-timer-display><?= gmdate('i:s', $remaining) ?></span></div>
  </div>
  <p class="sr-only" aria-live="assertive" data-timer-announce></p>
  <form id="exam-form" method="post" action="<?= e(route('attempts.submit', ['id' => $attempt['id']])) ?>" class="stack" data-exam-form>
    <?= csrf_field() ?>
    <?php foreach ($questions as $n => $q): $qt = $q['t']; $name = 'responses[' . (int) $q['id'] . ']'; ?>
      <fieldset class="card question">
        <legend class="question__legend"><span class="question__num"><?= e(__('exams.question_n', ['n' => $n + 1, 'total' => count($questions)])) ?></span>
          <span class="chip chip--muted chip--xs"><?= e(__('exams.qtype.' . $q['question_type'])) ?></span>
          <span class="chip chip--muted chip--xs"><?= e(trans_choice('labs.points', (int) $q['points'])) ?></span></legend>
        <?php if (!empty($qt['scenario'])): ?><div class="question__scenario"><?= (new \App\Services\ContentRenderer())->render($qt['scenario']) ?></div><?php endif; ?>
        <p class="question__stem"><?= e($qt['stem']) ?></p>
        <?php if (!empty($qt['_fallback'])): ?><p class="translation-note"><?= icon('globe', 'icon icon--sm') ?> <?= e(__('content.fallback_short')) ?></p><?php endif; ?>
        <?php if (in_array($q['question_type'], ['single', 'true_false', 'scenario'], true)): ?>
          <div class="option-list"><?php foreach ($q['options'] as $o): ?><label class="option"><input type="radio" name="<?= e($name) ?>" value="<?= (int) $o['id'] ?>"> <span><?= e($o['t']['label'] ?? '') ?></span></label><?php endforeach; ?></div>
        <?php elseif ($q['question_type'] === 'multiple'): ?>
          <p class="form-hint"><?= e(__('exams.select_all')) ?></p>
          <div class="option-list"><?php foreach ($q['options'] as $o): ?><label class="option"><input type="checkbox" name="<?= e($name) ?>[]" value="<?= (int) $o['id'] ?>"> <span><?= e($o['t']['label'] ?? '') ?></span></label><?php endforeach; ?></div>
        <?php elseif ($q['question_type'] === 'matching'): ?>
          <div class="matching">
            <?php foreach ($q['options'] as $o): $sid = 'm-' . $q['id'] . '-' . $o['id']; ?>
              <div class="matching__row"><label for="<?= e($sid) ?>"><?= e($o['t']['label'] ?? '') ?></label>
                <select class="input" id="<?= e($sid) ?>" name="<?= e($name) ?>[<?= (int) $o['id'] ?>]"><option value=""><?= e(__('exams.choose')) ?></option>
                  <?php foreach ($q['match_choices'] as $key => $label): ?><option value="<?= e($key) ?>"><?= e($label) ?></option><?php endforeach; ?>
                </select></div>
            <?php endforeach; ?>
          </div>
        <?php elseif ($q['question_type'] === 'numeric'): ?>
          <label class="sr-only" for="q-<?= (int) $q['id'] ?>"><?= e(__('exams.your_answer')) ?></label>
          <input class="input input--short" id="q-<?= (int) $q['id'] ?>" name="<?= e($name) ?>" inputmode="decimal" autocomplete="off">
        <?php else: ?>
          <label class="sr-only" for="q-<?= (int) $q['id'] ?>"><?= e(__('exams.your_answer')) ?></label>
          <input class="input" id="q-<?= (int) $q['id'] ?>" name="<?= e($name) ?>" maxlength="500" autocomplete="off" spellcheck="false">
        <?php endif; ?>
      </fieldset>
    <?php endforeach; ?>
    <div class="card exam-submit">
      <p class="muted"><?= e(__('exams.submit_hint')) ?></p>
      <button class="btn btn--primary btn--lg" type="submit" data-once data-confirm="<?= e(__('exams.confirm_submit')) ?>"><?= e(__('exams.submit')) ?></button>
    </div>
  </form>
</div></section>
