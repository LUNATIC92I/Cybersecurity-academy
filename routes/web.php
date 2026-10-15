<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CatalogController;
use App\Controllers\CertificateController;
use App\Controllers\CheckoutController;
use App\Controllers\DashboardController;
use App\Controllers\ExamController;
use App\Controllers\LabController;
use App\Controllers\LessonController;
use App\Controllers\PageController;
use App\Controllers\ShareController;
use App\Controllers\SupportController;
use App\Controllers\Admin;

/** @var \App\Core\Router $router */

// ---------------------------------------------------------------- Public
$router->get('/', [PageController::class, 'home'], 'home');
$router->get('/about', [PageController::class, 'about'], 'about');
$router->get('/methodology', [PageController::class, 'methodology'], 'methodology');
$router->get('/pricing', [PageController::class, 'pricing'], 'pricing');
$router->get('/faq', [PageController::class, 'faq'], 'faq');
$router->get('/student-success', [PageController::class, 'success'], 'success');
$router->get('/certifications', [PageController::class, 'certifications'], 'certifications');
$router->get('/privacy', [PageController::class, 'legal'], 'legal.privacy');
$router->get('/terms', [PageController::class, 'legal'], 'legal.terms');
$router->get('/cookies', [PageController::class, 'legal'], 'legal.cookies');
$router->get('/responsible-use', [PageController::class, 'legal'], 'legal.responsible');
$router->get('/sitemap.xml', [PageController::class, 'sitemap'], 'sitemap');
$router->get('/robots.txt', [PageController::class, 'robots'], 'robots');

$router->get('/catalog', [CatalogController::class, 'index'], 'catalog');
$router->get('/paths/{slug}', [CatalogController::class, 'path'], 'paths.show');
$router->get('/courses/{slug}', [CatalogController::class, 'course'], 'courses.show');

$router->get('/labs', [LabController::class, 'index'], 'labs.index');
$router->get('/labs/{slug}', [LabController::class, 'show'], 'labs.show');
$router->get('/assessments', [ExamController::class, 'index'], 'assessments.index');

$router->get('/verify', [CertificateController::class, 'verifyForm'], 'verify.form');
$router->get('/verify/{number}', [CertificateController::class, 'verify'], 'verify.show');
$router->get('/verify/{number}/qr.png', [CertificateController::class, 'qr'], 'verify.qr');

$router->get('/achievements/{token:[a-f0-9]{32}}', [ShareController::class, 'certificate'], 'share.certificate');
$router->get('/achievements/{token:[a-f0-9]{32}}/card.png', [ShareController::class, 'certificateImage'], 'share.certificate.image');
$router->get('/badges/{token:[a-f0-9]{32}}', [ShareController::class, 'badge'], 'share.badge');
$router->get('/badges/{token:[a-f0-9]{32}}/card.png', [ShareController::class, 'badgeImage'], 'share.badge.image');
$router->get('/learners/{slug}', [ShareController::class, 'profile'], 'share.profile');

$router->get('/contact', [SupportController::class, 'contact'], 'contact');
$router->post('/contact', [SupportController::class, 'submit'], 'contact.submit');

$router->post('/webhooks/stripe', [CheckoutController::class, 'stripeWebhook'], 'webhooks.stripe');

// ---------------------------------------------------------------- Guests
$router->group(['middleware' => ['guest']], function ($router) {
    $router->get('/login', [AuthController::class, 'showLogin'], 'login');
    $router->post('/login', [AuthController::class, 'login'], 'login.submit');
    $router->get('/register', [AuthController::class, 'showRegister'], 'register');
    $router->post('/register', [AuthController::class, 'register'], 'register.submit');
    $router->get('/forgot-password', [AuthController::class, 'showForgot'], 'password.request');
    $router->post('/forgot-password', [AuthController::class, 'sendReset'], 'password.email');
    $router->get('/reset-password/{token}', [AuthController::class, 'showReset'], 'password.reset');
    $router->post('/reset-password', [AuthController::class, 'reset'], 'password.update');
});

