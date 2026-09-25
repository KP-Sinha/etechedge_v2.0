<?php
/**
 * EtechEdge Solutions - Standalone SMTP Mailer Utility
 * 
 * Supports industry-standard PHPMailer.
 * Configured for multi-recipient delivery (3 recipients) and visitor Reply-To.
 */

error_reporting(E_ALL);
ini_set('display_errors', '0'); // Never expose errors or credentials in public output

$configFile = __DIR__ . '/smtp_config.php';
$config = file_exists($configFile) ? require $configFile : [];

/**
 * Send an email using SMTP and PHPMailer.
 *
 * @param array $options [
 *   'to'          => 'comma-separated or array of emails',
 *   'to_name'     => 'Recipient Name',
 *   'subject'     => 'Email Subject',
 *   'body_html'   => '<p>HTML content</p>',
 *   'body_plain'  => 'Plain text content',
 *   'reply_to'    => 'visitor@example.com',
 *   'reply_name'  => 'Visitor Name',
 * ]
 * @return array ['success' => bool, 'message' => string]
 */
function sendEtechEdgeMail(array $options): array {
    global $config;

    // Load .env if present
    if (file_exists(__DIR__ . '/.env')) {
        $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value, " \t\n\r\0\x0B'\"");
                putenv("$name=$value");
                $_ENV[$name] = $value;
            }
        }
    }

    $host       = getenv('SMTP_HOST') ?: ($config['host'] ?? 'smtp.gmail.com');
    $port       = (int)(getenv('SMTP_PORT') ?: ($config['port'] ?? 587));
    $username   = getenv('SMTP_USERNAME') ?: ($config['username'] ?? '');
    $password   = getenv('SMTP_PASSWORD') ?: ($config['password'] ?? '');
    $encryption = strtolower(getenv('SMTP_ENCRYPTION') ?: ($config['encryption'] ?? 'tls'));
    $fromEmail  = getenv('MAIL_FROM_ADDRESS') ?: ($config['from_email'] ?? $username);
    $fromName   = getenv('MAIL_FROM_NAME') ?: ($config['from_name'] ?? 'EtechEdge Solutions');

    // Parse recipients (supports comma-separated string or array)
    $rawRecipients = $options['to'] ?? (getenv('MAIL_TO_ADDRESS') ?: ($config['to_email'] ?? ''));
    $recipientList = [];
    if (is_array($rawRecipients)) {
        $recipientList = $rawRecipients;
    } elseif (is_string($rawRecipients)) {
        foreach (explode(',', $rawRecipients) as $email) {
            $clean = trim($email);
            if (filter_var($clean, FILTER_VALIDATE_EMAIL)) {
                $recipientList[] = $clean;
            }
        }
    }

    if (empty($recipientList)) {
        return [
            'success' => false,
            'message' => 'No valid recipient email address configured.',
        ];
    }

    $toName    = $options['to_name'] ?? (getenv('MAIL_TO_NAME') ?: ($config['to_name'] ?? 'EtechEdge Admin'));
    $subject   = $options['subject'] ?? 'New Website Enquiry - EtechEdge';
    $bodyHtml  = $options['body_html'] ?? '';
    $bodyPlain = $options['body_plain'] ?? strip_tags($bodyHtml);

    // 1. Check if PHPMailer is available via Composer autoload
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    }

    // 2. Check PHPMailer class
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $username;
            $mail->Password   = $password;

            if ($encryption === 'ssl' || $port === 465) {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls' || $port === 587) {
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
            }
            $mail->Port = $port;
            $mail->Timeout = 15;

            // Sender: Authenticated Gmail account
            $mail->setFrom($fromEmail, $fromName);

            // Add all configured recipients
            foreach ($recipientList as $recEmail) {
                $mail->addAddress($recEmail, $toName);
            }

            // Reply-To: Website visitor email
            if (!empty($options['reply_to']) && filter_var($options['reply_to'], FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($options['reply_to'], $options['reply_name'] ?? '');
            }

            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $bodyHtml ?: nl2br(htmlspecialchars($bodyPlain));
            $mail->AltBody = $bodyPlain;

            $mail->send();
            return [
                'success' => true,
                'message' => 'Email sent successfully via PHPMailer to ' . count($recipientList) . ' recipients.',
            ];
        } catch (\Exception $e) {
            // Mask password if it appears anywhere in the exception message
            $safeError = !empty($password) ? str_replace($password, '********', $e->getMessage()) : $e->getMessage();
            return [
                'success' => false,
                'message' => 'PHPMailer Error: ' . $safeError,
            ];
        }
    }

    return [
        'success' => false,
        'message' => 'PHPMailer is not installed. Composer autoload missing.',
    ];
}
