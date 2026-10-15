<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuditLogger;

final class AnnouncementController extends AdminController
{
    public function index(Request $request): Response
    {
        $rows = db()->fetchAll(
            'SELECT a.*, en.title AS title_en, fr.title AS title_fr FROM announcements a
             LEFT JOIN announcement_translations en ON en.announcement_id = a.id AND en.locale = "en"
             LEFT JOIN announcement_translations fr ON fr.announcement_id = a.id AND fr.locale = "fr"
             ORDER BY a.id DESC'
        );
        return $this->render('announcements', ['announcements' => $rows]);
    }

    public function store(Request $request): Response
    {
        $data = $this->validate($request, [
            'level' => 'required|in:info,success,warning',
            'title_en' => 'required|max:190',
            'body_en' => 'required|max:2000',
            'title_fr' => 'required|max:190',
            'body_fr' => 'required|max:2000',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ]);
        $id = db()->transaction(function () use ($data) {
            $id = db()->insert('announcements', [
                'level' => $data['level'],
                'starts_at' => $data['starts_at'] ? gmdate('Y-m-d H:i:s', strtotime($data['starts_at'])) : null,
                'ends_at' => $data['ends_at'] ? gmdate('Y-m-d H:i:s', strtotime($data['ends_at'])) : null,
                'created_by' => auth()->id(),
            ]);
            db()->insert('announcement_translations', ['announcement_id' => $id, 'locale' => 'en', 'title' => $data['title_en'], 'body' => $data['body_en']]);
            db()->insert('announcement_translations', ['announcement_id' => $id, 'locale' => 'fr', 'title' => $data['title_fr'], 'body' => $data['body_fr']]);
            return $id;
        });
        AuditLogger::log('announcement.created', 'announcement', $id);
        flash('success', __('admin.saved'));
        return redirect(route('admin.announcements'));
    }

    public function toggle(Request $request): Response
    {
        $a = db()->fetch('SELECT * FROM announcements WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        db()->update('announcements', ['is_active' => (int) !$a['is_active']], ['id' => (int) $a['id']]);
        AuditLogger::log('announcement.toggled', 'announcement', (int) $a['id']);
        return redirect(route('admin.announcements'));
    }
}
