<p><?= e(__('email.greeting', ['name' => $name])) ?></p>
<p><?= e(__('email.password_reset.body', ['minutes' => $minutes])) ?></p>
<p style="margin:24px 0;"><a href="<?= e($url) ?>" style="background:#0284c7;color:#ffffff;padding:12px 20px;border-radius:6px;text-decoration:none;display:inline-block;"><?= e(__('email.password_reset.button')) ?></a></p>
<p style="font-size:13px;color:#475569;"><?= e(__('email.link_fallback')) ?><br><?= e($url) ?></p>
<p><?= e(__('email.password_reset.ignore')) ?></p>
