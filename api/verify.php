<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);
if (!verifyCsrf()) jsonResponse(['error' => 'Invalid token'], 403);

$code = trim($_POST['code'] ?? '');
$userId = $_SESSION['pending_user_id'] ?? null;

if (!$userId) jsonResponse(['error' => 'No pending verification. Please register first.'], 400);
if (strlen($code) !== 6) jsonResponse(['error' => 'Code must be 6 digits'], 400);

$db = getDB();

// Check code
$stmt = $db->prepare('SELECT id FROM verification_codes WHERE user_id = ? AND code = ? AND expires_at > NOW() AND used = 0 ORDER BY id DESC LIMIT 1');
$stmt->execute([$userId, $code]);
$vc = $stmt->fetch();

if (!$vc) jsonResponse(['error' => 'Invalid or expired code. Please request a new one.'], 400);

// Mark verified
$db->prepare('UPDATE users SET verified = 1 WHERE id = ?')->execute([$userId]);
$db->prepare('UPDATE verification_codes SET used = 1 WHERE id = ?')->execute([$vc['id']]);

// Log in
$_SESSION['user_id'] = $userId;
unset($_SESSION['pending_user_id'], $_SESSION['pending_email']);

// Update last login
$db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$userId]);

jsonResponse(['success' => true, 'message' => 'Email verified. Welcome to the Archives.', 'redirect' => 'cabinet.php']);
