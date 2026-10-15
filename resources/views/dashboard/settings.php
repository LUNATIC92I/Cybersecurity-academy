<header class="dash-header"><h1 class="h2"><?= e(__('dashboard.settings')) ?></h1></header>
<form class="card form" method="post" action="<?= e(route('settings.profile')) ?>" id="profile">
  <?= csrf_field() ?>
  <h2 class="h4"><?= e(__('settings.profile')) ?></h2>
  <?= partial('partials/field', ['name' => 'display_name', 'label' => __('field.display_name'), 'value' => old('display_name', $user['display_name']), 'required' => true, 'hint' => __('settings.display_name_hint')]) ?>
  <?= partial('partials/field', ['name' => 'headline', 'label' => __('field.headline'), 'value' => old('headline', $user['headline'] ?? ''), 'hint' => __('settings.headline_hint')]) ?>
  <div class="form-row">
    <div class="form-group"><label class="form-label" for="locale"><?= e(__('settings.interface_language')) ?></label>
      <select class="input" id="locale" name="locale"><option value="en" <?= $user['locale'] === 'en' ? 'selected' : '' ?>>English</option><option value="fr" <?= $user['locale'] === 'fr' ? 'selected' : '' ?>>Français</option></select></div>
    <div class="form-group"><label class="form-label" for="content_locale"><?= e(__('settings.content_language')) ?></label>
      <select class="input" id="content_locale" name="content_locale" aria-describedby="cl-hint"><option value="auto" <?= $user['content_locale'] === null ? 'selected' : '' ?>><?= e(__('settings.same_as_interface')) ?></option><option value="en" <?= $user['content_locale'] === 'en' ? 'selected' : '' ?>>English</option><option value="fr" <?= $user['content_locale'] === 'fr' ? 'selected' : '' ?>>Français</option></select>
      <p class="form-hint" id="cl-hint"><?= e(__('settings.content_language_hint')) ?></p></div>
  </div>
  <button class="btn btn--primary" type="submit"><?= e(__('common.save')) ?></button>
</form>

<form class="card form" method="post" action="<?= e(route('settings.password')) ?>" id="security">
  <?= csrf_field() ?>
  <h2 class="h4"><?= e(__('settings.security')) ?></h2>
  <?= partial('partials/field', ['name' => 'current_password', 'type' => 'password', 'label' => __('field.current_password'), 'required' => true, 'autocomplete' => 'current-password']) ?>
  <div class="form-row">
    <?= partial('partials/field', ['name' => 'password', 'type' => 'password', 'label' => __('field.new_password'), 'required' => true, 'autocomplete' => 'new-password', 'hint' => __('validation.password')]) ?>
    <?= partial('partials/field', ['name' => 'password_confirmation', 'type' => 'password', 'label' => __('field.password_confirmation'), 'required' => true, 'autocomplete' => 'new-password']) ?>
  </div>
  <p class="form-hint"><?= e(__('settings.password_sessions')) ?></p>
  <button class="btn btn--primary" type="submit"><?= e(__('settings.change_password')) ?></button>
</form>

<form class="card form" method="post" action="<?= e(route('settings.privacy')) ?>" id="privacy">
  <?= csrf_field() ?>
  <h2 class="h4"><?= e(__('settings.privacy')) ?></h2>
  <label class="checkbox"><input type="checkbox" name="public_profile" value="1" <?= (int) $user['public_profile'] ? 'checked' : '' ?>> <span><?= e(__('settings.public_profile')) ?></span></label>
  <label class="checkbox"><input type="checkbox" name="show_progress_public" value="1" <?= (int) $user['show_progress_public'] ? 'checked' : '' ?>> <span><?= e(__('settings.show_progress')) ?></span></label>
  <?php if ((int) $user['public_profile'] && $user['public_slug']): ?><p class="form-hint"><?= e(__('settings.profile_url')) ?> <a href="<?= e(route('share.profile', ['slug' => $user['public_slug']])) ?>"><?= e(absolute_route('share.profile', ['slug' => $user['public_slug']])) ?></a></p><?php endif; ?>
  <p class="form-hint"><?= e(__('settings.privacy_hint')) ?></p>
  <button class="btn btn--primary" type="submit"><?= e(__('common.save')) ?></button>
</form>

<section class="card">
  <h2 class="h4"><?= e(__('settings.data')) ?></h2>
  <p class="muted"><?= e(__('settings.export_text')) ?></p>
  <a class="btn btn--outline" href="<?= e(route('settings.export')) ?>"><?= icon('download', 'icon icon--sm') ?> <?= e(__('settings.export')) ?></a>
</section>

<form class="card form card--danger" method="post" action="<?= e(route('settings.delete')) ?>" id="delete">
  <?= csrf_field() ?>
  <h2 class="h4"><?= e(__('settings.delete_title')) ?></h2>
  <p><?= e(__('settings.delete_text', ['days' => (int) config('security.account_deletion_grace_days', 14)])) ?></p>
  <?php if ($err = error_for('delete')): ?><p class="form-error"><?= e($err) ?></p><?php endif; ?>
  <div class="form-row">
    <?= partial('partials/field', ['name' => 'password', 'id' => 'delete_password', 'type' => 'password', 'label' => __('field.current_password'), 'required' => true, 'autocomplete' => 'current-password', 'errorKey' => 'delete_password']) ?>
    <?= partial('partials/field', ['name' => 'confirm', 'label' => __('settings.type_delete'), 'required' => true, 'placeholder' => 'DELETE']) ?>
  </div>
  <button class="btn btn--danger" type="submit" data-confirm="<?= e(__('settings.delete_confirm')) ?>"><?= e(__('settings.delete_button')) ?></button>
</form>
