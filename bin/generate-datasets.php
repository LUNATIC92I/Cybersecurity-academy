<?php

declare(strict_types=1);

/*
 * Generates the synthetic lab datasets in database/content/datasets.
 * Output is deterministic (fixed seed) so lab answer keys stay valid.
 * All IP addresses use private (RFC 1918) or documentation ranges
 * (RFC 5737), and all names and domains are fictitious (.example).
 *
 *   php bin/generate-datasets.php
 */

mt_srand(20260314);
$dir = dirname(__DIR__) . '/database/content/datasets';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

function csv(string $file, array $header, array $rows): void
{
    $fh = fopen($file, 'w');
    fputcsv($fh, $header, ',', '"', '\\');
    foreach ($rows as $row) {
        fputcsv($fh, $row, ',', '"', '\\');
    }
    fclose($fh);
}

function ts(int $t): string
{
    return gmdate('Y-m-d H:i:s', $t);
}

// -----------------------------------------------------------------
// 1. Windows authentication events (brute-force investigation)
// -----------------------------------------------------------------
$users = ['a.martin', 'b.okafor', 'c.dubois', 'd.nguyen', 'e.silva', 'f.rossi', 'g.kowalski', 'h.diallo', 'i.haddad', 'j.berg'];
$hosts = ['WS-FIN-07', 'WS-HR-02', 'WS-ENG-11', 'WS-ENG-14', 'WS-SALES-03', 'WS-MKT-05'];
$rows = [];
$start = strtotime('2026-03-13 07:30:00 UTC');
for ($i = 0; $i < 260; $i++) {
    $t = $start + mt_rand(0, 11 * 3600);
    $u = $users[array_rand($users)];
    $rows[] = [ts($t), 4624, 'DC-01', $u, mt_rand(0, 4) === 0 ? 10 : 3, '10.0.5.' . mt_rand(10, 60), '0x0', 'An account was successfully logged on'];
}
// A few genuine typing mistakes (wrong password) followed by success.
foreach (['c.dubois', 'f.rossi', 'i.haddad'] as $k => $u) {
    $t = strtotime('2026-03-13 08:1' . $k . ':00 UTC');
    $ip = '10.0.5.' . (20 + $k);
    $rows[] = [ts($t), 4625, 'DC-01', $u, 3, $ip, '0xC000006A', 'An account failed to log on'];
    $rows[] = [ts($t + 9), 4625, 'DC-01', $u, 3, $ip, '0xC000006A', 'An account failed to log on'];
    $rows[] = [ts($t + 21), 4624, 'DC-01', $u, 3, $ip, '0x0', 'An account was successfully logged on'];
}
// Known vulnerability scanner with stale credentials (benign noise).
for ($i = 0; $i < 6; $i++) {
    $rows[] = [ts(strtotime('2026-03-13 23:00:00 UTC') + $i * 600), 4625, 'FS-01', 'svc_scan', 3, '10.0.0.5', '0xC000006A', 'An account failed to log on'];
}
// Unknown user names tried once each (enumeration noise from the same attacker host, earlier).
foreach (['administrator', 'admin', 'backup', 'test'] as $k => $name) {
    $rows[] = [ts(strtotime('2026-03-14 01:49:00 UTC') + $k * 7), 4625, 'FS-01', $name, 3, '10.0.9.77', '0xC0000064', 'An account failed to log on'];
}
// The attack: 38 wrong-password failures against svc_backup, then success.
$t = strtotime('2026-03-14 01:52:10 UTC');
for ($i = 0; $i < 38; $i++) {
    $rows[] = [ts($t), 4625, 'FS-01', 'svc_backup', 3, '10.0.9.77', '0xC000006A', 'An account failed to log on'];
    $t += ($i === 37) ? 0 : (int) round(250 / 37);
}
$rows[] = [ts(strtotime('2026-03-14 01:56:20 UTC')), 4625, 'FS-01', 'svc_backup', 3, '10.0.9.77', '0xC000006A', 'An account failed to log on'];
array_pop($rows); // keep exactly 38 failures; last failure already near 01:56:20
$rows[] = ['2026-03-14 01:56:41', 4624, 'FS-01', 'svc_backup', 3, '10.0.9.77', '0x0', 'An account was successfully logged on'];
$rows[] = ['2026-03-14 01:56:41', 4672, 'FS-01', 'svc_backup', 3, '10.0.9.77', '0x0', 'Special privileges assigned to new logon'];
$rows[] = ['2026-03-14 02:03:17', 4720, 'FS-01', 'helpdesk2', 0, '-', '0x0', 'A user account was created (by svc_backup)'];
$rows[] = ['2026-03-14 02:03:20', 4732, 'FS-01', 'helpdesk2', 0, '-', '0x0', 'A member was added to a security-enabled local group: Administrators'];
$rows[] = ['2026-03-14 02:11:02', 1102, 'FS-01', 'helpdesk2', 0, '-', '0x0', 'The audit log was cleared'];
usort($rows, fn ($a, $b) => strcmp($a[0], $b[0]));
csv("$dir/soc-auth-events.csv", ['TimeGenerated', 'EventID', 'Computer', 'Account', 'LogonType', 'IpAddress', 'SubStatus', 'Activity'], $rows);
$failures = count(array_filter($rows, fn ($r) => $r[1] === 4625 && $r[3] === 'svc_backup' && $r[5] === '10.0.9.77'));
fwrite(STDOUT, "soc-auth-events.csv: " . count($rows) . " rows, svc_backup failures from 10.0.9.77 = $failures\n");

