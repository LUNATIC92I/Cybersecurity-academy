<?php

use App\Core\Env;

return [
    'login_max_attempts' => (int) Env::get('LOGIN_MAX_ATTEMPTS', 5),
    'login_decay_seconds' => (int) Env::get('LOGIN_DECAY_SECONDS', 900),
    'password_reset_ttl_minutes' => 60,
    'upload_max_bytes' => (int) Env::get('UPLOAD_MAX_BYTES', 10 * 1024 * 1024),
    'upload_allowed' => [
        'pdf' => ['application/pdf'],
        'csv' => ['text/csv', 'text/plain', 'application/csv'],
        'txt' => ['text/plain'],
        'json' => ['application/json', 'text/plain'],
        'log' => ['text/plain'],
        'md' => ['text/plain', 'text/markdown'],
        'png' => ['image/png'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'pcap' => ['application/vnd.tcpdump.pcap', 'application/octet-stream'],
        'zip' => ['application/zip'],
    ],
    'account_deletion_grace_days' => 14,
];
