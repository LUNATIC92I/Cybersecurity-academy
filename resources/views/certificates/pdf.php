<?php
$L = fn (string $key, array $r = []) => __($key, $r, $locale);
$title = \App\Services\CertificateService::title($cert, $locale);
$path = \App\Services\CertificateService::pathName($cert, $locale);
$criteria = json_decode((string) $cert['criteria'], true) ?: [];
?>
<!doctype html>
<html lang="<?= e($locale) ?>">
<head><meta charset="utf-8"><title><?= e($cert['certificate_number']) ?></title>
<style>
@page { margin: 0; }
body { margin: 0; font-family: 'DejaVu Sans', sans-serif; color: #0f172a; }
.page { position: relative; width: 297mm; height: 210mm; background: #ffffff; }
.frame { position: absolute; top: 10mm; left: 10mm; right: 10mm; bottom: 10mm; border: 2px solid #0b1b3f; }
.inner { position: absolute; top: 14mm; left: 14mm; right: 14mm; bottom: 14mm; border: 0.6px solid #38bdf8; }
.band { position: absolute; top: 0; left: 0; right: 0; height: 9mm; background: #0b1b3f; }
.band span { display: block; height: 9mm; width: 40%; background: #0ea5e9; }
.content { position: absolute; top: 26mm; left: 28mm; right: 28mm; text-align: center; }
.brand { font-size: 12pt; letter-spacing: 3px; color: #0b1b3f; font-weight: bold; }
.label { margin-top: 10mm; font-size: 11pt; letter-spacing: 2px; color: #475569; text-transform: uppercase; }
.holder { margin-top: 5mm; font-size: 30pt; font-weight: bold; color: #0b1b3f; }
.text { margin-top: 5mm; font-size: 11pt; color: #334155; }
.title { margin-top: 3mm; font-size: 20pt; font-weight: bold; color: #0369a1; }
.path { margin-top: 2mm; font-size: 11pt; color: #475569; }
.criteria { margin-top: 6mm; font-size: 9pt; color: #475569; }
.footer { position: absolute; left: 28mm; right: 28mm; bottom: 24mm; }
.footer table { width: 100%; border-collapse: collapse; }
.footer td { vertical-align: bottom; font-size: 9pt; color: #334155; }
.sig { border-top: 0.6px solid #94a3b8; padding-top: 2mm; width: 70mm; }
.qr { text-align: right; }
.qr img { width: 30mm; height: 30mm; }
.number { font-family: 'DejaVu Sans Mono', monospace; font-size: 9pt; color: #0b1b3f; }
.disclaimer { position: absolute; left: 28mm; right: 28mm; bottom: 16mm; font-size: 7pt; color: #64748b; text-align: center; }
</style></head>
<body><div class="page">
  <div class="band"><span></span></div>
  <div class="frame"></div><div class="inner"></div>
  <div class="content">
    <div class="brand">CYBERSECURITY-ACADEMY</div>
    <div class="label"><?= e($L('pdf.certificate_of_achievement')) ?></div>
    <div class="text"><?= e($L('pdf.presented_to')) ?></div>
    <div class="holder"><?= e($cert['holder_name']) ?></div>
    <div class="text"><?= e($L('pdf.for_completing')) ?></div>
    <div class="title"><?= e($title) ?></div>
    <?php if ($path): ?><div class="path"><?= e($L('pdf.learning_path', ['path' => $path])) ?></div><?php endif; ?>
    <div class="criteria"><?= e($L('verify.criteria_text', ['lessons' => $criteria['lessons_completed'] ?? 0, 'labs' => $criteria['labs_passed'] ?? 0, 'score' => $criteria['exam_score'] ?? 0, 'required' => $criteria['exam_required'] ?? 0])) ?></div>
  </div>
  <div class="footer"><table><tr>
    <td><div class="sig"><?= e($signatory) ?></div><div><?= e($L('pdf.issued_on', ['date' => format_date($cert['issued_at'], $locale)])) ?></div></td>
    <td class="qr"><img src="<?= e($qr) ?>" alt=""><div class="number"><?= e($cert['certificate_number']) ?></div><div><?= e($L('pdf.verify_at')) ?> <?= e(preg_replace('#^https?://#', '', $verifyUrl)) ?></div></td>
  </tr></table></div>
  <div class="disclaimer"><?= e($L('footer.certificate_notice')) ?></div>
</div></body></html>
