<?php
$meta = $meta ?? [];
$title = isset($meta['title']) && $meta['title'] !== '' ? $meta['title'] . ' | Cybersecurity-Academy' : 'Cybersecurity-Academy';
$description = $meta['description'] ?? __('meta.default_description');
$request = app('request');
$canonical = $meta['canonical'] ?? url($request->path);
$ogImage = $meta['og_image'] ?? url('/assets/images/og-default.png');
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e(mb_substr($description, 0, 300)) ?>">
<?php if (!empty($meta['robots'])): ?><meta name="robots" content="<?= e($meta['robots']) ?>"><?php endif; ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<link rel="alternate" hreflang="en" href="<?= e(url(current_path_with_lang('en'))) ?>">
<link rel="alternate" hreflang="fr" href="<?= e(url(current_path_with_lang('fr'))) ?>">
<link rel="alternate" hreflang="x-default" href="<?= e(url($request->path)) ?>">
<meta property="og:site_name" content="Cybersecurity-Academy">
<meta property="og:title" content="<?= e($meta['title'] ?? 'Cybersecurity-Academy') ?>">
<meta property="og:description" content="<?= e(mb_substr($description, 0, 300)) ?>">
<meta property="og:type" content="<?= e($meta['og_type'] ?? 'website') ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:locale" content="<?= locale() === 'fr' ? 'fr_FR' : 'en_US' ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#070c1c">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="icon" href="/assets/images/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
<?php if (!empty($meta['structured'])): ?>
<script type="application/ld+json"><?= json_encode($meta['structured'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php endif; ?>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
