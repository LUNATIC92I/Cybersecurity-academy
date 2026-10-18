<?php

declare(strict_types=1);

namespace App\Services;

use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Certificate issuance, verification and revocation.
 *
 * Certificates document achievement within Cybersecurity-Academy only; they
 * are not accredited professional certifications.
 */
final class CertificateService
{
    public const NUMBER_PATTERN = '/^CA-[A-Z]{2,5}-\d{4}-[A-F0-9]{8}$/';

    /**
     * Issue a certificate if (and only if) the completion rules are met.
     */
    public static function issueIfEligible(int $userId, int $courseId, ?int $actorId = null): ?array
    {
        $existing = db()->fetch('SELECT * FROM certificates WHERE user_id = ? AND course_id = ?', [$userId, $courseId]);
        if ($existing) {
            return $existing;
        }
        $status = CompletionService::evaluate($userId, $courseId);
        if (!$status['eligible'] || (int) $status['course']['certificate_enabled'] !== 1) {
            return null;
        }
        return self::issue($userId, $status, $actorId);
    }

    private static function issue(int $userId, array $status, ?int $actorId): array
    {
        $course = $status['course'];
        return db()->transaction(function () use ($userId, $course, $status, $actorId) {
            $user = db()->fetch('SELECT * FROM users WHERE id = ? FOR UPDATE', [$userId]);
            if ($again = db()->fetch('SELECT * FROM certificates WHERE user_id = ? AND course_id = ?', [$userId, (int) $course['id']])) {
                return $again;
            }
            $path = db()->fetch(
                'SELECT lp.* FROM learning_paths lp JOIN learning_path_courses lpc ON lpc.path_id = lp.id
                 WHERE lpc.course_id = ? ORDER BY lpc.sort_order LIMIT 1',
                [(int) $course['id']]
            );
            $titles = [];
            $pathNames = [];
            foreach (['en', 'fr'] as $loc) {
                $t = db()->fetch('SELECT title, certificate_title FROM course_translations WHERE course_id = ? AND locale = ?', [(int) $course['id'], $loc])
                    ?? db()->fetch('SELECT title, certificate_title FROM course_translations WHERE course_id = ? AND locale = "en"', [(int) $course['id']]);
                $titles[$loc] = $t['certificate_title'] ?: $t['title'];
                if ($path) {
                    $pathNames[$loc] = db()->value('SELECT title FROM learning_path_translations WHERE path_id = ? AND locale = ?', [(int) $path['id'], $loc])
                        ?? db()->value('SELECT title FROM learning_path_translations WHERE path_id = ? AND locale = "en"', [(int) $path['id']]);
                }
            }
            $issuedAt = now();
            $number = self::generateNumber($path['code'] ?? 'GEN');
            $criteria = [
                'lessons_completed' => $status['lessons']['done'],
                'lessons_required' => $status['lessons']['total'],
                'labs_passed' => $status['labs']['done'],
                'labs_required' => $status['labs']['total'],
                'exam_score' => $status['exam']['best'],
                'exam_required' => $status['exam']['required'],
            ];
            $id = db()->insert('certificates', [
                'certificate_number' => $number,
                'user_id' => $userId,
                'course_id' => (int) $course['id'],
                'path_id' => $path['id'] ?? null,
                'holder_name' => $user['display_name'],
                'title_en' => $titles['en'],
                'title_fr' => $titles['fr'],
                'path_name_en' => $pathNames['en'] ?? null,
                'path_name_fr' => $pathNames['fr'] ?? null,
                'locale' => $user['locale'],
                'criteria' => json_encode($criteria),
                'status' => 'valid',
                'issued_at' => $issuedAt,
                'signature' => self::sign($number, $user['display_name'], (int) $course['id'], $issuedAt),
                'is_public' => 0,
                'share_token' => bin2hex(random_bytes(16)),
            ]);
            db()->insert('certificate_events', ['certificate_id' => $id, 'event' => 'issued', 'actor_id' => $actorId]);
            AuditLogger::log('certificate.issued', 'certificate', $number, ['course_id' => (int) $course['id']], $actorId ?? $userId);
            NotificationService::notify($userId, 'certificate_issued', ['number' => $number], route('dashboard.certificates'));
            ActivityService::record($userId, 'certificate_issued', $id);
            app('mailer')->send($user['email'], 'certificate_issued', [
                'name' => $user['display_name'],
                'title' => $titles[$user['locale']] ?? $titles['en'],
                'number' => $number,
                'url' => absolute_route('verify.show', ['number' => $number]),
                'subject_vars' => ['title' => $titles[$user['locale']] ?? $titles['en']],
            ], $user['locale']);
            return db()->fetch('SELECT * FROM certificates WHERE id = ?', [$id]);
        });
    }

    /**
     * CA-{PATH}-{YEAR}-{8 hex chars from a CSPRNG}; retried on collision.
     */
    public static function generateNumber(string $pathCode): string
    {
        $code = strtoupper(preg_replace('/[^A-Z]/i', '', $pathCode) ?: 'GEN');
        do {
            $number = sprintf('CA-%s-%s-%s', $code, gmdate('Y'), strtoupper(bin2hex(random_bytes(4))));
        } while (db()->value('SELECT COUNT(*) FROM certificates WHERE certificate_number = ?', [$number]));
        return $number;
    }

