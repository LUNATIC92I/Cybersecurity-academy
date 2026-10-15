<h1 class="h2"><?= e(__('auth.register.title')) ?></h1>
<p class="muted"><?= e(__('auth.register.lead')) ?></p>
<form method="post" action="<?= e(route('register.submit')) ?>" class="form" novalidate>
  <?= csrf_field() ?>
  <?= partial('partials/field', ['name' => 'display_name', 'label' => __('field.display_name'), 'value' => old('display_name'), 'required' => true, 'autocomplete' => 'name', 'hint' => __('auth.display_name_hint')]) ?>
  <?= partial('partials/field', ['name' => 'email', 'type' => 'email', 'label' => __('field.email'), 'value' => old('email'), 'required' => true, 'autocomplete' => 'email']) ?>
  <?= partial('partials/field', ['name' => 'password', 'type' => 'password', 'label' => __('field.password'), 'required' => true, 'autocomplete' => 'new-password', 'minlength' => 12, 'hint' => __('validation.password')]) ?>
  <?= partial('partials/field', ['name' => 'password_confirmation', 'type' => 'password', 'label' => __('field.password_confirmation'), 'required' => true, 'autocomplete' => 'new-password']) ?>
  <div class="form-group">
    <label class="form-label" for="locale"><?= e(__('field.locale')) ?></label>
    <select class="input" id="locale" name="locale"><option value="en" <?= old('locale', locale()) === 'en' ? 'selected' : '' ?>>English</option><option value="fr" <?= old('locale', locale()) === 'fr' ? 'selected' : '' ?>>Français</option></select>
  </div>
  <div class="form-group <?= error_for('accept_terms') ? 'has-error' : '' ?>">
    <label class="checkbox"><input type="checkbox" name="accept_terms" value="1" required <?= old('accept_terms') ? 'checked' : '' ?>>
      <span><?= e(__('auth.accept_prefix')) ?> <a href="<?= e(route('legal.terms')) ?>" target="_blank"><?= e(__('legal.terms.title')) ?></a>, <a href="<?= e(route('legal.privacy')) ?>" target="_blank"><?= e(__('legal.privacy.title')) ?></a> <?= e(__('common.and')) ?> <a href="<?= e(route('legal.responsible')) ?>" target="_blank"><?= e(__('legal.responsible.title')) ?></a>.</span></label>
    <?php if ($err = error_for('accept_terms')): ?><p class="form-error"><?= e($err) ?></p><?php endif; ?>
  </div>
  <button class="btn btn--primary btn--block" type="submit"><?= e(__('auth.register.submit')) ?></button>
</form>
<p class="center muted"><?= e(__('auth.have_account')) ?> <a href="<?= e(route('login')) ?>"><?= e(__('nav.login')) ?></a></p>
