<header class="admin-header"><h1 class="h2"><?= e(__('admin.nav.settings')) ?></h1></header>
<form class="card form" method="post" action="<?= e(route('admin.settings.update')) ?>"><?= csrf_field() ?>
  <label class="checkbox"><input type="checkbox" name="registration_open" value="1" <?= $values['registration_open'] === '1' ? 'checked' : '' ?>> <span><?= e(__('admin.set.registration_open')) ?></span></label>
  <label class="checkbox"><input type="checkbox" name="public_stats_enabled" value="1" <?= $values['public_stats_enabled'] === '1' ? 'checked' : '' ?>> <span><?= e(__('admin.set.public_stats')) ?></span></label>
  <div class="form-row">
    <?= partial('partials/field', ['name' => 'lesson_min_seconds', 'type' => 'number', 'label' => __('admin.set.lesson_min_seconds'), 'value' => $values['lesson_min_seconds'], 'min' => 0, 'hint' => __('admin.set.lesson_min_hint')]) ?>
    <?= partial('partials/field', ['name' => 'contact_email', 'type' => 'email', 'label' => __('admin.set.contact_email'), 'value' => $values['contact_email'], 'required' => true]) ?>
  </div>
  <?= partial('partials/field', ['name' => 'certificate_signatory', 'label' => __('admin.set.signatory'), 'value' => $values['certificate_signatory'], 'required' => true, 'hint' => __('admin.set.signatory_hint')]) ?>
  <?= partial('partials/field', ['name' => 'maintenance_banner', 'label' => __('admin.set.banner'), 'value' => $values['maintenance_banner']]) ?>
  <div class="form-row form-row--3">
    <?php foreach (['social_linkedin', 'social_facebook', 'social_tiktok'] as $s): ?><?= partial('partials/field', ['name' => $s, 'type' => 'url', 'label' => __('admin.set.' . $s), 'value' => $values[$s]]) ?><?php endforeach; ?>
  </div>
  <button class="btn btn--primary" type="submit"><?= e(__('common.save')) ?></button>
</form>
<section class="card"><h2 class="h4"><?= e(__('admin.environment')) ?></h2><p class="form-hint"><?= e(__('admin.environment_hint')) ?></p>
  <dl class="kv"><?php foreach ($environment as $k => $val): ?><div><dt><?= e($k) ?></dt><dd><code><?= e($val) ?></code></dd></div><?php endforeach; ?></dl></section>
