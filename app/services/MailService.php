<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/env.php';

class MailService
{
    private ?string $lastError = null;

    public function send(string $to, string $subject, string $message): bool
    {
        $this->lastError = null;

        $from = getenv('LMS_MAIL_FROM') ?: 'no-reply@library.local';
        $fromName = getenv('LMS_MAIL_FROM_NAME') ?: 'Library Management System';

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $this->formatAddress($from, $fromName),
            'Reply-To: ' . $from,
            'X-Mailer: PHP/' . phpversion(),
        ];

        $sent = mail($to, $subject, $message, implode("\r\n", $headers));

        if (! $sent) {
            $this->lastError = 'PHP mail() returned false. Check SMTP/sendmail configuration for this PHP installation.';
            $this->logFailure($to, $subject);
        }

        return $sent;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    private function formatAddress(string $email, string $name): string
    {
        $cleanName = str_replace(['"', "\r", "\n"], '', $name);
        $cleanEmail = str_replace(["\r", "\n"], '', $email);

        return sprintf('"%s" <%s>', $cleanName, $cleanEmail);
    }

    private function logFailure(string $to, string $subject): void
    {
        $logDirectory = __DIR__ . '/../logs';
        if (! is_dir($logDirectory)) {
            @mkdir($logDirectory, 0775, true);
        }

        $logFile = $logDirectory . '/mail-debug.log';
        $line = sprintf(
            "[%s] Failed sending mail to %s with subject %s: %s%s",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            $this->lastError,
            PHP_EOL
        );

        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
