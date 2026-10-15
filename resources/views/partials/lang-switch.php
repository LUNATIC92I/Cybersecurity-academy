<div class="lang-switch" role="group" aria-label="<?= e(__('nav.language')) ?>">
  <a href="<?= e(current_path_with_lang('en')) ?>" hreflang="en" lang="en" class="<?= locale() === 'en' ? 'is-active' : '' ?>" <?= locale() === 'en' ? 'aria-current="true"' : '' ?>>EN</a>
  <a href="<?= e(current_path_with_lang('fr')) ?>" hreflang="fr" lang="fr" class="<?= locale() === 'fr' ? 'is-active' : '' ?>" <?= locale() === 'fr' ? 'aria-current="true"' : '' ?>>FR</a>
</div>
