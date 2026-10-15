<?php

declare(strict_types=1);

namespace Database\Seeders;

final class RoleSeeder
{
    public const PERMISSIONS = [
        'admin.access' => 'Access the administration area',
        'users.view' => 'View learner accounts',
        'users.manage' => 'Suspend accounts and grant access',
        'roles.manage' => 'Change user roles',
        'content.manage' => 'Create and edit courses, modules and lessons',
        'content.publish' => 'Publish or archive content',
        'content.preview' => 'Preview unpublished content and bypass enrolment',
        'translations.manage' => 'Review and approve translations',
        'labs.manage' => 'Create and configure labs',
        'questions.manage' => 'Manage question banks',
        'exams.manage' => 'Configure quizzes and examinations',
        'progress.view' => 'Review learner progress',
        'certificates.manage' => 'Issue, revoke and reinstate certificates',
        'commerce.manage' => 'Manage plans, coupons and orders',
        'announcements.manage' => 'Publish announcements',
        'support.manage' => 'Handle support requests',
        'audit.view' => 'Review the security audit log',
        'reports.export' => 'Export reports',
        'settings.manage' => 'Change platform settings',
    ];

    public const ROLES = [
        'super_admin' => ['Super Administrator', ['*']],
        'course_admin' => ['Course Administrator', [
            'admin.access', 'users.view', 'content.manage', 'content.publish', 'content.preview', 'translations.manage',
            'labs.manage', 'questions.manage', 'exams.manage', 'progress.view', 'certificates.manage',
            'announcements.manage', 'support.manage', 'reports.export',
        ]],
        'instructor' => ['Instructor', [
            'admin.access', 'content.manage', 'content.preview', 'translations.manage', 'labs.manage',
            'questions.manage', 'progress.view', 'support.manage',
        ]],
        'student' => ['Student', []],
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $code => $description) {
            db()->query('INSERT INTO permissions (code, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE description = VALUES(description)', [$code, $description]);
        }
        foreach (self::ROLES as $code => [$name, $permissions]) {
            db()->query('INSERT INTO roles (code, name) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)', [$code, $name]);
            $roleId = (int) db()->value('SELECT id FROM roles WHERE code = ?', [$code]);
            db()->delete('role_permissions', ['role_id' => $roleId]);
            $codes = $permissions === ['*'] ? array_keys(self::PERMISSIONS) : $permissions;
            foreach ($codes as $permission) {
                $pid = (int) db()->value('SELECT id FROM permissions WHERE code = ?', [$permission]);
                db()->query('INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)', [$roleId, $pid]);
            }
        }
    }
}