// ---------------------------------------------------------------- Learners
$router->group(['middleware' => ['auth']], function ($router) {
    $router->post('/logout', [AuthController::class, 'logout'], 'logout');

    $router->post('/courses/{slug}/enroll', [CatalogController::class, 'enroll'], 'courses.enroll');
    $router->get('/learn/{course}/{module}/{lesson}', [LessonController::class, 'show'], 'lessons.show');
    $router->post('/lessons/{id:\d+}/complete', [LessonController::class, 'complete'], 'lessons.complete');
    $router->post('/lessons/{id:\d+}/heartbeat', [LessonController::class, 'heartbeat'], 'lessons.heartbeat');
    $router->get('/resources/{id:\d+}/download', [LessonController::class, 'download'], 'resources.download');

    $router->post('/labs/{slug}/acknowledge', [LabController::class, 'acknowledge'], 'labs.acknowledge');
    $router->post('/labs/{slug}/submit', [LabController::class, 'submit'], 'labs.submit');
    $router->get('/labs/{slug}/dataset', [LabController::class, 'dataset'], 'labs.dataset');
    $router->get('/labs/{slug}/dataset.json', [LabController::class, 'datasetJson'], 'labs.dataset.json');
    $router->get('/labs/{slug}/attempts/{id:\d+}', [LabController::class, 'attempt'], 'labs.attempt');

    $router->get('/assessments/{slug}', [ExamController::class, 'show'], 'assessments.show');
    $router->post('/assessments/{slug}/start', [ExamController::class, 'start'], 'assessments.start');
    $router->get('/attempts/{id:\d+}', [ExamController::class, 'take'], 'attempts.take');
    $router->post('/attempts/{id:\d+}/submit', [ExamController::class, 'submit'], 'attempts.submit');
    $router->get('/attempts/{id:\d+}/results', [ExamController::class, 'results'], 'attempts.results');

    $router->get('/dashboard', [DashboardController::class, 'index'], 'dashboard');
    $router->get('/dashboard/courses', [DashboardController::class, 'courses'], 'dashboard.courses');
    $router->get('/dashboard/resources', [DashboardController::class, 'resources'], 'dashboard.resources');
    $router->get('/dashboard/certificates', [DashboardController::class, 'certificates'], 'dashboard.certificates');
    $router->get('/dashboard/certificates/{id:\d+}/pdf', [CertificateController::class, 'pdf'], 'certificates.pdf');
    $router->post('/dashboard/certificates/{id:\d+}/visibility', [CertificateController::class, 'visibility'], 'certificates.visibility');
    $router->get('/dashboard/achievements', [DashboardController::class, 'achievements'], 'dashboard.achievements');
    $router->post('/dashboard/achievements/{id:\d+}/visibility', [ShareController::class, 'badgeVisibility'], 'achievements.visibility');
    $router->post('/share/record', [ShareController::class, 'record'], 'share.record');
    $router->get('/dashboard/notifications', [DashboardController::class, 'notifications'], 'dashboard.notifications');
    $router->post('/dashboard/notifications/read', [DashboardController::class, 'markRead'], 'dashboard.notifications.read');
    $router->get('/dashboard/billing', [DashboardController::class, 'billing'], 'dashboard.billing');
    $router->get('/dashboard/settings', [DashboardController::class, 'settings'], 'dashboard.settings');
    $router->post('/dashboard/settings/profile', [DashboardController::class, 'updateProfile'], 'settings.profile');
    $router->post('/dashboard/settings/password', [DashboardController::class, 'updatePassword'], 'settings.password');
    $router->post('/dashboard/settings/privacy', [DashboardController::class, 'updatePrivacy'], 'settings.privacy');
    $router->post('/dashboard/settings/delete', [DashboardController::class, 'deleteAccount'], 'settings.delete');
    $router->get('/dashboard/settings/export', [DashboardController::class, 'exportData'], 'settings.export');

    $router->post('/checkout', [CheckoutController::class, 'create'], 'checkout.create');
    $router->get('/checkout/demo/{reference:[A-F0-9]{20}}', [CheckoutController::class, 'demo'], 'checkout.demo');
    $router->post('/checkout/demo/{reference:[A-F0-9]{20}}/complete', [CheckoutController::class, 'demoComplete'], 'checkout.demo.complete');
    $router->get('/checkout/return/{reference:[A-F0-9]{20}}', [CheckoutController::class, 'returned'], 'checkout.return');
});

