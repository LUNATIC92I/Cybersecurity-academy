<?php $passed = (int) $attempt['passed'] === 1; ?>
<section class="section section--tight"><div class="container container--narrow">
  <div class="card result-card <?= $passed ? 'result-card--pass' : 'result-card--fail' ?>">
    <div class="score-ring" data-score="<?= (int) $attempt['percent'] ?>" role="img" aria-label="<?= e(__('exams.score_aria', ['score' => $attempt['percent']])) ?>"><span><?= (int) $attempt['percent'] ?>%</span></div>
    <div>
      <p class="eyebrow"><?= e($quiz['t']['title']) ?> · <?= e(__('exams.attempt_no', ['n' => $attempt['attempt_no']])) ?></p>
      <h1 class="h2"><?= e($attempt['status'] === 'expired' ? __('exams.expired_title') : ($passed ? __('exams.passed_title') : __('exams.failed_title'))) ?></h1>
      <p><?= e(__('exams.points_summary', ['score' => $attempt['score_points'], 'max' => $attempt['max_points']])) ?></p>
      <div class="btn-row">
        <?php if ($certificate): ?><a class="btn btn--primary" href="<?= e(route('dashboard.certificates')) ?>"><?= icon('award', 'icon icon--sm') ?> <?= e(__('exams.view_certificate')) ?></a><?php endif; ?>
        <?php if (!$passed && $eligibility['allowed']): ?><a class="btn btn--outline" href="<?= e(route('assessments.show', ['slug' => $quiz['slug']])) ?>"><?= e(__('exams.retake')) ?></a><?php endif; ?>
        <a class="btn btn--ghost" href="<?= e(route('courses.show', ['slug' => $course['slug']])) ?>"><?= e(__('exams.back_course')) ?></a>
      </div>
      <?php if (!$passed && !$eligibility['allowed'] && $eligibility['reason'] !== 'already_passed'): ?><p class="form-hint"><?= e(__('exams.cannot_start.' . $eligibility['reason'], ['minutes' => $eligibility['wait_minutes'] ?? 0])) ?></p><?php endif; ?>
    </div>
  </div>
  <?php if (!$showAnswers): ?>
    <div class="notice notice--info"><?= icon('lock') ?> <span><?= e(__('exams.answers_hidden.' . $quiz['show_explanations'])) ?></span></div>
  <?php else: ?>
    <h2 class="subheading"><?= e(__('exams.review_answers')) ?></h2>
    <?php foreach ($questions as $n => $q): $r = $q['response']; $ok = $r && (int) $r['is_correct'] === 1; $given = $r['response'] ?? null; ?>
      <article class="card question question--review <?= $ok ? 'is-correct' : 'is-incorrect' ?>">
        <p class="question__legend"><span class="question__num"><?= e(__('exams.question_n', ['n' => $n + 1, 'total' => count($questions)])) ?></span>
          <span class="<?= $ok ? 'text-success' : 'text-danger' ?>"><?= icon($ok ? 'check-circle' : 'x-circle', 'icon icon--sm') ?> <?= e($ok ? __('exams.correct') : __('exams.incorrect')) ?> (<?= e((float) ($r['points_awarded'] ?? 0)) ?>/<?= (int) $q['points'] ?>)</span></p>
        <?php if (!empty($q['t']['scenario'])): ?><div class="question__scenario"><?= $renderer->render($q['t']['scenario']) ?></div><?php endif; ?>
        <p class="question__stem"><?= e($q['t']['stem']) ?></p>
        <?php if ($q['options'] && $q['question_type'] !== 'matching'): ?>
          <ul class="review-options">
            <?php foreach ($q['options'] as $o): $chosen = is_array($given) ? in_array((string) $o['id'], array_map('strval', $given), true) : (string) $given === (string) $o['id']; ?>
              <li class="<?= (int) $o['is_correct'] ? 'is-answer' : '' ?> <?= $chosen ? 'is-chosen' : '' ?>"><?= icon((int) $o['is_correct'] ? 'check' : ($chosen ? 'close' : 'info'), 'icon icon--sm') ?> <?= e($o['t']['label'] ?? '') ?> <?php if ($chosen): ?><small class="muted">(<?= e(__('exams.your_choice')) ?>)</small><?php endif; ?></li>
            <?php endforeach; ?>
          </ul>
        <?php elseif ($q['question_type'] === 'matching'): ?>
          <ul class="review-options"><?php foreach ($q['options'] as $o): $pick = is_array($given) ? ($given[$o['id']] ?? '') : ''; ?>
            <li class="<?= $pick === $o['match_key'] ? 'is-answer is-chosen' : 'is-chosen' ?>"><?= e($o['t']['label'] ?? '') ?> → <strong><?= e($q['match_choices'][$o['match_key']] ?? '') ?></strong><?php if ($pick !== $o['match_key']): ?> <small class="muted">(<?= e(__('exams.your_choice')) ?>: <?= e($q['match_choices'][$pick] ?? '—') ?>)</small><?php endif; ?></li>
          <?php endforeach; ?></ul>
        <?php else: ?>
          <p><?= e(__('exams.your_answer')) ?>: <code><?= e(is_scalar($given) ? (string) $given : '—') ?></code></p>
          <p><?= e(__('exams.expected_answer')) ?>: <code><?= e($q['question_type'] === 'numeric' ? ($q['answer_spec']['value'] ?? '') : implode(' / ', (array) ($q['answer_spec']['accepted'] ?? []))) ?></code></p>
        <?php endif; ?>
        <?php if (!empty($q['t']['explanation'])): ?><div class="question__explanation"><strong><?= e(__('exams.explanation')) ?></strong> <?= e($q['t']['explanation']) ?></div><?php endif; ?>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>
</div></section>
