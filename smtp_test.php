<?php
/**
 * EtechEdge Solutions - Standalone SMTP Test Utility
 * 
 * IMPORTANT:
 * - THIS FILE IS NOT CONNECTED TO THE WEBSITE.
 * - For local XAMPP testing (http://localhost/.../smtp_test.php) or staging diagnosis.
 * - Never exposes the SMTP password in browser or console.
 * - Expected to fail with initial dummy credentials (smtp.example.com).
 */

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/smtp_mailer.php';
$config = require __DIR__ . '/smtp_config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$result = null;

if ($action === 'test_send') {
    $testTo = trim($_POST['test_to'] ?? $config['to_email']);
    $replyToVisitor = filter_var($_POST['test_visitor_reply'] ?? 'visitor@example.com', FILTER_SANITIZE_EMAIL);

    $result = sendEtechEdgeMail([
        'to'          => $testTo,
        'to_name'     => 'EtechEdge Admin Test',
        'reply_to'    => $replyToVisitor,
        'reply_name'  => 'Sample Website Visitor',
        'subject'     => 'EtechEdge SMTP Test - ' . date('Y-m-d H:i:s'),
        'body_html'   => '<h2>SMTP Diagnostic Test</h2><p>This is a verification test from your standalone EtechEdge SMTP configuration.</p><p>Recipient: <strong>' . htmlspecialchars($testTo) . '</strong></p><p>Reply-To: <strong>' . htmlspecialchars($replyToVisitor) . '</strong></p><p>Time: ' . date('r') . '</p>',
        'body_plain'  => 'SMTP Diagnostic Test - Verification test from your standalone EtechEdge SMTP configuration at ' . date('r'),
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EtechEdge - SMTP Configuration Diagnostic</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #070c18; color: #e1e7f0; margin: 0; padding: 40px 20px; }
        .container { max-width: 720px; margin: 0 auto; background: #0c1428; border: 1px solid #1a2a4e; border-radius: 12px; padding: 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { color: #80d0e1; margin-top: 0; font-size: 24px; border-bottom: 1px solid #1a2a4e; padding-bottom: 16px; }
        .alert { padding: 14px 18px; border-radius: 8px; margin: 20px 0; font-size: 14px; line-height: 1.5; }
        .alert-info { background: rgba(0, 210, 255, 0.1); border: 1px solid #00d2ff; color: #80d0e1; }
        .alert-success { background: rgba(37, 211, 102, 0.15); border: 1px solid #25d366; color: #85e8a7; }
        .alert-danger { background: rgba(255, 75, 75, 0.15); border: 1px solid #ff4b4b; color: #ffa3a3; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
        td, th { padding: 10px 12px; text-align: left; border-bottom: 1px solid #16223e; }
        th { color: #80d0e1; }
        .btn { background: #0868f7; color: #fff; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; }
        .btn:hover { background: #0654c7; }
        .masked { font-family: monospace; letter-spacing: 2px; }
        .field { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-size: 13px; color: #80d0e1; }
        input[type="email"], input[type="text"] { width: 100%; box-sizing: border-box; padding: 10px 14px; border-radius: 6px; border: 1px solid #1a2a4e; background: #050a16; color: #fff; font-size: 14px; }
        .note { font-size: 12px; color: #8899aa; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>EtechEdge SMTP Test Utility</h1>
        
        <div class="alert alert-info">
            <strong>Notice:</strong> This test script is an isolated diagnostic utility for XAMPP / local testing and is <strong>not connected</strong> to the public frontend website.
        </div>

        <h3>Active Server-Side Configuration</h3>
        <table>
            <tr><th>Setting</th><th>Current Value</th></tr>
            <tr><td>SMTP Host</td><td><code><?= htmlspecialchars($config['host']) ?></code></td></tr>
            <tr><td>SMTP Port</td><td><code><?= (int)$config['port'] ?></code></td></tr>
            <tr><td>Encryption</td><td><code><?= htmlspecialchars($config['encryption']) ?></code></td></tr>
            <tr><td>Username (Auth Account)</td><td><code><?= htmlspecialchars($config['username']) ?></code></td></tr>
            <tr><td>Password</td><td><span class="masked">&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;</span> <em>(Protected server-side)</em></td></tr>
            <tr><td>From Address (Sender)</td><td><code><?= htmlspecialchars($config['from_email']) ?></code></td></tr>
            <tr><td>Default Recipient</td><td><code><?= htmlspecialchars($config['to_email']) ?></code></td></tr>
            <tr><td>PHPMailer Status</td><td>
                <?php if (class_exists('PHPMailer\PHPMailer\PHPMailer')): ?>
                    <span style="color:#25d366;">&#10003; Installed and available</span>
                <?php else: ?>
                    <span style="color:#f59e0b;">&#9888; Not installed (Run <code>composer require phpmailer/phpmailer</code> on your PHP server)</span>
                <?php endif; ?>
            </td></tr>
        </table>

        <?php if ($result !== null): ?>
            <?php if ($result['success']): ?>
                <div class="alert alert-success">
                    <strong>&#10003; Success:</strong> <?= htmlspecialchars($result['message']) ?>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <strong>&#10007; Delivery Failed (Expected with dummy credentials):</strong><br>
                    <?= htmlspecialchars($result['message']) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST" action="smtp_test.php">
            <input type="hidden" name="action" value="test_send">
            <div class="field">
                <label for="test_to">Send Test Email To (Recipient(s), comma-separated):</label>
                <input type="text" id="test_to" name="test_to" value="<?= htmlspecialchars($config['to_email']) ?>" required>
                <div class="note">Configured recipients: <?= htmlspecialchars($config['to_email']) ?></div>
            </div>
            <div class="field">
                <label for="test_visitor_reply">Simulate Visitor Reply-To Address:</label>
                <input type="email" id="test_visitor_reply" name="test_visitor_reply" value="visitor@example.com" required>
                <div class="note">When you reply to the received email in your inbox, your reply will be addressed to this visitor email.</div>
            </div>
            <button type="submit" class="btn">Execute SMTP Diagnostic Test</button>
        </form>
    </div>
</body>
</html>
