<?php
$L = fn (string $key, array $r = []) => __($key, $r, $locale);
$title = \App\Services\CertificateService::title($cert, $locale);
$path = \App\Services\CertificateService::pathName($cert, $locale);
$criteria = json_decode((string) ($cert['criteria'] ?? ''), true) ?: [];
$lessons = (int) ($criteria['lessons_completed'] ?? 0);
$labs = (int) ($criteria['labs_passed'] ?? 0);
$score = (int) ($criteria['exam_score'] ?? 0);
$fontDir = base_path('resources/certificates/fonts');
?>
<!doctype html>
<html lang="<?= e($locale) ?>">
<head><meta charset="utf-8"><title><?= e($cert['certificate_number']) ?></title>
<style>
@font-face { font-family: "Grotesk"; font-weight: 700; font-style: normal; src: url("<?= e($fontDir) ?>/sg700.ttf") format("truetype"); }
@font-face { font-family: "Grotesk"; font-weight: 500; font-style: normal; src: url("<?= e($fontDir) ?>/sg500.ttf") format("truetype"); }
@font-face { font-family: "Mono"; font-weight: 600; font-style: normal; src: url("<?= e($fontDir) ?>/jbm600.ttf") format("truetype"); }
@page { margin: 0; }
body { margin: 0; font-family: "DejaVu Sans", sans-serif; color: #11203b; }
.page { position: relative; width: 297mm; height: 210mm; background: #ffffff; }

/* Ornamental frame */
.frame { position: absolute; top: 8mm; left: 8mm; right: 8mm; bottom: 8mm; border: 1.1mm solid #0B1B3F; }
.frame-inner { position: absolute; top: 10.6mm; left: 10.6mm; right: 10.6mm; bottom: 10.6mm; border: 0.3mm solid #21D4C2; }
/* Path-colour accent bar under the wordmark */
.accentbar { position: absolute; top: 46mm; left: 118mm; width: 61mm; height: 1.1mm; }
.accentbar span { float: left; height: 1.1mm; width: 33.33%; }
.ab1 { background: #247BFD; } .ab2 { background: #21D4C2; } .ab3 { background: #8B5CF6; }
/* Corner ornaments */
.corner { position: absolute; width: 9mm; height: 9mm; }
.corner b { position: absolute; background: #247BFD; }
.corner .h { height: 0.5mm; width: 9mm; } .corner .v { width: 0.5mm; height: 9mm; }
.c-tl { top: 12.5mm; left: 12.5mm; } .c-tr { top: 12.5mm; right: 12.5mm; }
.c-bl { bottom: 12.5mm; left: 12.5mm; } .c-br { bottom: 12.5mm; right: 12.5mm; }
.c-tr .h, .c-tr .v { right: 0; } .c-bl .h { bottom: 0; } .c-bl .v { bottom: 0; } .c-br .h { bottom: 0; right: 0; } .c-br .v { bottom: 0; right: 0; }

/* Watermark monogram */
.watermark { position: absolute; top: 80mm; left: 0; right: 0; text-align: center; font-family: "Grotesk"; font-weight: 700; font-size: 120pt; color: #f5f8fd; letter-spacing: 2mm; }

/* Header */
.head { position: absolute; top: 22mm; left: 24mm; right: 24mm; text-align: center; }
.mark { display: inline-block; width: 13mm; height: 13mm; background: #0B1B3F; border-radius: 2.6mm; color: #fff; font-family: "Grotesk"; font-weight: 700; font-size: 14pt; line-height: 13mm; }
.wordmark { font-family: "Grotesk"; font-weight: 700; font-size: 14pt; letter-spacing: 1.4mm; color: #0B1B3F; margin-top: 3mm; }
.wordmark em { color: #247BFD; font-style: normal; }
.eyebrow { font-size: 9.5pt; letter-spacing: 2.6mm; text-transform: uppercase; color: #5a6b85; margin-top: 8mm; }

/* Body */
.content { position: absolute; top: 74mm; left: 30mm; right: 30mm; text-align: center; }
.lede { font-size: 11pt; color: #5a6b85; }
.holder { font-family: "Grotesk"; font-weight: 700; font-size: 34pt; color: #0B1B3F; margin: 3mm 0 1mm; }
.rule { width: 70mm; height: 0.4mm; background: #C9D4E6; margin: 0 auto 4mm; }
.title { font-family: "Grotesk"; font-weight: 700; font-size: 20pt; color: #247BFD; margin: 3mm 0 1mm; }
.path { font-size: 10.5pt; color: #5a6b85; }

/* Credential details strip */
.details { position: absolute; top: 134mm; left: 54mm; right: 54mm; }
.details table { width: 100%; border-collapse: collapse; }
.details td { text-align: center; padding: 2mm 1mm; border-left: 0.3mm solid #dbe3f0; }
.details td:first-child { border-left: 0; }
.details .val { font-family: "Grotesk"; font-weight: 700; font-size: 15pt; color: #0B1B3F; }
.details .lab { font-size: 7.5pt; letter-spacing: 0.5mm; text-transform: uppercase; color: #5a6b85; margin-top: 1mm; }

/* Verification seal (left) */
.seal { position: absolute; left: 30mm; bottom: 26mm; width: 30mm; height: 30mm; border: 0.8mm solid #0B1B3F; border-radius: 50%; text-align: center; }
.seal .ring { position: absolute; top: 2mm; left: 2mm; right: 2mm; bottom: 2mm; border: 0.3mm solid #21D4C2; border-radius: 50%; }
.seal .check { font-size: 15pt; color: #21D4C2; margin-top: 6mm; }
.seal .vtxt { font-family: "Grotesk"; font-weight: 700; font-size: 8pt; letter-spacing: 0.8mm; text-transform: uppercase; color: #0B1B3F; margin-top: 0.5mm; }
.seal .vsub { font-size: 5pt; color: #5a6b85; margin-top: 0.6mm; }

/* Signature (center) */
.sign { position: absolute; left: 108mm; right: 108mm; bottom: 28mm; text-align: center; }
.sign .who { font-family: "Grotesk"; font-weight: 500; font-size: 11pt; color: #0B1B3F; padding-bottom: 1.5mm; border-bottom: 0.3mm solid #8a99b4; }
.sign .role { font-size: 7.5pt; letter-spacing: 0.4mm; text-transform: uppercase; color: #5a6b85; margin-top: 1.5mm; }
.sign .date { font-size: 9pt; color: #11203b; margin-top: 2mm; }

/* QR (right) */
.qr { position: absolute; right: 30mm; bottom: 24mm; width: 44mm; text-align: center; }
.qr img { width: 24mm; height: 24mm; }
.qr .num { font-family: "Mono"; font-weight: 600; font-size: 8pt; color: #0B1B3F; margin-top: 1.5mm; }
.qr .vlabel { font-size: 6.5pt; color: #5a6b85; margin-top: 0.5mm; }

/* Honest notice */
.notice { position: absolute; left: 24mm; right: 24mm; bottom: 11mm; font-size: 6.5pt; line-height: 1.5; color: #7587a1; text-align: center; }
</style></head>
<body><div class="page">
  <div class="frame"></div>
  <div class="frame-inner"></div>
  <div class="corner c-tl"><b class="h"></b><b class="v"></b></div>
  <div class="corner c-tr"><b class="h"></b><b class="v"></b></div>
  <div class="corner c-bl"><b class="h"></b><b class="v"></b></div>
  <div class="corner c-br"><b class="h"></b><b class="v"></b></div>

  <div class="watermark">CA</div>

  <div class="head">
    <span class="mark">CA</span>
    <div class="wordmark">CYBERSECURITY<em>-</em>ACADEMY</div>
    <div class="eyebrow"><?= e($L('pdf.certificate_of_achievement')) ?></div>
  </div>
  <div class="accentbar"><span class="ab1"></span><span class="ab2"></span><span class="ab3"></span></div>

  <div class="content">
    <div class="lede"><?= e($L('pdf.presented_to')) ?></div>
    <div class="holder"><?= e($cert['holder_name']) ?></div>
    <div class="rule"></div>
    <div class="lede"><?= e($L('pdf.for_completing')) ?></div>
    <div class="title"><?= e($title) ?></div>
    <?php if ($path): ?><div class="path"><?= e($L('pdf.learning_path', ['path' => $path])) ?></div><?php endif; ?>
  </div>

  <div class="details"><table><tr>
    <td><div class="val"><?= e(format_number($lessons)) ?></div><div class="lab"><?= e($L('pdf.detail_lessons')) ?></div></td>
    <td><div class="val"><?= e(format_number($labs)) ?></div><div class="lab"><?= e($L('pdf.detail_labs')) ?></div></td>
    <td><div class="val"><?= e($score) ?>%</div><div class="lab"><?= e($L('pdf.detail_score')) ?></div></td>
  </tr></table></div>

  <div class="seal">
    <div class="ring"></div>
    <div class="check">&#10003;</div>
    <div class="vtxt"><?= e($L('pdf.verified_seal')) ?></div>
    <div class="vsub"><?= e($L('pdf.authenticated')) ?></div>
  </div>

  <div class="sign">
    <div class="who"><?= e($signatory) ?></div>
    <div class="role"><?= e($L('pdf.signatory_label')) ?></div>
    <div class="date"><?= e($L('pdf.issued_on', ['date' => format_date($cert['issued_at'], $locale)])) ?></div>
  </div>

  <div class="qr">
    <img src="<?= e($qr) ?>" alt="">
    <div class="num"><?= e($cert['certificate_number']) ?></div>
    <div class="vlabel"><?= e($L('pdf.verify_at')) ?> <?= e(preg_replace('#^https?://#', '', $verifyUrl)) ?></div>
  </div>

  <div class="notice"><?= e($L('footer.certificate_notice')) ?></div>
</div></body></html>
