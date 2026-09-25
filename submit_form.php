<?php
/**
 * EtechEdge Solutions - Form Submission Handler
 *
 * Receives POST data from website lead/contact/consultation forms,
 * validates user inputs, sends email to the 3 configured recipient addresses via Gmail SMTP,
 * and returns clean JSON without exposing credentials or internal errors.
 */

header('Content-Type: application/json; charset=utf-8');

// Strict error reporting internally, but suppress output
error_reporting(E_ALL);
ini_set('display_errors', '0');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

require_once __DIR__ . '/smtp_mailer.php';

// Support both form-urlencoded/FormData and raw JSON requests
$inputData = [];
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($contentType, 'application/json') !== false) {
    $rawInput = file_get_contents('php://input');
    $inputData = json_decode($rawInput, true) ?: [];
} else {
    $inputData = $_POST;
}

// Sanitize inputs
$name    = trim(strip_tags($inputData['name'] ?? ''));
$email   = filter_var(trim($inputData['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone   = trim(strip_tags($inputData['phone'] ?? ''));
$company = trim(strip_tags($inputData['company'] ?? ''));
$service = trim(strip_tags($inputData['service'] ?? ''));
$message = trim(strip_tags($inputData['message'] ?? $inputData['details'] ?? ''));
$source  = trim(strip_tags($inputData['form_source'] ?? 'Website Inquiry'));

// Basic Validation
if (empty($name)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please provide your full name.']);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
    exit;
}

// Construct clean HTML email
$subject = 'New Website Enquiry - EtechEdge (' . htmlspecialchars($name) . ')';

$htmlContent = '
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; background: #f8fafc; color: #1e293b; padding: 20px; }
  .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
  .email-header { background: #0868f7; color: #ffffff; padding: 24px 30px; }
  .email-header h2 { margin: 0; font-size: 20px; font-weight: 700; }
  .email-header p { margin: 6px 0 0; font-size: 13px; opacity: 0.9; }
  .email-body { padding: 30px; }
  .field-row { margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; }
  .field-row:last-child { border-bottom: none; }
  .field-label { font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px; margin-bottom: 4px; }
  .field-val { font-size: 15px; color: #0f172a; font-weight: 500; }
  .field-message { white-space: pre-wrap; background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px; color: #334155; line-height: 1.6; }
  .email-footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 30px; font-size: 12px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>
  <div class="email-container">
    <div class="email-header">
      <h2>New Website Enquiry</h2>
      <p>Source: ' . htmlspecialchars($source) . ' | Time: ' . date('d M Y, h:i A T') . '</p>
    </div>
    <div class="email-body">
      <div class="field-row">
        <div class="field-label">Name</div>
        <div class="field-val">' . htmlspecialchars($name) . '</div>
      </div>
      <div class="field-row">
        <div class="field-label">Email</div>
        <div class="field-val"><a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a></div>
      </div>
      ' . (!empty($phone) ? '<div class="field-row"><div class="field-label">Phone</div><div class="field-val">' . htmlspecialchars($phone) . '</div></div>' : '') . '
      ' . (!empty($company) ? '<div class="field-row"><div class="field-label">Company</div><div class="field-val">' . htmlspecialchars($company) . '</div></div>' : '') . '
      ' . (!empty($service) ? '<div class="field-row"><div class="field-label">Service / Interest Area</div><div class="field-val">' . htmlspecialchars($service) . '</div></div>' : '') . '
      ' . (!empty($message) ? '<div class="field-row"><div class="field-label">Project Details / Message</div><div class="field-message">' . nl2br(htmlspecialchars($message)) . '</div></div>' : '') . '
    </div>
    <div class="email-footer">
      Sent from EtechEdge Solutions Website &bull; Authenticated via Gmail SMTP
    </div>
  </div>
</body>
</html>';

$plainContent = "New Website Enquiry - EtechEdge\n"
              . "==============================\n"
              . "Name: $name\n"
              . "Email: $email\n"
              . (!empty($phone) ? "Phone: $phone\n" : "")
              . (!empty($company) ? "Company: $company\n" : "")
              . (!empty($service) ? "Service: $service\n" : "")
              . (!empty($message) ? "Message:\n$message\n" : "")
              . "\nSubmitted: " . date('Y-m-d H:i:s');

// Send through existing SMTP mailer
$result = sendEtechEdgeMail([
    'subject'    => $subject,
    'body_html'  => $htmlContent,
    'body_plain' => $plainContent,
    'reply_to'   => $email,
    'reply_name' => $name,
]);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your request has been recorded. Our team will contact you within 24 hours.',
    ]);
} else {
    // Log server-side error for debugging, but do not expose credentials
    error_log('SMTP Form Error: ' . ($result['message'] ?? 'Unknown error'));
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sorry, we were unable to process your request at this moment. Please try again or reach out to us at info@etechedge.com.',
    ]);
}
