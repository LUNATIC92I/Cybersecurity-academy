<p><?= e(__('email.greeting', ['name' => $name])) ?></p>
<p><?= e(__('email.certificate_issued.body', ['title' => $title, 'number' => $number])) ?></p>
<p style="margin:24px 0;"><a href="<?= e($url) ?>" style="background:#0284c7;color:#ffffff;padding:12px 20px;border-radius:6px;text-decoration:none;display:inline-block;"><?= e(__('email.certificate_issued.button')) ?></a></p>
<p style="font-size:13px;color:#475569;"><?= e(__('footer.certificate_notice')) ?></p>
