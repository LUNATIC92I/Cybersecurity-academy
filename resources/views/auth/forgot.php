<h1 class="h2"><?= e(__('auth.forgot.title')) ?></h1>
<p class="muted"><?= e(__('auth.forgot.lead')) ?></p>
<form method="post" action="<?= e(route('password.email')) ?>" class="form" novalidate>
  <?= csrf_field() ?>
  <?= partial('partials/field', ['name' => 'email', 'type' => 'email', 'label' => __('field.email'), 'value' => old('email'), 'required' => true, 'autocomplete' => 'email']) ?>
  <button class="btn btn--primary btn--block" type="submit"><?= e(__('auth.forgot.submit')) ?></button>
</form>
<p class="center muted"><a href="<?= e(route('login')) ?>">← <?= e(__('auth.back_to_login')) ?></a></p>
