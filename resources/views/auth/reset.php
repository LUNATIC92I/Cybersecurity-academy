<h1 class="h2"><?= e(__('auth.reset.title')) ?></h1>
<?php if (!$valid): ?>
  <div class="notice notice--warning"><?= icon('alert') ?> <span><?= e(__('auth.reset_invalid')) ?></span></div>
  <a class="btn btn--primary btn--block" href="<?= e(route('password.request')) ?>"><?= e(__('auth.forgot.submit')) ?></a>
<?php else: ?>
  <p class="muted"><?= e(__('auth.reset.lead')) ?></p>
  <form method="post" action="<?= e(route('password.update')) ?>" class="form" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <?= partial('partials/field', ['name' => 'password', 'type' => 'password', 'label' => __('field.new_password'), 'required' => true, 'autocomplete' => 'new-password', 'hint' => __('validation.password')]) ?>
    <?= partial('partials/field', ['name' => 'password_confirmation', 'type' => 'password', 'label' => __('field.password_confirmation'), 'required' => true, 'autocomplete' => 'new-password']) ?>
    <button class="btn btn--primary btn--block" type="submit"><?= e(__('auth.reset.submit')) ?></button>
  </form>
<?php endif; ?>
