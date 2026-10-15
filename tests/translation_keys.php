<?php

/**
 * Returns every interface translation key the application can request:
 * keys written literally in code, plus keys built dynamically from known
 * value sets (statuses, types, categories...). Used by the translation
 * completeness test and `php bin/console translations:check`.
 */

$root = dirname(__DIR__);
$keys = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/app'));
$files = iterator_to_array($iterator);
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/resources')) as $f) {
    $files[] = $f;
}
foreach ($files as $file) {
    if (!str_ends_with((string) $file, '.php')) {
        continue;
    }
    preg_match_all("/(?:__|trans_choice|\\\$L)\(\s*'([a-zA-Z0-9_.]+)'\s*[,)]/", (string) file_get_contents((string) $file), $m);
    foreach ($m[1] as $k) {
        if (!str_ends_with($k, '.')) {
            $keys[$k] = true;
        }
    }
}

$expand = static function (string $prefix, array $values, string $suffix = '') use (&$keys): void {
    foreach ($values as $v) {
        $keys[$prefix . $v . $suffix] = true;
    }
};
$expand('status.', ['draft', 'in_review', 'published', 'archived', 'missing']);
$expand('level.', ['beginner', 'intermediate', 'advanced', 'all']);
$expand('role.', ['super_admin', 'course_admin', 'instructor', 'student']);
$expand('error.', [400, 403, 404, 405, 419, 429, 500], '.title');
$expand('error.', [400, 403, 404, 405, 419, 429, 500], '.text');
$expand('activity.', ['enrolled', 'lesson_completed', 'lab_passed', 'lab_attempted', 'quiz_started', 'quiz_passed', 'quiz_submitted', 'certificate_issued']);
$expand('notification.', ['generic', 'achievement_earned', 'certificate_issued', 'certificate_revoked', 'order_paid']);
$expand('content.block.', ['solution', 'hint', 'note', 'tip', 'warning', 'scenario', 'check', 'diagram']);
$expand('course.access.', ['free', 'premium']);
$expand('course.access_via.', ['free', 'subscription', 'purchase', 'grant']);
$expand('path.', ['soc', 'pen', 'grc']);
$expand('stats.', ['paths', 'courses', 'modules', 'lessons', 'labs', 'questions', 'learners', 'certificates', 'lessons_completed', 'labs_passed']);
$expand('home.method.', ['learn', 'practise', 'assess', 'certify'], '.title');
$expand('home.method.', ['learn', 'practise', 'assess', 'certify'], '.text');
$expand('methodology.', ['learn', 'practise', 'assess', 'certify', 'share'], '.title');
$expand('methodology.', ['learn', 'practise', 'assess', 'certify', 'share'], '.text');
for ($i = 1; $i <= 10; $i++) {
    $keys["faq.q$i"] = $keys["faq.a$i"] = true;
}
$expand('pricing.per.', ['month', 'year', 'none']);
$expand('certs.feature.', ['number', 'qr', 'revocation'], '.title');
$expand('certs.feature.', ['number', 'qr', 'revocation'], '.text');
$expand('contact.category.', ['general', 'technical', 'billing', 'enterprise', 'certificate', 'security']);
$expand('legal.', ['privacy', 'terms', 'cookies', 'responsible'], '.title');
$expand('legal.', ['privacy', 'terms', 'cookies', 'responsible'], '.description');
$expand('labs.type.', ['simulation', 'download', 'virtual']);
$expand('labs.type_desc.', ['simulation', 'download', 'virtual']);
$expand('labs.section.', ['objectives', 'tools', 'prerequisites', 'scenario', 'setup', 'instructions', 'expected', 'scoring', 'solution', 'cleanup', 'lessons_learned']);
$expand('exams.feature.', ['random', 'timed', 'server', 'review'], '.title');
$expand('exams.feature.', ['random', 'timed', 'server', 'review'], '.text');
$expand('exams.qtype.', ['single', 'multiple', 'true_false', 'scenario', 'matching', 'numeric', 'text']);
$expand('exams.kind.', ['knowledge_check', 'module_quiz', 'final_exam']);
$expand('exams.explanations.', ['never', 'after_submit', 'after_pass']);
$expand('exams.answers_hidden.', ['never', 'after_submit', 'after_pass']);
$expand('exams.status.', ['in_progress', 'submitted', 'expired']);
$expand('exams.cannot_start.', ['unpublished', 'no_access', 'not_enrolled', 'max_attempts', 'cooldown', 'already_passed', 'no_questions']);
$expand('verify.state.', ['valid', 'revoked', 'invalid']);
$expand('checkout.error.', ['invalid_item', 'invalid_coupon', 'provider']);
$expand('checkout.order_status.', ['pending', 'paid', 'failed', 'canceled', 'refunded']);
$expand('checkout.return.', ['paid', 'pending']);
$expand('checkout.return_text.', ['paid', 'pending']);
$expand('dashboard.grant_source.', ['purchase', 'admin', 'enterprise']);
$expand('email.', ['password_reset', 'welcome', 'certificate_issued', 'ticket_received', 'ticket_reply', 'account_deletion'], '.subject');
$expand('admin.stat.', ['users_total', 'users_30d', 'enrollments', 'attempts_7d', 'certificates', 'active_subscriptions', 'open_tickets', 'fr_lessons_pending']);
$expand('admin.ann_level.', ['info', 'success', 'warning']);
$expand('admin.certs.event.', ['issued', 'revoked', 'reinstated', 'downloaded']);
$expand('admin.diff.', ['easy', 'medium', 'hard']);
$expand('admin.risk.', ['standard', 'elevated']);
$expand('admin.set.', ['social_linkedin', 'social_facebook', 'social_tiktok']);
$expand('admin.task.', ['text', 'number', 'choice', 'multi', 'query']);
$expand('admin.ticket_status.', ['open', 'pending', 'closed', 'all']);
$expand('admin.tr.', ['courses', 'modules', 'lessons', 'labs', 'questions']);
$expand('admin.user_status.', ['active', 'suspended', 'deleted']);
$expand('admin.upload.', ['failed', 'too.large', 'type', 'missing']);
$expand('admin.', ['signups_30d', 'enrolments_30d']);

// Field labels used by validation rules in controllers.
foreach ($files as $file) {
    if (!str_ends_with((string) $file, '.php') || !str_contains((string) $file, '/Controllers/')) {
        continue;
    }
    if (preg_match_all("/validate\(\\\$request,\s*\[(.*?)\]\);/s", (string) file_get_contents((string) $file), $blocks)) {
        foreach ($blocks[1] as $block) {
            preg_match_all("/'([a-z_]+)'\s*=>/", $block, $fields);
            foreach ($fields[1] as $field) {
                $keys['field.' . $field] = true;
            }
        }
    }
}

ksort($keys);
return array_keys($keys);
