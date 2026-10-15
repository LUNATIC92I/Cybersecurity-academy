<?php
$sections = [
    ['admin.dashboard', 'chart', 'admin.nav.dashboard', 'admin.access'],
    ['admin.users', 'users', 'admin.nav.users', 'users.view'],
    ['admin.courses', 'book', 'admin.nav.courses', 'content.manage'],
    ['admin.translations', 'globe', 'admin.nav.translations', 'translations.manage'],
    ['admin.labs', 'flask', 'admin.nav.labs', 'labs.manage'],
    ['admin.questions', 'help', 'admin.nav.questions', 'questions.manage'],
    ['admin.quizzes', 'clipboard', 'admin.nav.quizzes', 'exams.manage'],
    ['admin.progress', 'route', 'admin.nav.progress', 'progress.view'],
    ['admin.certificates', 'award', 'admin.nav.certificates', 'certificates.manage'],
    ['admin.commerce', 'building', 'admin.nav.commerce', 'commerce.manage'],
    ['admin.announcements', 'bell', 'admin.nav.announcements', 'announcements.manage'],
    ['admin.support', 'mail', 'admin.nav.support', 'support.manage'],
    ['admin.audit', 'shield', 'admin.nav.audit', 'audit.view'],
    ['admin.settings', 'settings', 'admin.nav.settings', 'settings.manage'],
];
?>
<!doctype html>
<html lang="<?= e(locale()) ?>" data-theme="dark">
<head>
<?= partial('partials/head', ['meta' => $meta ?? []]) ?>
</head>
<body class="admin-body">
<a class="skip-link" href="#main"><?= e(__('nav.skip')) ?></a>
<div class="admin-shell">
  <aside class="admin-sidebar" aria-label="<?= e(__('admin.navigation')) ?>">
    <a class="brand" href="<?= e(route('admin.dashboard')) ?>"><span class="brand__mark"><?= icon('shield-check') ?></span><span class="brand__name">CA <span>Admin</span></span></a>
    <nav>
      <?php foreach ($sections as [$name, $ic, $label, $perm]): if (!auth()->can($perm)) { continue; }
          $path = route($name); $active = $name === 'admin.dashboard' ? app('request')->path === $path : str_starts_with(app('request')->path, $path); ?>
        <a href="<?= e($path) ?>" class="admin-sidebar__link <?= $active ? 'is-active' : '' ?>" <?= $active ? 'aria-current="page"' : '' ?>><?= icon($ic) ?><span><?= e(__($label)) ?></span></a>
      <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar__foot">
      <a href="<?= e(route('home')) ?>" class="admin-sidebar__link"><?= icon('home') ?><span><?= e(__('admin.nav.site')) ?></span></a>
      <form method="post" action="<?= e(route('logout')) ?>"><?= csrf_field() ?><button type="submit" class="admin-sidebar__link admin-sidebar__link--button"><?= icon('logout') ?><span><?= e(__('nav.logout')) ?></span></button></form>
    </div>
  </aside>
  <div class="admin-main">
    <header class="admin-topbar">
      <span class="muted"><?= e(auth()->user()['display_name']) ?> · <?= e(implode(', ', array_map(fn ($r) => __('role.' . $r), auth()->roles()))) ?></span>
      <div class="admin-topbar__actions"><?= partial('partials/lang-switch') ?>
        <button type="button" class="icon-btn" data-theme-toggle aria-label="<?= e(__('nav.toggle_theme')) ?>"><?= icon('sun', 'icon theme-icon-light') ?><?= icon('moon', 'icon theme-icon-dark') ?></button></div>
    </header>
    <main id="main" class="admin-content" tabindex="-1">
      <?= partial('partials/flash') ?>
      <?= $content ?>
    </main>
  </div>
</div>
<div class="toast" role="status" aria-live="polite" data-toast hidden></div>
</body>
</html>
