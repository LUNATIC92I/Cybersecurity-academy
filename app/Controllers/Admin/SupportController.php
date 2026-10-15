<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuditLogger;

final class SupportController extends AdminController
{
    public function index(Request $request): Response
    {
        $status = (string) $request->query('status', 'open');
        $params = [];
        $where = '1 = 1';
        if (in_array($status, ['open', 'pending', 'closed'], true)) {
            $where = 'status = ?';
            $params[] = $status;
        }
        return $this->render('support/index', [
            'result' => $this->paginate("SELECT * FROM support_tickets WHERE $where ORDER BY created_at DESC", $params, (int) $request->query('page', 1)),
            'status' => $status,
        ]);
    }

    public function show(Request $request): Response
    {
        $ticket = db()->fetch('SELECT * FROM support_tickets WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        return $this->render('support/show', [
            'ticket' => $ticket,
            'replies' => db()->fetchAll('SELECT r.*, u.display_name FROM ticket_replies r LEFT JOIN users u ON u.id = r.author_id WHERE r.ticket_id = ? ORDER BY r.id', [(int) $ticket['id']]),
        ]);
    }

    public function reply(Request $request): Response
    {
        $ticket = db()->fetch('SELECT * FROM support_tickets WHERE id = ?', [(int) $request->param('id')]) ?? throw HttpException::notFound();
        $data = $this->validate($request, ['message' => 'max:5000', 'status' => 'required|in:open,pending,closed']);
        if ($data['message'] !== '' && $data['message'] !== null) {
            db()->insert('ticket_replies', ['ticket_id' => (int) $ticket['id'], 'author_id' => auth()->id(), 'is_staff' => 1, 'message' => $data['message']]);
            app('mailer')->send($ticket['email'], 'ticket_reply', [
                'name' => $ticket['name'],
                'reference' => $ticket['reference'],
                'reply' => $data['message'],
            ], $ticket['locale']);
        }
        db()->update('support_tickets', ['status' => $data['status']], ['id' => (int) $ticket['id']]);
        AuditLogger::log('support.ticket_updated', 'support_ticket', (int) $ticket['id'], ['status' => $data['status']]);
        flash('success', __('admin.saved'));
        return redirect(route('admin.support.show', ['id' => $ticket['id']]));
    }
}
