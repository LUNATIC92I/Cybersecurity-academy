<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Social sharing through each network's official, unauthenticated share
 * URLs. Nothing is ever posted on a learner's behalf. TikTok has no web
 * share-URL for links, so the TikTok workflow is: download the achievement
 * image, copy the public link, and post it from the TikTok app.
 */
final class ShareService
{
    public static function linkedInUrl(string $url): string
    {
        return 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($url);
    }

    public static function facebookUrl(string $url): string
    {
        return 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($url);
    }

    public static function record(int $userId, string $subjectType, int $subjectId, string $network): void
    {
        if (!in_array($network, ['linkedin', 'facebook', 'tiktok', 'copy', 'download'], true)
            || !in_array($subjectType, ['certificate', 'achievement', 'profile'], true)) {
            return;
        }
        db()->insert('social_shares', [
            'user_id' => $userId,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'network' => $network,
        ]);
    }

    /**
     * 1200x630 PNG achievement card (Open Graph size) drawn with GD using the
     * DejaVu fonts bundled with dompdf.
     */
    public static function achievementImage(string $headline, string $title, string $holder, string $date, string $url): string
    {
        $w = 1200;
        $h = 630;
        $img = imagecreatetruecolor($w, $h);
        imageantialias($img, true);

        // Vertical navy gradient background.
        for ($y = 0; $y < $h; $y++) {
            $t = $y / $h;
            $c = imagecolorallocate($img, (int) (7 + 6 * $t), (int) (12 + 10 * $t), (int) (28 + 22 * $t));
            imageline($img, 0, $y, $w, $y, $c);
        }
        // Subtle network nodes.
        $node = imagecolorallocatealpha($img, 56, 189, 248, 100);
        $line = imagecolorallocatealpha($img, 56, 189, 248, 115);
        $points = [[860, 90], [1010, 160], [1120, 70], [940, 260], [1090, 330], [1160, 220], [990, 430], [1140, 500]];
        foreach ($points as $i => [$x, $y]) {
            foreach (array_slice($points, $i + 1, 2) as [$x2, $y2]) {
                imageline($img, $x, $y, $x2, $y2, $line);
            }
            imagefilledellipse($img, $x, $y, 12, 12, $node);
        }
        // Accent bar.
        for ($x = 0; $x < $w; $x++) {
            $t = $x / $w;
            $c = imagecolorallocate($img, (int) (56 + 83 * $t), (int) (189 - 97 * $t), (int) (248 - 2 * $t));
            imageline($img, $x, 0, $x, 8, $c);
        }

        $white = imagecolorallocate($img, 241, 245, 249);
        $muted = imagecolorallocate($img, 148, 163, 184);
        $cyan = imagecolorallocate($img, 56, 189, 248);
        $fonts = base_path('vendor/dompdf/dompdf/lib/fonts');
        $bold = $fonts . '/DejaVuSans-Bold.ttf';
        $regular = $fonts . '/DejaVuSans.ttf';

        if (is_file($bold) && is_file($regular)) {
            imagettftext($img, 26, 0, 80, 110, $cyan, $bold, 'CYBERSECURITY-ACADEMY');
            imagettftext($img, 24, 0, 80, 190, $muted, $regular, self::fit($headline, 60));
            $y = 270;
            foreach (self::wrapMeasured($title, $bold, 46, 1000, 2) as $lineText) {
                imagettftext($img, 46, 0, 80, $y, $white, $bold, $lineText);
                $y += 66;
            }
            imagettftext($img, 30, 0, 80, $y + 30, $white, $regular, self::fit($holder, 40));
            imagettftext($img, 22, 0, 80, $y + 80, $muted, $regular, $date);
            imagettftext($img, 18, 0, 80, 585, $cyan, $regular, self::fit($url, 90));
        } else {
            imagestring($img, 5, 80, 100, 'Cybersecurity-Academy', $white);
            imagestring($img, 5, 80, 160, self::fit($title, 80), $white);
        }

        ob_start();
        imagepng($img, null, 6);
        imagedestroy($img);
        return (string) ob_get_clean();
    }

    private static function fit(string $text, int $max): string
    {
        return mb_strlen($text) > $max ? mb_substr($text, 0, $max - 1) . '…' : $text;
    }

    /**
     * Wrap text to a pixel width using the font's real glyph metrics.
     */
    private static function wrapMeasured(string $text, string $font, int $size, int $maxWidth, int $maxLines): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $current = '';
        $width = static function (string $s) use ($font, $size): int {
            $box = imagettfbbox($size, 0, $font, $s);
            return $box ? abs($box[2] - $box[0]) : 0;
        };
        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;
            if ($current !== '' && $width($candidate) > $maxWidth) {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }
        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $last = $lines[$maxLines - 1];
            while ($last !== '' && $width($last . '…') > $maxWidth) {
                $last = mb_substr($last, 0, -1);
            }
            $lines[$maxLines - 1] = rtrim($last) . '…';
        }
        return $lines;
    }

    private static function wrap(string $text, int $width, int $maxLines): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $current = '';
        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;
            if (mb_strlen($candidate) > $width && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }
        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $lines[$maxLines - 1] = self::fit($lines[$maxLines - 1] . '…', $width);
        }
        return $lines;
    }
}
