<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);
if (!verifyCsrf()) jsonResponse(['error' => 'Invalid token'], 403);

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['password_confirm'] ?? '';

// Validation
$errors = [];
if (strlen($username) < 3 || strlen($username) > 50) $errors[] = 'Username must be 3–50 characters';
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) $errors[] = 'Username: letters, numbers, hyphens, underscores only';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address';
if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters';
if ($password !== $confirm) $errors[] = 'Passwords do not match';

if ($errors) jsonResponse(['errors' => $errors], 400);

$db = getDB();

// Check uniqueness
$stmt = $db->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
$stmt->execute([$username, $email]);
if ($stmt->fetch()) jsonResponse(['errors' => ['Username or email already registered']], 409);

// Create user
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
$stmt = $db->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
$stmt->execute([$username, $email, $hash]);
$userId = $db->lastInsertId();

// Generate verification code
$code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));
$stmt = $db->prepare('INSERT INTO verification_codes (user_id, code, expires_at) VALUES (?, ?, ?)');
$stmt->execute([$userId, $code, $expires]);

// Send verification email
$sent = sendVerificationEmail($email, $code);

$_SESSION['pending_user_id'] = $userId;
$_SESSION['pending_email'] = $email;

jsonResponse([
    'success' => true,
    'message' => 'Account created. Check your email for the verification code.',
    'email_sent' => $sent,
    'redirect' => 'login.php?step=verify'
]);