    public static function sign(string $number, string $holder, int $courseId, string $issuedAt): string
    {
        $key = (string) config('app.key');
        if ($key === '') {
            throw new \RuntimeException('APP_KEY must be configured before issuing certificates.');
        }
        return hash_hmac('sha256', implode('|', [$number, $holder, $courseId, $issuedAt]), $key);
    }

    /**
     * Public verification. Returns ['state' => valid|revoked|invalid|tampered, 'certificate' => ?array].
     */
    public static function verify(string $number): array
    {
        $number = strtoupper(trim($number));
        if (!preg_match(self::NUMBER_PATTERN, $number)) {
            return ['state' => 'invalid', 'certificate' => null];
        }
        $cert = db()->fetch('SELECT * FROM certificates WHERE certificate_number = ?', [$number]);
        if (!$cert) {
            return ['state' => 'invalid', 'certificate' => null];
        }
        $expected = self::sign($cert['certificate_number'], $cert['holder_name'], (int) $cert['course_id'], $cert['issued_at']);
        if (!hash_equals($expected, $cert['signature'])) {
            app('logger')->warning('Certificate signature mismatch', ['number' => $number]);
            return ['state' => 'tampered', 'certificate' => null];
        }
        return ['state' => $cert['status'] === 'valid' ? 'valid' : 'revoked', 'certificate' => $cert];
    }

    public static function revoke(int $certificateId, string $reason, int $actorId): void
    {
        db()->transaction(function () use ($certificateId, $reason, $actorId) {
            $cert = db()->fetch('SELECT * FROM certificates WHERE id = ? FOR UPDATE', [$certificateId]);
            if (!$cert || $cert['status'] === 'revoked') {
                return;
            }
            db()->update('certificates', ['status' => 'revoked', 'revoked_at' => now(), 'revoke_reason' => mb_substr($reason, 0, 255)], ['id' => $certificateId]);
            db()->insert('certificate_events', ['certificate_id' => $certificateId, 'event' => 'revoked', 'actor_id' => $actorId, 'notes' => mb_substr($reason, 0, 255)]);
            AuditLogger::log('certificate.revoked', 'certificate', $cert['certificate_number'], ['reason' => $reason], $actorId);
            if ($cert['user_id']) {
                NotificationService::notify((int) $cert['user_id'], 'certificate_revoked', ['number' => $cert['certificate_number']]);
            }
        });
    }

    public static function reinstate(int $certificateId, string $notes, int $actorId): void
    {
        db()->transaction(function () use ($certificateId, $notes, $actorId) {
            $cert = db()->fetch('SELECT * FROM certificates WHERE id = ? FOR UPDATE', [$certificateId]);
            if (!$cert || $cert['status'] === 'valid') {
                return;
            }
            db()->update('certificates', ['status' => 'valid', 'revoked_at' => null, 'revoke_reason' => null], ['id' => $certificateId]);
            db()->insert('certificate_events', ['certificate_id' => $certificateId, 'event' => 'reinstated', 'actor_id' => $actorId, 'notes' => mb_substr($notes, 0, 255)]);
            AuditLogger::log('certificate.reinstated', 'certificate', $cert['certificate_number'], ['notes' => $notes], $actorId);
        });
    }

    /**
     * Manual issuance by an administrator still requires eligibility; an
     * override is deliberately not offered so certificates stay meaningful.
     */
    public static function adminIssue(int $userId, int $courseId, int $actorId): ?array
    {
        ProgressService::recalculate($userId, $courseId);
        return self::issueIfEligible($userId, $courseId, $actorId);
    }

    public static function title(array $cert, ?string $locale = null): string
    {
        return ($locale ?? locale()) === 'fr' ? $cert['title_fr'] : $cert['title_en'];
    }

    public static function pathName(array $cert, ?string $locale = null): ?string
    {
        return ($locale ?? locale()) === 'fr' ? $cert['path_name_fr'] : $cert['path_name_en'];
    }

    public static function verificationUrl(array $cert): string
    {
        return absolute_route('verify.show', ['number' => $cert['certificate_number']]);
    }

    public static function qrPng(string $data, int $scale = 6): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'outputBase64' => false,
            'scale' => $scale,
            'quietzoneSize' => 2,
            'eccLevel' => \chillerlan\QRCode\Common\EccLevel::M,
        ]);
        return (string) (new QRCode($options))->render($data);
    }

    public static function qrDataUri(string $data, int $scale = 6): string
    {
        return 'data:image/png;base64,' . base64_encode(self::qrPng($data, $scale));
    }

    public static function pdf(array $cert, string $locale): string
    {
        $html = app('view')->render('certificates/pdf', [
            'cert' => $cert,
            'locale' => $locale,
            'qr' => self::qrDataUri(self::verificationUrl($cert), 5),
            'verifyUrl' => self::verificationUrl($cert),
            'signatory' => (string) setting('certificate_signatory'),
        ], null);
        $options = new Options();
        $options->setIsRemoteEnabled(false);
        $options->setIsPhpEnabled(false);
        $options->setDefaultFont('DejaVu Sans');
        $options->setChroot([base_path('resources/certificates')]);
        // Writable cache so custom @font-face brand fonts can be registered.
        $fontCache = base_path('storage/cache/dompdf-fonts');
        if (!is_dir($fontCache)) {
            @mkdir($fontCache, 0775, true);
        }
        if (is_dir($fontCache) && is_writable($fontCache)) {
            $options->setFontDir($fontCache);
            $options->setFontCache($fontCache);
        }
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        return (string) $dompdf->output();
    }
}
