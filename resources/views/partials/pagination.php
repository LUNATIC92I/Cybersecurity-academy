<?php if (($result['pages'] ?? 1) > 1):
    $query = app('request')->queryString(); ?>
<nav class="pagination" aria-label="<?= e(__('common.pagination')) ?>">
  <?php for ($p = 1; $p <= $result['pages']; $p++): $query['page'] = $p; ?>
    <a href="?<?= e(http_build_query($query)) ?>" class="<?= $p === $result['page'] ? 'is-active' : '' ?>" <?= $p === $result['page'] ? 'aria-current="page"' : '' ?>><?= $p ?></a>
  <?php endfor; ?>
</nav>
<?php endif; ?>
