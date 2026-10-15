<p><?= e(__('email.greeting', ['name' => $name])) ?></p>
<p><?= e(__('email.ticket_reply.body', ['reference' => $reference])) ?></p>
<blockquote style="border-left:3px solid #0284c7;margin:16px 0;padding:8px 16px;color:#334155;white-space:pre-wrap;"><?= e($reply) ?></blockquote>
