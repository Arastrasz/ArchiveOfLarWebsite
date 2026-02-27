<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

$userId = $_SESSION['pending_user_id'] ?? null;
if (!$userId) jsonResponse(['error' => 'No pending verification'], 400);

$db = getDB();

// Rate limit: max 1 resend per 2 minutes
$stmt = $db->prepare('SELECT created_at FROM verification_codes WHERE user_id = ? ORDER BY id DESC LIMIT 1');
$stmt->execute([$userId]);
$last = $stmt->fetch();
if ($last) {
    $elapsed = time() - strtotime($last['created_at'] ?? '2000-01-01');
    if ($elapsed < 120) jsonResponse(['error' => 'Please wait ' . (120 - $elapsed) . ' seconds before requesting a new code'], 429);
}

// Get email
$stmt = $db->prepare('SELECT email FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) jsonResponse(['error' => 'User not found'], 404);

// New code
$code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));
$db->prepare('INSERT INTO verification_codes (user_id, code, expires_at) VALUES (?, ?, ?)')->execute([$userId, $code, $expires]);

$sent = sendVerificationEmail($user['email'], $code);

jsonResponse(['success' => true, 'message' => 'New code sent to ' . substr($user['email'], 0, 3) . '***', 'email_sent' => $sent]);
