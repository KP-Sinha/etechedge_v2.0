<?php
/**
 * EtechEdge Solutions - Standalone SMTP Configuration
 * 
 * Secure server-side configuration reading from environment variables or .env.
 * Dummy fallbacks only; real credentials must reside in .env or server environment.
 */

// Load .env helper if present
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            // Remove quotes if present
            $value = trim($value, "'\"");
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

return [
    'host'       => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
    'port'       => (int)(getenv('SMTP_PORT') ?: 587),
    'username'   => getenv('SMTP_USERNAME') ?: 'krishanu0344@gmail.com',
    'password'   => getenv('SMTP_PASSWORD') ?: '',
    'encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
    'from_email' => getenv('MAIL_FROM_ADDRESS') ?: 'krishanu0344@gmail.com',
    'from_name'  => getenv('MAIL_FROM_NAME') ?: 'EtechEdge Solutions',
    'to_email'   => getenv('MAIL_TO_ADDRESS') ?: 'kprash357@gmail.com,prabalsiddharan@gmail.com,info@etechedge.com',
    'to_name'    => getenv('MAIL_TO_NAME') ?: 'EtechEdge Inquiries',
];
