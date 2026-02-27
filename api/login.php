<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);
if (!verifyCsrf()) jsonResponse(['error' => 'Invalid token'], 403);

$login = trim($_POST['login'] ?? '');  // Username or email
$password = $_POST['password'] ?? '';

if (!$login || !$password) jsonResponse(['error' => 'All fields are required'], 400);

$db = getDB();
$stmt = $db->prepare('SELECT id, username, password_hash, verified FROM users WHERE username = ? OR email = ?');
$stmt->execute([$login, $login]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    jsonResponse(['error' => 'Invalid credentials'], 401);
}

if (!$user['verified']) {
    // Re-send to verification
    $_SESSION['pending_user_id'] = $user['id'];
    jsonResponse(['error' => 'Email not verified', 'redirect' => 'login.php?step=verify'], 403);
}

$_SESSION['user_id'] = $user['id'];
$db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);

jsonResponse(['success' => true, 'message' => 'Welcome back, ' . $user['username'], 'redirect' => 'cabinet.php']);
