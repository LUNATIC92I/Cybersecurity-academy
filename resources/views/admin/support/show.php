<header class="admin-header"><div><a class="link-muted" href="<?= e(route('admin.support')) ?>">← <?= e(__('admin.nav.support')) ?></a><h1 class="h2"><?= e($ticket['subject']) ?></h1>
  <p class="muted"><code><?= e($ticket['reference']) ?></code> · <?= e($ticket['name']) ?> &lt;<?= e($ticket['email']) ?>&gt; · <?= e(__('contact.category.' . $ticket['category'])) ?> · <?= e(strtoupper($ticket['locale'])) ?></p></div></header>
<article class="card"><p class="pre-wrap"><?= e($ticket['message']) ?></p><small class="muted"><?= e(format_datetime($ticket['created_at'])) ?></small></article>
<?php foreach ($replies as $r): ?><article class="card <?= (int) $r['is_staff'] ? 'card--staff' : '' ?>"><p class="pre-wrap"><?= e($r['message']) ?></p><small class="muted"><?= e($r['display_name'] ?? '') ?> · <?= e(format_datetime($r['created_at'])) ?></small></article><?php endforeach; ?>
<form class="card form" method="post" action="<?= e(route('admin.support.reply', ['id' => $ticket['id']])) ?>"><?= csrf_field() ?>
  <?= partial('partials/field', ['name' => 'message', 'type' => 'textarea', 'rows' => 5, 'label' => __('admin.reply'), 'hint' => __('admin.reply_hint')]) ?>
  <div class="form-group"><label class="form-label" for="tstatus"><?= e(__('admin.status')) ?></label><select class="input" id="tstatus" name="status"><?php foreach (['open', 'pending', 'closed'] as $s): ?><option value="<?= $s ?>" <?= $ticket['status'] === $s ? 'selected' : '' ?>><?= e(__('admin.ticket_status.' . $s)) ?></option><?php endforeach; ?></select></div>
  <button class="btn btn--primary btn--sm" type="submit"><?= e(__('common.save')) ?></button>
</form>
