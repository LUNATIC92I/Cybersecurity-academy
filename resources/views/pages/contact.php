<?php $hideErrorSummary = true; $cat = old('category', app('request')->query('category', 'general')); ?>
<section class="page-hero"><div class="container">
  <p class="eyebrow"><?= e(__('nav.contact')) ?></p>
  <h1><?= e(__('contact.title')) ?></h1>
  <p class="lead"><?= e(__('contact.lead')) ?></p>
</div></section>
<section class="section"><div class="container split">
  <form class="card form reveal" method="post" action="<?= e(route('contact.submit')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="hp" aria-hidden="true"><label for="website">Website</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
    <div class="form-row">
      <?= partial('partials/field', ['name' => 'name', 'label' => __('field.name'), 'value' => old('name', auth()->user()['display_name'] ?? ''), 'required' => true, 'autocomplete' => 'name']) ?>
      <?= partial('partials/field', ['name' => 'email', 'type' => 'email', 'label' => __('field.email'), 'value' => old('email', auth()->user()['email'] ?? ''), 'required' => true, 'autocomplete' => 'email']) ?>
    </div>
    <div class="form-group">
      <label class="form-label" for="category"><?= e(__('field.category')) ?></label>
      <select class="input" id="category" name="category">
        <?php foreach (['general', 'technical', 'billing', 'enterprise', 'certificate', 'security'] as $c): ?>
          <option value="<?= e($c) ?>" <?= $cat === $c ? 'selected' : '' ?>><?= e(__('contact.category.' . $c)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?= partial('partials/field', ['name' => 'subject', 'label' => __('field.subject'), 'value' => old('subject'), 'required' => true]) ?>
    <?= partial('partials/field', ['name' => 'message', 'type' => 'textarea', 'label' => __('field.message'), 'value' => old('message'), 'required' => true, 'rows' => 7]) ?>
    <p class="form-hint"><?= e(__('contact.privacy')) ?> <a href="<?= e(route('legal.privacy')) ?>"><?= e(__('legal.privacy.title')) ?></a></p>
    <button class="btn btn--primary" type="submit"><?= icon('mail', 'icon icon--sm') ?> <?= e(__('contact.send')) ?></button>
  </form>
  <aside class="stack reveal">
    <div class="card"><h2 class="h4"><?= icon('help') ?> <?= e(__('contact.help_title')) ?></h2><p><?= e(__('contact.help_text')) ?></p><a href="<?= e(route('faq')) ?>"><?= e(__('nav.faq')) ?></a></div>
    <div class="card"><h2 class="h4"><?= icon('shield') ?> <?= e(__('contact.security_title')) ?></h2><p><?= e(__('contact.security_text')) ?></p></div>
    <div class="card"><h2 class="h4"><?= icon('building') ?> <?= e(__('contact.enterprise_title')) ?></h2><p><?= e(__('contact.enterprise_text')) ?></p></div>
    <div class="card"><h2 class="h4"><?= icon('mail') ?> <?= e(__('contact.email_title')) ?></h2><p><a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a></p></div>
  </aside>
</div></section>