// -----------------------------------------------------------------
// 2. Firewall / flow log (network triage)
// -----------------------------------------------------------------
$rows = [];
$base = strtotime('2026-03-14 01:00:00 UTC');
// Normal browsing and services.
$internal = ['10.0.5.11', '10.0.5.14', '10.0.5.23', '10.0.5.31', '10.0.5.42'];
$external = ['203.0.113.10', '203.0.113.25', '198.51.100.12', '198.51.100.40'];
for ($i = 0; $i < 180; $i++) {
    $rows[] = [ts($base + mt_rand(0, 7200)), $internal[array_rand($internal)], mt_rand(49152, 65535), $external[array_rand($external)], [443, 443, 443, 80, 53][mt_rand(0, 4)], 'TCP', 'allow', mt_rand(400, 9000), mt_rand(5000, 400000)];
}
// Vertical scan 10.0.9.77 -> 10.0.2.15.
$t = strtotime('2026-03-14 01:40:00 UTC');
foreach (range(1, 120) as $k => $port) {
    $open = in_array($port, [22, 80, 88, 445], true);
    $rows[] = [ts($t + intdiv($k, 3)), '10.0.9.77', 51000 + $k, '10.0.2.15', $port, 'TCP', $open ? 'allow' : 'deny', 60, $open ? 60 : 0];
}
// Beaconing 10.0.5.20 -> 198.51.100.77:443 roughly every 60 s.
$t = strtotime('2026-03-14 01:30:00 UTC');
for ($i = 0; $i < 70; $i++) {
    $rows[] = [ts($t), '10.0.5.20', mt_rand(49152, 65535), '198.51.100.77', 443, 'TCP', 'allow', 405 + mt_rand(0, 12), 1195 + mt_rand(0, 12)];
    $t += 60 + mt_rand(-3, 3);
}
// Exfiltration 10.0.5.20 -> 203.0.113.200:443.
$rows[] = ['2026-03-14 02:14:05', '10.0.5.20', 55012, '203.0.113.200', 443, 'TCP', 'allow', 3812004118, 182330];
usort($rows, fn ($a, $b) => strcmp($a[0], $b[0]));
csv("$dir/soc-network-flows.csv", ['timestamp', 'src_ip', 'src_port', 'dst_ip', 'dst_port', 'protocol', 'action', 'bytes_out', 'bytes_in'], $rows);
fwrite(STDOUT, "soc-network-flows.csv: " . count($rows) . " rows\n");

