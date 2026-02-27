<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);
if (!verifyCsrf()) jsonResponse(['error' => 'Invalid token'], 403);

$user = currentUser();

$category = $_POST['category'] ?? 'other';
$subject = trim($_POST['subject'] ?? '');
$content = trim($_POST['content'] ?? '');
$name = $user ? ($user['display_name'] ?: $user['username']) : trim($_POST['name'] ?? '');
$email = $user ? $user['email'] : trim($_POST['email'] ?? '');

// Validation
$validCats = ['review', 'problem', 'visual_creation', 'collaboration', 'other'];
if (!in_array($category, $validCats)) jsonResponse(['error' => 'Invalid category'], 400);
if (strlen($subject) < 3 || strlen($subject) > 255) jsonResponse(['error' => 'Subject must be 3–255 characters'], 400);
if (strlen($content) < 10) jsonResponse(['error' => 'Message must be at least 10 characters'], 400);
if (!$name) jsonResponse(['error' => 'Name is required'], 400);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResponse(['error' => 'Valid email is required'], 400);

$db = getDB();

// Save to database
$stmt = $db->prepare('INSERT INTO messages (user_id, name, email, category, subject, content) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->execute([
    $user ? $user['id'] : null,
    e($name),
    $email,
    $category,
    e($subject),
    e($content)
]);

$msgId = $db->lastInsertId();

// Category labels
$catLabels = [
    'review' => 'House Review',
    'problem' => 'Bug / Problem Report',
    'visual_creation' => 'Visual Creation Request',
    'collaboration' => 'Collaboration',
    'other' => 'Other',
];
$catLabel = $catLabels[$category] ?? 'Other';

// 1. Send email to external address
$emailBody = emailTemplate("
    <h2 style='color:#e0e0e2;font-family:Georgia,serif;'>New Message: {$catLabel}</h2>
    <table style='width:100%;border-collapse:collapse;margin:1rem 0;'>
        <tr><td style='color:#666;padding:0.5rem 0;font-size:0.85rem;'>From</td><td style='color:#e0e0e2;padding:0.5rem 0;'>" . e($name) . " &lt;" . e($email) . "&gt;</td></tr>
        <tr><td style='color:#666;padding:0.5rem 0;font-size:0.85rem;'>Category</td><td style='color:#e0e0e2;padding:0.5rem 0;'>{$catLabel}</td></tr>
        <tr><td style='color:#666;padding:0.5rem 0;font-size:0.85rem;'>Subject</td><td style='color:#e0e0e2;padding:0.5rem 0;'>" . e($subject) . "</td></tr>
    </table>
    <div style='background:#111;padding:1.5rem;margin:1rem 0;border-left:2px solid #333;'>
        <p style='color:#c0c0c4;white-space:pre-wrap;'>" . e($content) . "</p>
    </div>
    <p style='color:#666;font-size:0.8rem;'>Message ID: #{$msgId}</p>
");
$emailSent = sendEmail(ADMIN_EMAIL, "[Clan Lar] {$catLabel}: " . e($subject), $emailBody);

// 2. Also flag as unread for admin in DB (they see it in cabinet)

jsonResponse([
    'success' => true,
    'message' => 'Message sent. Andrey will receive it shortly.',
    'email_sent' => $emailSent
]);
