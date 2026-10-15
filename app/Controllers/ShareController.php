<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\CertificateService;
use App\Services\Localizer;
use App\Services\ShareService;

/**
 * Public achievement pages. Only items the learner explicitly made public
 * are reachable; everything else returns 404 (no information leak).
 */
final class ShareController extends Controller
{
    public function certificate(Request $request): Response
    {
        $cert = $this->publicCertificate((string) $request->param('token'));
        $url = absolute_route('share.certificate', ['token' => $cert['share_token']]);
        $title = CertificateService::title($cert);
        return view('share/certificate', [
            'cert' => $cert,
            'shareUrl' => $url,
            'verifyUrl' => CertificateService::verificationUrl($cert),
            'meta' => $this->meta(
                __('share.cert_title', ['name' => $cert['holder_name'], 'title' => $title]),
                __('share.cert_description', ['title' => $title, 'date' => format_date($cert['issued_at'])]),
                [
                    'og_image' => absolute_route('share.certificate.image', ['token' => $cert['share_token']]),
                    'og_type' => 'article',
                    'canonical' => $url,
                ]
            ),
        ]);
    }

    public function certificateImage(Request $request): Response
    {
        $cert = $this->publicCertificate((string) $request->param('token'));
        $png = ShareService::achievementImage(
            __('share.card_headline_cert'),
            CertificateService::title($cert),
            $cert['holder_name'],
            format_date($cert['issued_at']) . ' · ' . $cert['certificate_number'],
            preg_replace('#^https?://#', '', CertificateService::verificationUrl($cert))
        );
        return Response::download($png, $cert['certificate_number'] . '.png', 'image/png', $request->query('download') === null)
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function badge(Request $request): Response
    {
        $badge = $this->publicBadge((string) $request->param('token'));
        $url = absolute_route('share.badge', ['token' => $badge['share_token']]);
        return view('share/badge', [
            'badge' => $badge,
            'shareUrl' => $url,
            'meta' => $this->meta(
                __('share.badge_title', ['name' => $badge['display_name'], 'title' => $badge['t']['title']]),
                $badge['t']['description'],
                ['og_image' => absolute_route('share.badge.image', ['token' => $badge['share_token']]), 'canonical' => $url]
            ),
        ]);
    }

    public function badgeImage(Request $request): Response
    {
        $badge = $this->publicBadge((string) $request->param('token'));
        $png = ShareService::achievementImage(
            __('share.card_headline_badge'),
            $badge['t']['title'],
            $badge['display_name'],
            format_date($badge['earned_at']),
            preg_replace('#^https?://#', '', absolute_route('share.badge', ['token' => $badge['share_token']]))
        );
        return Response::download($png, 'badge-' . $badge['code'] . '.png', 'image/png', $request->query('download') === null)
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function profile(Request $request): Response
    {
        $user = db()->fetch('SELECT * FROM users WHERE public_slug = ? AND public_profile = 1 AND status = "active"', [(string) $request->param('slug')]);
        if (!$user) {
            throw HttpException::notFound();
        }
        $certs = db()->fetchAll('SELECT * FROM certificates WHERE user_id = ? AND is_public = 1 AND status = "valid" ORDER BY issued_at DESC', [(int) $user['id']]);
        $badges = db()->fetchAll(
            'SELECT a.*, ua.earned_at, ua.share_token FROM user_achievements ua JOIN achievements a ON a.id = ua.achievement_id
             WHERE ua.user_id = ? AND ua.is_public = 1 ORDER BY ua.earned_at DESC',
            [(int) $user['id']]
        );
        $badges = Localizer::attach($badges, 'achievement_translations', 'achievement_id', 't', locale());
        $progress = [];
        if ((int) $user['show_progress_public'] === 1) {
            $progress = \App\Repositories\CourseRepository::decorate(db()->fetchAll(
                'SELECT c.*, cp.percent FROM course_progress cp JOIN courses c ON c.id = cp.course_id WHERE cp.user_id = ? AND c.status = "published" ORDER BY cp.percent DESC',
                [(int) $user['id']]
            ));
        }
        return view('share/profile', [
            'profile' => $user,
            'certs' => $certs,
            'badges' => $badges,
            'progress' => $progress,
            'shareUrl' => absolute_route('share.profile', ['slug' => $user['public_slug']]),
            'meta' => $this->meta(__('share.profile_title', ['name' => $user['display_name']]), $user['headline'] ?: __('share.profile_description')),
        ]);
    }

    public function badgeVisibility(Request $request): Response
    {
        $row = db()->fetch('SELECT * FROM user_achievements WHERE id = ? AND user_id = ?', [(int) $request->param('id'), $this->userId()]);
        if (!$row) {
            throw HttpException::notFound();
        }
        db()->update('user_achievements', ['is_public' => $request->bool('is_public') ? 1 : 0], ['id' => (int) $row['id']]);
        flash('success', $request->bool('is_public') ? __('share.now_public') : __('share.now_private'));
        return redirect(route('dashboard.achievements'));
    }

    /**
     * Records which sharing workflow a learner used (for their own history
     * and aggregate statistics). Only the owner's public items are accepted.
     */
    public function record(Request $request): Response
    {
        $type = $request->string('subject_type');
        $id = $request->int('subject_id');
        $owned = match ($type) {
            'certificate' => (bool) db()->value('SELECT COUNT(*) FROM certificates WHERE id = ? AND user_id = ? AND is_public = 1', [$id, $this->userId()]),
            'achievement' => (bool) db()->value('SELECT COUNT(*) FROM user_achievements WHERE id = ? AND user_id = ? AND is_public = 1', [$id, $this->userId()]),
            'profile' => $id === $this->userId(),
            default => false,
        };
        if (!$owned) {
            return Response::json(['ok' => false], 404);
        }
        ShareService::record($this->userId(), $type, $id, $request->string('network'));
        return Response::json(['ok' => true]);
    }

    private function publicCertificate(string $token): array
    {
        $cert = db()->fetch('SELECT * FROM certificates WHERE share_token = ? AND is_public = 1 AND status = "valid"', [$token]);
        if (!$cert) {
            throw HttpException::notFound();
        }
        return $cert;
    }

    private function publicBadge(string $token): array
    {
        $badge = db()->fetch(
            'SELECT a.*, ua.earned_at, ua.share_token, u.display_name FROM user_achievements ua
             JOIN achievements a ON a.id = ua.achievement_id JOIN users u ON u.id = ua.user_id
             WHERE ua.share_token = ? AND ua.is_public = 1 AND u.status = "active"',
            [$token]
        );
        if (!$badge) {
            throw HttpException::notFound();
        }
        return Localizer::attach([$badge], 'achievement_translations', 'achievement_id', 't', locale())[0];
    }
}
