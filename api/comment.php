<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);
if (!verifyCsrf()) jsonResponse(['error' => 'Invalid token'], 403);

$user = requireAuth();

$newsId = (int)($_POST['news_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if (!$newsId) jsonResponse(['error' => 'Invalid news post'], 400);
if (strlen($content) < 2 || strlen($content) > 2000) jsonResponse(['error' => 'Comment must be 2–2000 characters'], 400);

$db = getDB();

// Verify news exists
$stmt = $db->prepare('SELECT id FROM news WHERE id = ? AND published = 1');
$stmt->execute([$newsId]);
if (!$stmt->fetch()) jsonResponse(['error' => 'News post not found'], 404);

// Rate limit: max 5 comments per minute
$stmt = $db->prepare('SELECT COUNT(*) as cnt FROM comments WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)');
$stmt->execute([$user['id']]);
if ($stmt->fetch()['cnt'] >= 5) jsonResponse(['error' => 'Too many comments. Wait a moment.'], 429);

// Insert
$stmt = $db->prepare('INSERT INTO comments (news_id, user_id, content) VALUES (?, ?, ?)');
$stmt->execute([$newsId, $user['id'], e($content)]);

jsonResponse([
    'success' => true,
    'comment' => [
        'id' => $db->lastInsertId(),
        'username' => $user['username'],
        'display_name' => $user['display_name'] ?: $user['username'],
        'avatar' => getAvatarSymbol($user['avatar']),
        'content' => e($content),
        'time' => 'just now'
    ]
]);
