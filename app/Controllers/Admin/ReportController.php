<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuditLogger;

final class ReportController extends AdminController
{
    public function progress(Request $request): Response
    {
        $courseId = (int) $request->query('course', 0);
        $params = [];
        $where = '1 = 1';
        if ($courseId) {
            $where = 'e.course_id = ?';
            $params[] = $courseId;
        }
        $result = $this->paginate(
            "SELECT e.*, u.display_name, u.email, ct.title, COALESCE(cp.percent, 0) AS percent, cp.lessons_completed, cp.lessons_total,
                    cp.labs_passed, cp.labs_total, cp.exam_passed, cp.updated_at AS progress_updated
             FROM enrollments e JOIN users u ON u.id = e.user_id
             JOIN course_translations ct ON ct.course_id = e.course_id AND ct.locale = 'en'
             LEFT JOIN course_progress cp ON cp.user_id = e.user_id AND cp.course_id = e.course_id
             WHERE $where ORDER BY e.enrolled_at DESC",
            $params,
            (int) $request->query('page', 1)
        );
        $funnel = db()->fetchAll(
            'SELECT ct.title, COUNT(e.id) AS enrolled, SUM(cp.percent >= 50) AS halfway, SUM(e.completed_at IS NOT NULL) AS completed
             FROM courses c JOIN course_translations ct ON ct.course_id = c.id AND ct.locale = "en"
             LEFT JOIN enrollments e ON e.course_id = c.id
             LEFT JOIN course_progress cp ON cp.user_id = e.user_id AND cp.course_id = e.course_id
             GROUP BY c.id, ct.title ORDER BY c.id'
        );
        return $this->render('reports/progress', [
            'result' => $result,
            'funnel' => $funnel,
            'courseId' => $courseId,
            'courses' => db()->fetchAll('SELECT c.id, ct.title FROM courses c JOIN course_translations ct ON ct.course_id = c.id AND ct.locale = "en" ORDER BY c.id'),
        ]);
    }

    public function audit(Request $request): Response
    {
        $action = mb_substr((string) $request->query('action', ''), 0, 80);
        $params = [];
        $where = '1 = 1';
        if ($action !== '') {
            $where = 'a.action LIKE ?';
            $params[] = str_replace(['%', '_'], ['\\%', '\\_'], $action) . '%';
        }
        return $this->render('reports/audit', [
            'result' => $this->paginate(
                "SELECT a.*, u.display_name, u.email FROM audit_logs a LEFT JOIN users u ON u.id = a.actor_id WHERE $where ORDER BY a.id DESC",
                $params,
                (int) $request->query('page', 1),
                50
            ),
            'action' => $action,
        ]);
    }

    /**
     * CSV exports. Cells beginning with formula characters are prefixed to
     * prevent CSV/formula injection in spreadsheet software.
     */
    public function export(Request $request): Response
    {
        $type = (string) $request->param('type');
        [$header, $rows] = match ($type) {
            'users' => [
                ['id', 'email', 'display_name', 'status', 'locale', 'created_at', 'last_login_at'],
                db()->fetchAll('SELECT id, email, display_name, status, locale, created_at, last_login_at FROM users ORDER BY id'),
            ],
            'progress' => [
                ['user_id', 'email', 'course', 'percent', 'lessons_completed', 'lessons_total', 'labs_passed', 'exam_passed', 'enrolled_at', 'completed_at'],
                db()->fetchAll(
                    'SELECT e.user_id, u.email, ct.title, COALESCE(cp.percent,0), cp.lessons_completed, cp.lessons_total, cp.labs_passed, cp.exam_passed, e.enrolled_at, e.completed_at
                     FROM enrollments e JOIN users u ON u.id = e.user_id JOIN course_translations ct ON ct.course_id = e.course_id AND ct.locale = "en"
                     LEFT JOIN course_progress cp ON cp.user_id = e.user_id AND cp.course_id = e.course_id ORDER BY e.id'
                ),
            ],
            'certificates' => [
                ['certificate_number', 'holder_name', 'title_en', 'status', 'issued_at', 'revoked_at', 'revoke_reason'],
                db()->fetchAll('SELECT certificate_number, holder_name, title_en, status, issued_at, revoked_at, revoke_reason FROM certificates ORDER BY id'),
            ],
            'orders' => [
                ['reference', 'email', 'item_type', 'item_id', 'total_cents', 'currency', 'status', 'provider', 'created_at', 'paid_at'],
                db()->fetchAll('SELECT o.reference, u.email, o.item_type, o.item_id, o.total_cents, o.currency, o.status, o.provider, o.created_at, o.paid_at FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.id'),
            ],
        };
        $out = fopen('php://temp', 'w+');
        fputcsv($out, $header, ',', '"', '\\');
        foreach ($rows as $row) {
            fputcsv($out, array_map([self::class, 'safeCell'], array_values($row)), ',', '"', '\\');
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        AuditLogger::log('report.exported', 'report', $type, ['rows' => count($rows)]);
        return Response::download("\xEF\xBB\xBF" . $csv, $type . '-' . gmdate('Ymd') . '.csv', 'text/csv; charset=UTF-8');
    }

    public static function safeCell(mixed $value): string
    {
        $value = (string) ($value ?? '');
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}
