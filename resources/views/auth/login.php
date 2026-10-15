<h1 class="h2"><?= e(__('auth.login.title')) ?></h1>
<p class="muted"><?= e(__('auth.login.lead')) ?></p>
<form method="post" action="<?= e(route('login.submit')) ?>" class="form" novalidate>
  <?= csrf_field() ?>
  <?= partial('partials/field', ['name' => 'email', 'type' => 'email', 'label' => __('field.email'), 'value' => old('email'), 'required' => true, 'autocomplete' => 'username']) ?>
  <?= partial('partials/field', ['name' => 'password', 'type' => 'password', 'label' => __('field.password'), 'required' => true, 'autocomplete' => 'current-password']) ?>
  <p class="form-inline-link"><a href="<?= e(route('password.request')) ?>"><?= e(__('auth.forgot_link')) ?></a></p>
  <button class="btn btn--primary btn--block" type="submit"><?= e(__('auth.login.submit')) ?></button>
</form>
<p class="center muted"><?= e(__('auth.no_account')) ?> <a href="<?= e(route('register')) ?>"><?= e(__('auth.register.title')) ?></a></p>