// -----------------------------------------------------------------
// 3. Suspicious e-mail (phishing investigation)
// -----------------------------------------------------------------
$eml = <<<EML
Return-Path: <bounce-7731@mail.bulk-sender.example>
Received: from mail.bulk-sender.example (mail.bulk-sender.example [203.0.113.45])
	by mx1.northwind.example with ESMTPS id 4F2A91C0
	for <a.martin@northwind.example>; Sat, 14 Mar 2026 06:41:12 +0000
Authentication-Results: mx1.northwind.example;
	spf=fail (domain of micros0ft-support.example does not designate 203.0.113.45 as permitted sender) smtp.mailfrom=micros0ft-support.example;
	dkim=none (message not signed);
	dmarc=fail action=quarantine header.from=micros0ft-support.example
From: "Microsoft 365 Security" <no-reply@micros0ft-support.example>
Reply-To: <billing@payments-secure.example>
To: <a.martin@northwind.example>
Subject: [Action required] Unusual sign-in activity - verify within 24 hours
Date: Sat, 14 Mar 2026 06:41:09 +0000
Message-ID: <a81f2c90.7731@mail.bulk-sender.example>
MIME-Version: 1.0
Content-Type: text/html; charset="UTF-8"
X-Mailer: BulkMailer 4.2

<html><body>
<p>Dear user,</p>
<p>We detected an unusual sign-in to your Microsoft 365 account from an unknown device.
If this was not you, your mailbox will be <b>suspended within 24 hours</b>.</p>
<p><a href="https://login.micros0ft-support.example/verify?u=a.martin%40northwind.example&amp;t=7731">Verify your account now</a></p>
<p>Attachment: Security_Report.html</p>
<p>Microsoft 365 Security Team</p>
</body></html>
EML;
file_put_contents("$dir/soc-phishing-email.eml", str_replace("\n", "\r\n", $eml) . "\r\n");
fwrite(STDOUT, "soc-phishing-email.eml written\n");

// -----------------------------------------------------------------
// 4. Web access log with SQL injection attempts (defensive analysis)
// -----------------------------------------------------------------
$lines = [];
$t = strtotime('2026-03-15 09:00:00 UTC');
$agents = ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) Firefox/124.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_3) Safari/605.1.15', 'Mozilla/5.0 (X11; Linux x86_64) Chrome/123.0'];
$pages = ['/', '/products.php?id=3', '/products.php?id=7', '/products.php?id=12', '/cart.php', '/about.html', '/static/app.css', '/search.php?q=lamp'];
$fmt = fn (string $ip, int $ts, string $req, int $code, int $size, string $ua) => sprintf('%s - - [%s] "%s HTTP/1.1" %d %d "-" "%s"', $ip, gmdate('d/M/Y:H:i:s +0000', $ts), $req, $code, $size, $ua);
for ($i = 0; $i < 150; $i++) {
    $t += mt_rand(2, 25);
    $lines[] = $fmt('203.0.113.' . mt_rand(20, 90), $t, 'GET ' . $pages[array_rand($pages)], 200, mt_rand(1800, 9400), $agents[array_rand($agents)]);
}
$attacker = '198.51.100.66';
$probe = [
    ["GET /products.php?id=3'", 500, 412],
    ["GET /products.php?id=3%27%20OR%20%271%27=%271", 200, 48211],
    ["GET /products.php?id=3%20ORDER%20BY%205--", 500, 412],
    ["GET /products.php?id=3%20ORDER%20BY%204--", 200, 3120],
    ["GET /products.php?id=-1%20UNION%20SELECT%201,2,3,4--",