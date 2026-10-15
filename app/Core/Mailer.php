<?php

declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Sends translated e-mail templates from resources/emails/{locale}/.
 * Drivers: "log" (writes to storage/logs/mail.log, default for development)
 * and "smtp" (PHPMailer). Sent messages are also kept in memory for tests.
 */
final class Mailer
{
    private array $sent = [];

    public function __construct(private readonly array $config, private readonly View $view, private readonly Logger $logger)
    {
    }

    public function send(string $to, string $template, array $data, string $locale): bool
    {
        $locale = in_array($locale, Translator::SUPPORTED, true) ? $locale : 'en';
        $previous = translator()->locale();
        translator()->setLocale($locale);
        try {
            $subject = __('email.' . $template . '.subject', $data['subject_vars'] ?? []);
            $html = $this->view->render('emails/' . $template, $data + ['locale' => $locale, 'subject' => $subject], 'emails/layout');
        } finally {
            translator()->setLocale($previous);
        }
        $text = trim(html_entity_decode(strip_tags(preg_replace('/<br\s*\/?>|<\/p>/i', "\n", $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        $this->sent[] = compact('to', 'subject', 'html', 'text', 'template', 'locale');

        $driver = $this->config['driver'] ?? 'log';
        if ($driver === 'smtp') {
            return $this->sendSmtp($to, $subject, $html, $text);
        }
        if ($driver === 'array') {
            return true;
        }
        $this->logger->info('mail', ['to' => $to, 'subject' => $subject, 'template' => $template]);
        @file_put_contents(
            base_path('storage/logs/mail.log'),
            sprintf("[%s] To: %s\nSubject: %s\n\n%s\n\n-----\n", gmdate('c'), $to, $subject, $text),
            FILE_APPEND | LOCK_EX
        );
        return true;
    }

    private function sendSmtp(string $to, string $subject, string $html, string $text): bool
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = (string) $this->config['host'];
            $mail->Port = (int) $this->config['port'];
            $mail->SMTPAuth = !empty($this->config['username']);
            $mail->Username = (string) $this->config['username'];
            $mail->Password = (string) $this->config['password'];
            $mail->SMTPSecure = $this->config['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom((string) $this->config['from_address'], (string) $this->config['from_name']);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $html;
            $mail->AltBody = $text;
            $mail->send();
            return true;
        } catch (\Throwable $e) {
            $this->logger->error('SMTP send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function sent(): array
    {
        return $this->sent;
    }
}
