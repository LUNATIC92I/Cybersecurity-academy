<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuditLogger;

final class SupportController extends Controller
{
    public function contact(Request $request): Response
    {
        return view('pages/contact', ['meta' => $this->meta(__('nav.contact'), __('contact.lead'))]);
    }

    public function submit(Request $request): Response
    {
        $this->throttle('contact:' . $request->ip(), 5, 3600);
        // Honeypot field: real users never fill it.
        if ($request->string('website') !== '') {
            flash('success', __('contact.sent', ['reference' => '—']));
            return redirect(route('contact'));
        }
        $data = $this->validate($request, [
            'name' => 'required|min:2|max:120',
            'email' => 'required|email',
            'category' => 'required|in:general,technical,billing,enterprise,certificate,security',
            'subject' => 'required|min:3|max:190',
            'message' => 'required|min:10|max:5000',
        ]);
        $reference = strtoupper(bin2hex(random_bytes(6)));
        $id = db()->insert('support_tickets', $data + [
            'reference' => $reference,
            'user_id' => auth()->id(),
            'locale' => locale(),
        ]);
        AuditLogger::log('support.ticket_created', 'support_ticket', $id);
        app('mailer')->send($data['email'], 'ticket_received', [
            'name' => $data['name'],
            'reference' => $reference,
            'subject_line' => $data['subject'],
        ], locale());
        flash('success', __('contact.sent', ['reference' => $reference]));
        return redirect(route('contact'));
    }
}
