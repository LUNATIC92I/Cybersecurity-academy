<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuditLogger;
use App\Services\UserService;

final class UserController extends AdminController
{
    public function index(Request $request): Response
    {
        $q = mb_substr((string) $request->query('q', ''), 0, 80);
        $role = (string) $request->query('role', '');
        $where = ['1 = 1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(u.email LIKE ? OR u.display_name LIKE ?)';
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
            array_push($params, $like, $like);
        }
        if ($role !== '') {
            $where[] = 'u.id IN (SELECT ur.user_id FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE r.code = ?)';
            $params[] = $role;
        }
        $result = $this->paginate(
            'SELECT u.id, u.email, u.display_name, u.status, u.locale, u.created_at, u.last_login_at,
                    (SELECT GROUP_CONCAT(r.code ORDER BY r.id) FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = u.id) AS roles
             FROM users u WHERE ' . implode(' AND ', $where) . ' ORDER BY u.id DESC',
            $params,
            (int) $request->query('page', 1)
        );
        return $this->render('users/index', [
            'result' => $result,
            'q' => $q,
            'role' => $role,
            'roles' => db()->fetchAll('SELECT * FROM roles ORDER BY id'),
        ]);
    }

    public function show(Request $request): Response
    {
        $user = db()->fetch('SELECT * FROM users WHERE id = ?', [(int) $request->param('id')]);
        if (!$user) {
            throw HttpException::notFound();
        }
        $id = (int) $user['id'];
        return $this->render('users/show', [
            'user' => $user,
            'roles' => db()->fetchAll('SELECT * FROM roles ORDER BY id'),
            'userRoles' => db()->column('SELECT r.code FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ?', [$id]),
            'enrollments' => db()->fetchAll(
                'SELECT e.*, ct.title, COALESCE(cp.percent, 0) AS percent FROM enrollments e
                 JOIN course_translations ct ON ct.course_id = e.course_id AND ct.locale = "en"
                 LEFT JOIN course_progress cp ON cp.user_id = e.user_id AND cp.course_id = e.course_id
                 WHERE e.user_id = ?',
                [$id]
            ),
            'grants' => db()->fetchAll('SELECT * FROM access_grants WHERE user_id = ? ORDER BY id DESC', [$id]),
            'certificates' => db()->fetchAll('SELECT * FROM certificates WHERE user_id = ?', [$id]),
            'logins' => db()->fetchAll('SELECT * FROM login_attempts WHERE email = ? ORDER BY id DESC LIMIT 10', [$user['email']]),
            'courses' => db()->fetchAll('SELECT c.id, ct.title FROM courses c JOIN course_translations ct ON ct.course_id = c.id AND ct.locale = "en" ORDER BY c.id'),
            'paths' => db()->fetchAll('SELECT p.id, pt.title FROM learning_paths p JOIN learning_path_translations pt ON pt.path_id = p.id AND pt.locale = "en" ORDER BY p.sort_order'),
        ]);
    }

    public function roles(Request $request): Response
    {
        $this->authorize('roles.manage');
        $id = (int) $request->param('id');
        $user = db()->fetch('SELECT * FROM users WHERE id = ?', [$id]) ?? throw HttpException::notFound();
        $valid = db()->column('SELECT code FROM roles');
        $roles = array_values(array_intersect($request->array('roles'), $valid));
        if (!$roles) {
            $roles = ['student'];
        }
        // Only a super administrator may grant or remove super administrator.
        $currentlySuper = in_array('super_admin', db()->column('SELECT r.code FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ?', [$id]), true);
        if ((in_array('super_admin', $roles, true) || $currentlySuper) && !auth()->hasRole('super_admin')) {
            throw HttpException::forbidden();
        }
        if ($currentlySuper && !in_array('super_admin', $roles, true)
            && (int) db()->value('SELECT COUNT(*) FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE r.code = "super_admin"') <= 1) {
            flash('error', __('admin.users.last_super'));
            return redirect(route('admin.users.show', ['id' => $id]));
        }
        UserService::syncRoles($id, $roles);
        AuditLogger::log('user.roles_changed', 'user', $id, ['roles' => $roles]);
        flash('success', __('admin.saved'));
        return redirect(route('admin.users.show', ['id' => $user['id']]));
    }

    public function status(Request $request): Response
    {
        $id = (int) $request->param('id');
        $status = $request->string('status');
        if (!in_array($status, ['active', 'suspended'], true) || $id === auth()->id()) {
            throw HttpException::forbidden();
        }
        if (in_array('super_admin', db()->column('SELECT r.code FROM user_roles ur JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ?', [$id]), true)
            && !auth()->hasRole('super_admin')) {
            throw HttpException::forbidden();
        }
        db()->query('UPDATE users SET status = ?, auth_version = auth_version + 1 WHERE id = ?', [$status, $id]);
        AuditLogger::log('user.status_changed', 'user', $id, ['status' => $status]);
        flash('success', __('admin.saved'));
        return redirect(route('admin.users.show', ['id' => $id]));
    }

    public function grant(Request $request): Response
    {
        $id = (int) $request->param('id');
        $data = $this->validate($request, [
            'scope' => 'required|in:all,course,path',
            'course_id' => 'integer',
            'path_id' => 'integer',
            'reason' => 'required|max:255',
            'expires_at' => 'date',
        ]);
        db()->insert('access_grants', [
            'user_id' => $id,
            'course_id' => $data['scope'] === 'course' ? (int) $data['course_id'] : null,
            'path_id' => $data['scope'] === 'path' ? (int) $data['path_id'] : null,
            'source' => 'admin',
            'granted_by' => auth()->id(),
            'reason' => $data['reason'],
            'expires_at' => $data['expires_at'] ? gmdate('Y-m-d H:i:s', strtotime($data['expires_at'])) : null,
        ]);
        AuditLogger::log('access.granted', 'user', $id, $data);
        flash('success', __('admin.saved'));
        return redirect(route('admin.users.show', ['id' => $id]));
    }

    public function revokeGrant(Request $request): Response
    {
        $grant = db()->fetch('SELECT * FROM access_grants WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        db()->update('access_grants', ['expires_at' => now()], ['id' => (int) $grant['id']]);
        AuditLogger::log('access.revoked', 'access_grant', (int) $grant['id'], ['user_id' => (int) $grant['user_id']]);
        flash('success', __('admin.saved'));
        return redirect(route('admin.users.show', ['id' => $grant['user_id']]));
    }
}