// ---------------------------------------------------------------- Administration
$router->group(['prefix' => '/admin', 'middleware' => ['can:admin.access']], function ($router) {
    $router->get('/', [Admin\DashboardController::class, 'index'], 'admin.dashboard');

    $router->group(['middleware' => ['can:users.view']], function ($router) {
        $router->get('/users', [Admin\UserController::class, 'index'], 'admin.users');
        $router->get('/users/{id:\d+}', [Admin\UserController::class, 'show'], 'admin.users.show');
    });
    $router->group(['middleware' => ['can:users.manage']], function ($router) {
        $router->post('/users/{id:\d+}/roles', [Admin\UserController::class, 'roles'], 'admin.users.roles');
        $router->post('/users/{id:\d+}/status', [Admin\UserController::class, 'status'], 'admin.users.status');
        $router->post('/users/{id:\d+}/grants', [Admin\UserController::class, 'grant'], 'admin.users.grant');
        $router->post('/grants/{id:\d+}/revoke', [Admin\UserController::class, 'revokeGrant'], 'admin.grants.revoke');
    });

    $router->group(['middleware' => ['can:content.manage']], function ($router) {
        $router->get('/courses', [Admin\CourseController::class, 'index'], 'admin.courses');
        $router->get('/courses/create', [Admin\CourseController::class, 'create'], 'admin.courses.create');
        $router->post('/courses', [Admin\CourseController::class, 'store'], 'admin.courses.store');
        $router->get('/courses/{id:\d+}/edit', [Admin\CourseController::class, 'edit'], 'admin.courses.edit');
        $router->post('/courses/{id:\d+}', [Admin\CourseController::class, 'update'], 'admin.courses.update');
        $router->post('/courses/{id:\d+}/modules', [Admin\CourseController::class, 'storeModule'], 'admin.modules.store');
        $router->get('/modules/{id:\d+}/edit', [Admin\CourseController::class, 'editModule'], 'admin.modules.edit');
        $router->post('/modules/{id:\d+}', [Admin\CourseController::class, 'updateModule'], 'admin.modules.update');
        $router->get('/modules/{id:\d+}/lessons/create', [Admin\LessonController::class, 'create'], 'admin.lessons.create');
        $router->post('/modules/{id:\d+}/lessons', [Admin\LessonController::class, 'store'], 'admin.lessons.store');
        $router->get('/lessons/{id:\d+}/edit', [Admin\LessonController::class, 'edit'], 'admin.lessons.edit');
        $router->post('/lessons/{id:\d+}', [Admin\LessonController::class, 'update'], 'admin.lessons.update');
        $router->post('/lessons/{id:\d+}/resources', [Admin\LessonController::class, 'upload'], 'admin.lessons.upload');
        $router->post('/resources/{id:\d+}/delete', [Admin\LessonController::class, 'deleteResource'], 'admin.resources.delete');
    });
    $router->group(['middleware' => ['can:translations.manage']], function ($router) {
        $router->get('/translations', [Admin\TranslationController::class, 'index'], 'admin.translations');
    });

    $router->group(['middleware' => ['can:labs.manage']], function ($router) {
        $router->get('/labs', [Admin\LabController::class, 'index'], 'admin.labs');
        $router->get('/labs/create', [Admin\LabController::class, 'create'], 'admin.labs.create');
        $router->post('/labs', [Admin\LabController::class, 'store'], 'admin.labs.store');
        $router->get('/labs/{id:\d+}/edit', [Admin\LabController::class, 'edit'], 'admin.labs.edit');
        $router->post('/labs/{id:\d+}', [Admin\LabController::class, 'update'], 'admin.labs.update');
        $router->post('/labs/{id:\d+}/tasks', [Admin\LabController::class, 'storeTask'], 'admin.labs.tasks.store');
        $router->post('/lab-tasks/{id:\d+}', [Admin\LabController::class, 'updateTask'], 'admin.labs.tasks.update');
        $router->post('/lab-tasks/{id:\d+}/delete', [Admin\LabController::class, 'deleteTask'], 'admin.labs.tasks.delete');
    });

    $router->group(['middleware' => ['can:questions.manage']], function ($router) {
        $router->get('/questions', [Admin\QuestionController::class, 'index'], 'admin.questions');
        $router->get('/questions/create', [Admin\QuestionController::class, 'create'], 'admin.questions.create');
        $router->post('/questions', [Admin\QuestionController::class, 'store'], 'admin.questions.store');
        $router->get('/questions/{id:\d+}/edit', [Admin\QuestionController::class, 'edit'], 'admin.questions.edit');
        $router->post('/questions/{id:\d+}', [Admin\QuestionController::class, 'update'], 'admin.questions.update');
        $router->post('/banks', [Admin\QuestionController::class, 'storeBank'], 'admin.banks.store');
    });

    $router->group(['middleware' => ['can:exams.manage']], function ($router) {
        $router->get('/quizzes', [Admin\QuizController::class, 'index'], 'admin.quizzes');
        $router->post('/quizzes', [Admin\QuizController::class, 'store'], 'admin.quizzes.store');
        $router->get('/quizzes/{id:\d+}/edit', [Admin\QuizController::class, 'edit'], 'admin.quizzes.edit');
        $router->post('/quizzes/{id:\d+}', [Admin\QuizController::class, 'update'], 'admin.quizzes.update');
    });

    $router->group(['middleware' => ['can:progress.view']], function ($router) {
        $router->get('/progress', [Admin\ReportController::class, 'progress'], 'admin.progress');
    });
    $router->group(['middleware' => ['can:reports.export']], function ($router) {
        $router->get('/exports/{type:users|progress|certificates|orders}.csv', [Admin\ReportController::class, 'export'], 'admin.export');
    });
    $router->group(['middleware' => ['can:audit.view']], function ($router) {
        $router->get('/audit', [Admin\ReportController::class, 'audit'], 'admin.audit');
    });

    $router->group(['middleware' => ['can:certificates.manage']], function ($router) {
        $router->get('/certificates', [Admin\CertificateController::class, 'index'], 'admin.certificates');
        $router->post('/certificates/issue', [Admin\CertificateController::class, 'issue'], 'admin.certificates.issue');
        $router->post('/certificates/{id:\d+}/revoke', [Admin\CertificateController::class, 'revoke'], 'admin.certificates.revoke');
        $router->post('/certificates/{id:\d+}/reinstate', [Admin\CertificateController::class, 'reinstate'], 'admin.certificates.reinstate');
    });

    $router->group(['middleware' => ['can:commerce.manage']], function ($router) {
        $router->get('/commerce', [Admin\CommerceController::class, 'index'], 'admin.commerce');
        $router->post('/coupons', [Admin\CommerceController::class, 'storeCoupon'], 'admin.coupons.store');
        $router->post('/coupons/{id:\d+}/toggle', [Admin\CommerceController::class, 'toggleCoupon'], 'admin.coupons.toggle');
        $router->post('/plans/{id:\d+}', [Admin\CommerceController::class, 'updatePlan'], 'admin.plans.update');
    });

    $router->group(['middleware' => ['can:announcements.manage']], function ($router) {
        $router->get('/announcements', [Admin\AnnouncementController::class, 'index'], 'admin.announcements');
        $router->post('/announcements', [Admin\AnnouncementController::class, 'store'], 'admin.announcements.store');
        $router->post('/announcements/{id:\d+}/toggle', [Admin\AnnouncementController::class, 'toggle'], 'admin.announcements.toggle');
    });

    $router->group(['middleware' => ['can:support.manage']], function ($router) {
        $router->get('/support', [Admin\SupportController::class, 'index'], 'admin.support');
        $router->get('/support/{id:\d+}', [Admin\SupportController::class, 'show'], 'admin.support.show');
        $router->post('/support/{id:\d+}/reply', [Admin\SupportController::class, 'reply'], 'admin.support.reply');
    });

    $router->group(['middleware' => ['can:settings.manage']], function ($router) {
        $router->get('/settings', [Admin\SettingsController::class, 'index'], 'admin.settings');
        $router->post('/settings', [Admin\SettingsController::class, 'update'], 'admin.settings.update');
    });
});
