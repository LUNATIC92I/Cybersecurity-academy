<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\CourseRepository;
use App\Services\AccessService;
use App\Services\AchievementService;
use App\Services\ActivityService;
use App\Services\AuditLogger;
use App\Services\Localizer;
use App\Services\NotificationService;
use App\Services\ProgressService;
use App\Services\UserService;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $this->userId();
        $enrolled = $this->enrolledCourses($userId);
        $next = null;
        foreach ($enrolled as $course) {
            if ($course['completed_at'] === null && ($lesson = ProgressService::nextLesson($userId, (int) $course['id']))) {
                $lt = Localizer::attach([$lesson], 'lesson_translations', 'lesson_id')[0];
                $next = ['course' => $course, 'lesson' => $lt];
                break;
            }
        }
        $quizStats = db()->fetch(
            'SELECT COUNT(*) AS attempts, COALESCE(ROUND(AVG(percent)), 0) AS avg_score, COALESCE(SUM(passed), 0) AS passed
             FROM quiz_attempts WHERE user_id = ? AND status = "submitted"',
            [$userId]
        );
        $labStats = db()->fetch(
            'SELECT COUNT(*) AS attempts, COUNT(DISTINCT CASE WHEN passed = 1 THEN lab_id END) AS passed FROM lab_attempts WHERE user_id = ?',
            [$userId]
        );
        $recentQuizzes = db()->fetchAll(
            'SELECT qa.*, q.slug, q.kind FROM quiz_attempts qa JOIN quizzes q ON q.id = qa.quiz_id
             WHERE qa.user_id = ? AND qa.status <> "in_progress" ORDER BY qa.submitted_at DESC LIMIT 5',
            [$userId]
        );
        foreach ($recentQuizzes as &$rq) {
            $rq['t'] = Localizer::pick(array_column(db()->fetchAll('SELECT * FROM quiz_translations WHERE quiz_id = ?', [(int) $rq['quiz_id']]), null, 'locale')) ?? [];
        }
        unset($rq);
        $recentLabs = db()->fetchAll(
            'SELECT la.*, l.slug FROM lab_attempts la JOIN labs l ON l.id = la.lab_id WHERE la.user_id = ? ORDER BY la.submitted_at DESC LIMIT 5',
            [$userId]
        );
        foreach ($recentLabs as &$rl) {
            $rl['t'] = Localizer::pick(array_column(db()->fetchAll('SELECT * FROM lab_translations WHERE lab_id = ?', [(int) $rl['lab_id']]), null, 'locale')) ?? [];
        }
        unset($rl);
        $paths = \App\Repositories\PathRepository::all();

        return view('dashboard/index', [
            'enrolled' => $enrolled,
            'next' => $next,
            'quizStats' => $quizStats,
            'labStats' => $labStats,
            'streak' => ActivityService::streak($userId),
            'studySeconds' => ProgressService::totalStudySeconds($userId),
            'activity' => ActivityService::daily($userId, 14),
            'recentActivity' => $this->describeActivity(ActivityService::recent($userId, 8)),
            'recentQuizzes' => $recentQuizzes,
            'recentLabs' => $recentLabs,
            'achievements' => array_filter(AchievementService::forUser($userId), fn ($a) => $a['earned_at'] !== null),
            'certificates' => db()->fetchAll('SELECT * FROM certificates WHERE user_id = ? ORDER BY issued_at DESC', [$userId]),
            'subscription' => AccessService::activeSubscription($userId),
            'paths' => $paths,
            'announcements' => $this->announcements(),
            'meta' => $this->meta(__('dashboard.title')) + ['robots' => 'noindex'],
        ], 'layouts/dashboard');
    }

    public function courses(Request $request): Response
    {
        $userId = $this->userId();
        $enrolled = $this->enrolledCourses($userId);
        $enrolledIds = array_map(fn ($c) => (int) $c['id'], $enrolled);
        $available = array_filter(
            CourseRepository::catalog([], 1, 100)['items'],
            fn ($c) => !in_array((int) $c['id'], $enrolledIds, true)
        );
        foreach ($available as &$course) {
            $course['access_source'] = AccessService::accessSource($userId, $course);
        }
        return view('dashboard/courses', [
            'enrolled' => $enrolled,
            'available' => $available,
            'meta' => $this->meta(__('dashboard.my_courses')) + ['robots' => 'noindex'],
        ], 'layouts/dashboard');
    }

    public function resources(Request $request): Response
    {
        $userId = $this->userId();
        $courseIds = array_map('intval', db()->column('SELECT course_id FROM enrollments WHERE user_id = ?', [$userId]));
        $resources = [];
        if ($courseIds) {
            $resources = db()->fetchAll(
                'SELECT r.*, lt.title AS lesson_title, l.slug AS lesson_slug, m.slug AS module_slug, c.slug AS course_slug
                 FROM lesson_resources r
                 JOIN lessons l ON l.id = r.lesson_id JOIN modules m ON m.id = l.module_id JOIN courses c ON c.id = m.course_id
                 LEFT JOIN lesson_translations lt ON lt.lesson_id = l.id AND lt.locale = ?
                 WHERE m.course_id IN (' . \App\Core\Database::placeholders($courseIds) . ') AND l.status = "published"
                   AND (r.locale IS NULL OR r.locale = ?)
                 ORDER BY c.id, m.sort_order, l.sort_order',
                array_merge([Localizer::contentLocale()], $courseIds, [Localizer::contentLocale()])
            );
        }
        $labResources = db()->fetchAll(
            'SELECT r.*, l.slug AS lab_slug FROM lesson_resources r JOIN labs l ON l.id = r.lab_id
             WHERE l.status = "published" AND (r.locale IS NULL OR r.locale = ?) ORDER BY l.id',
            [Localizer::contentLocale()]
        );
        $datasets = \App\Repositories\LabRepository::published();
        return view('dashboard/resources', [
            'resources' => $resources,
            'labResources' => $labResources,
            'datasets' => array_filter($datasets, fn ($l) => !empty($l['dataset_file'])),
            'meta' => $this->meta(__('dashboard.resources')) + ['robots' => 'noindex'],
        ], 'layouts/dashboard');
    }

    public function certificates(Request $request): Response
    {
        $userId = $this->userId();
        $certs = db()->fetchAll('SELECT * FROM certificates WHERE user_id = ? ORDER BY issued_at DESC', [$userId]);
        $inProgress = array_filter($this->enrolledCourses($userId), fn ($c) => $c['completed_at'] === null);
        return view('dashboard/certificates', [
            'certificates' => $certs,
            'inProgress' => $inProgress,
            'meta' => $this->meta(__('dashboard.certificates')) + ['robots' => 'noindex'],
        ], 'layouts/dashboard');
    }

    public function achievements(Request $request): Response
    {
        $userId = $this->userId();
        AchievementService::evaluate($userId);
        return view('dashboard/achievements', [
            'achievements' => AchievementService::forUser($userId),
            'meta' => $this->meta(__('dashboard.achievements')) + ['robots' => 'noindex'],
        ], 'layouts/dashboard');
    }

    public function notifications(Request $request): Response
    {
        return view('dashboard/notifications', [
            'notifications' => NotificationService::recent($this->userId(), 50),
            'meta' => $this->meta(__('dashboard.notifications')) + ['robots' => 'noindex'],
        ], 'layouts/dashboard');
    }

    public function markRead(Request $request): Response
    {
        NotificationService::markAllRead($this->userId());
        return redirect(route('dashboard.notifications'));
    }

    public function billing(Request $request): Response
    {
        $userId = $this->userId();
        return view('dashboard/billing', [
            'subscription' => AccessService::activeSubscription($userId),
            'orders' => db()->fetchAll('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 50', [$userId]),
            'grants' => db()->fetchAll(
                'SELECT g.*, ct.title AS course_title FROM access_grants g LEFT JOIN course_translations ct ON ct.course_id = g.course_id AND ct.locale = ?
                 WHERE g.user_id = ? ORDER BY g.created_at DESC',
                [locale(), $userId]
            ),
            'meta' => $this->meta(__('dashboard.billing')) + ['robots' => 'noindex'],
        ], 'layouts/dashboard');
    }

    public function settings(Request $request): Response
    {
        return view('dashboard/settings', [
            'user' => $this->user(),
            'meta' => $this->meta(__('dashboard.settings')) + ['robots' => 'noindex'],
        ], 'layouts/dashboard');
    }

    public function updateProfile(Request $request): Response
    {
        $data = $this->validate($request, [
            'display_name' => 'required|min:2|max:120',
            'headline' => 'max:160',
            'locale' => 'required|in:en,fr',
            'content_locale' => 'in:en,fr,auto',
        ]);
        db()->update('users', [
            'display_name' => $data['display_name'],
            'headline' => $data['headline'] ?: null,
            'locale' => $data['locale'],
            'content_locale' => in_array($data['content_locale'], ['en', 'fr'], true) ? $data['content_locale'] : null,
        ], ['id' => $this->userId()]);
        session()->put('locale', $data['locale']);
        translator()->setLocale($data['locale']);
        auth()->refresh();
        flash('success', __('settings.saved'));
        return redirect(route('dashboard.settings'));
    }

    public function updatePassword(Request $request): Response
    {
        $this->throttle('password-change:' . $this->userId(), 10, 3600);
        $data = $this->validate($request, [
            'current_password' => 'required',
            'password' => 'required|password|confirmed',
        ]);
        $user = $this->user();
        if (!password_verify($data['current_password'], $user['password_hash'])) {
            session()->flash('errors', ['current_password' => __('settings.wrong_password')]);
            return redirect(route('dashboard.settings') . '#security');
        }
        UserService::changePassword((int) $user['id'], $data['password']);
        $fresh = db()->fetch('SELECT * FROM users WHERE id = ?', [(int) $user['id']]);
        auth()->login($fresh);
        AuditLogger::log('auth.password_changed', 'user', (int) $user['id']);
        flash('success', __('settings.password_changed'));
        return redirect(route('dashboard.settings'));
    }

    public function updatePrivacy(Request $request): Response
    {
        $user = $this->user();
        $public = $request->bool('public_profile');
        $slug = $user['public_slug'];
        if ($public && !$slug) {
            $slug = UserService::generatePublicSlug($user['display_name']);
        }
        db()->update('users', [
            'public_profile' => $public ? 1 : 0,
            'show_progress_public' => $public && $request->bool('show_progress_public') ? 1 : 0,
            'public_slug' => $slug,
        ], ['id' => (int) $user['id']]);
        AuditLogger::log('user.privacy_updated', 'user', (int) $user['id'], ['public_profile' => $public]);
        flash('success', __('settings.saved'));
        return redirect(route('dashboard.settings') . '#privacy');
    }

    public function deleteAccount(Request $request): Response
    {
        $user = $this->user();
        if (!password_verify((string) $request->input('password'), $user['password_hash']) || $request->string('confirm') !== 'DELETE') {
            session()->flash('errors', ['delete' => __('settings.delete_confirm_error')]);
            return redirect(route('dashboard.settings') . '#delete');
        }
        if (auth()->hasRole('super_admin') && (int) db()->value(
            'SELECT COUNT(*) FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE r.code = "super_admin"'
        ) <= 1) {
            session()->flash('errors', ['delete' => __('settings.last_admin')]);
            return redirect(route('dashboard.settings') . '#delete');
        }
        UserService::requestDeletion((int) $user['id']);
        app('mailer')->send($user['email'], 'account_deletion', [
            'name' => $user['display_name'],
            'days' => (int) config('security.account_deletion_grace_days', 14),
        ], $user['locale']);
        auth()->logout();
        flash('success', __('settings.deletion_scheduled', ['days' => (int) config('security.account_deletion_grace_days', 14)]));
        return redirect(route('home'));
    }

    /**
     * Data portability: JSON export of the learner's own records.
     */
    public function exportData(Request $request): Response
    {
        $userId = $this->userId();
        $user = $this->user();
        unset($user['password_hash'], $user['auth_version']);
        $data = [
            'exported_at' => gmdate('c'),
            'profile' => $user,
            'enrollments' => db()->fetchAll('SELECT course_id, access_source, enrolled_at, completed_at FROM enrollments WHERE user_id = ?', [$userId]),
            'lesson_progress' => db()->fetchAll('SELECT lesson_id, status, started_at, completed_at, active_seconds FROM lesson_progress WHERE user_id = ?', [$userId]),
            'quiz_attempts' => db()->fetchAll('SELECT quiz_id, attempt_no, status, started_at, submitted_at, percent, passed FROM quiz_attempts WHERE user_id = ?', [$userId]),
            'lab_attempts' => db()->fetchAll('SELECT lab_id, attempt_no, percent, passed, submitted_at FROM lab_attempts WHERE user_id = ?', [$userId]),
            'certificates' => db()->fetchAll('SELECT certificate_number, title_en, title_fr, status, issued_at FROM certificates WHERE user_id = ?', [$userId]),
            'orders' => db()->fetchAll('SELECT reference, item_type, item_id, total_cents, currency, status, created_at FROM orders WHERE user_id = ?', [$userId]),
        ];
        AuditLogger::log('user.data_exported', 'user', $userId);
        return Response::download(
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'cybersecurity-academy-data.json',
            'application/json'
        );
    }

    private function enrolledCourses(int $userId): array
    {
        $rows = db()->fetchAll(
            'SELECT c.*, e.enrolled_at, e.completed_at, e.access_source, COALESCE(cp.percent, 0) AS percent,
                    cp.lessons_completed, cp.lessons_total, cp.labs_passed, cp.labs_total, cp.exam_passed
             FROM enrollments e JOIN courses c ON c.id = e.course_id
             LEFT JOIN course_progress cp ON cp.user_id = e.user_id AND cp.course_id = e.course_id
             WHERE e.user_id = ? ORDER BY e.completed_at IS NOT NULL, e.enrolled_at DESC',
            [$userId]
        );
        return CourseRepository::decorate($rows);
    }

    private function describeActivity(array $rows): array
    {
        return array_map(fn ($r) => $r + ['label' => __('activity.' . $r['type'])], $rows);
    }

    private function announcements(): array
    {
        $rows = db()->fetchAll(
            'SELECT * FROM announcements WHERE is_active = 1
               AND (starts_at IS NULL OR starts_at <= UTC_TIMESTAMP()) AND (ends_at IS NULL OR ends_at > UTC_TIMESTAMP())
             ORDER BY created_at DESC LIMIT 3'
        );
        return Localizer::attach($rows, 'announcement_translations', 'announcement_id', 't', locale());
    }
}
