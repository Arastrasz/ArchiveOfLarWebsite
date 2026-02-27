<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);
if (!verifyCsrf()) jsonResponse(['error' => 'Invalid token'], 403);

$user = requireAuth();

$displayName = trim($_POST['display_name'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$avatar = $_POST['avatar'] ?? $user['avatar'];

// Validation
if ($displayName && strlen($displayName) > 100) jsonResponse(['error' => 'Display name too long'], 400);
if (strlen($bio) > 500) jsonResponse(['error' => 'Bio must be under 500 characters'], 400);

$validAvatars = array_keys(avatarSymbols());
if (!in_array($avatar, $validAvatars)) $avatar = 'default';

$db = getDB();
$stmt = $db->prepare('UPDATE users SET display_name = ?, bio = ?, avatar = ? WHERE id = ?');
$stmt->execute([$displayName ?: null, $bio ?: null, $avatar, $user['id']]);

jsonResponse(['success' => true, 'message' => 'Profile updated']);
