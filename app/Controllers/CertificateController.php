<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuditLogger;
use App\Services\CertificateService;

final class CertificateController extends Controller
{
    public function verifyForm(Request $request): Response
    {
        $number = strtoupper(trim((string) $request->query('number', '')));
        if ($number !== '') {
            return redirect(route('verify.show', ['number' => $number]));
        }
        return view('certificates/verify-form', ['meta' => $this->meta(__('verify.title'), __('verify.lead'))]);
    }

    public function verify(Request $request): Response
    {
        $this->throttle('verify:' . $request->ip(), 120, 3600);
        $result = CertificateService::verify((string) $request->param('number'));
        $cert = $result['certificate'];
        $meta = $this->meta(__('verify.title'), __('verify.lead'), ['robots' => 'noindex']);
        if ($cert && $result['state'] === 'valid') {
            $meta['description'] = __('verify.og_description', [
                'title' => CertificateService::title($cert),
                'date' => format_date($cert['issued_at']),
            ]);
        }
        $response = view('certificates/verify', [
            'state' => $result['state'],
            'cert' => $cert,
            'number' => strtoupper((string) $request->param('number')),
            'criteria' => $cert ? (json_decode((string) $cert['criteria'], true) ?: []) : [],
            'meta' => $meta,
        ]);
        return $result['state'] === 'invalid' || $result['state'] === 'tampered' ? $response->setStatus(404) : $response;
    }

    public function qr(Request $request): Response
    {
        $result = CertificateService::verify((string) $request->param('number'));
        if (!$result['certificate']) {
            throw HttpException::notFound();
        }
        $png = CertificateService::qrPng(CertificateService::verificationUrl($result['certificate']), 8);
        return Response::download($png, $result['certificate']['certificate_number'] . '-qr.png', 'image/png', true)
            ->header('Cache-Control', 'public, max-age=86400');
    }

    public function pdf(Request $request): Response
    {
        $cert = $this->ownCertificate((int) $request->param('id'));
        if ($cert['status'] !== 'valid') {
            flash('error', __('certs.revoked_no_download'));
            return redirect(route('dashboard.certificates'));
        }
        $locale = in_array($request->query('locale'), ['en', 'fr'], true) ? (string) $request->query('locale') : locale();
        $pdf = CertificateService::pdf($cert, $locale);
        db()->insert('certificate_events', ['certificate_id' => (int) $cert['id'], 'event' => 'downloaded', 'actor_id' => $this->userId()]);
        return Response::download($pdf, $cert['certificate_number'] . '-' . $locale . '.pdf', 'application/pdf');
    }

    public function visibility(Request $request): Response
    {
        $cert = $this->ownCertificate((int) $request->param('id'));
        $public = $request->bool('is_public');
        db()->update('certificates', ['is_public' => $public ? 1 : 0], ['id' => (int) $cert['id']]);
        AuditLogger::log($public ? 'certificate.made_public' : 'certificate.made_private', 'certificate', $cert['certificate_number']);
        flash('success', $public ? __('share.now_public') : __('share.now_private'));
        return redirect(route('dashboard.certificates'));
    }

    private function ownCertificate(int $id): array
    {
        $cert = db()->fetch('SELECT * FROM certificates WHERE id = ?', [$id]);
        if (!$cert || (int) $cert['user_id'] !== $this->userId()) {
            throw HttpException::notFound();
        }
        return $cert;
    }
}
