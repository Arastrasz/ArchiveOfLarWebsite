<?php
/* ============================================================
   THE ARCHIVES OF CLAN LAR — Configuration
   ============================================================ */

// --- Database ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'arkh574q_andrey');      // Change to your phpMyAdmin DB name
define('DB_USER', 'root');             // Change to your DB user
define('DB_PASS', 'JoneRinaldo34');                 // Change to your DB password

// --- Site ---
define('SITE_NAME', 'The Archives of Clan Lar');
define('SITE_URL', 'https://andreykuznetcoveso.com');  // Change to your domain
define('ADMIN_EMAIL', 'support@andreykuznetcoveso.com');

// --- Email ---
define('SMTP_FROM', 'noreply@andreykuznetcoveso.com');
define('SMTP_FROM_NAME', 'Clan Lar Archives');

// --- Session ---
session_start();

// --- Database connection ---
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
    return $pdo;
}

// --- Auth helpers ---
function currentUser(): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    static $user = null;
    if ($user === null) {
        $stmt = getDB()->prepare('SELECT id, username, email, display_name, avatar, bio, role, verified, created_at FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function requireAuth(): array {
    $user = currentUser();
    if (!$user) {
        if (isAjax()) {
            http_response_code(401);
            die(json_encode(['error' => 'Not authenticated']));
        }
        header('Location: login.php');
        exit;
    }
    return $user;
}

function requireAdmin(): array {
    $user = requireAuth();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        die(json_encode(['error' => 'Admin access required']));
    }
    return $user;
}

function isAjax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// --- CSRF ---
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

function verifyCsrf(): bool {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals(csrfToken(), $token);
}

// --- Sanitization ---
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function sanitizeHtml(string $html): string {
    $allowed = '<p><br><em><strong><a><ul><ol><li><blockquote>';
    return strip_tags($html, $allowed);
}

// --- Email ---
function sendEmail(string $to, string $subject, string $htmlBody): bool {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    return mail($to, $subject, $htmlBody, $headers);
}

function sendVerificationEmail(string $email, string $code): bool {
    $subject = "Verify your account — " . SITE_NAME;
    $body = emailTemplate("
        <h2 style='color:#e0e0e2;font-family:Georgia,serif;'>Verify Your Account</h2>
        <p style='color:#a0a0a5;'>Your verification code is:</p>
        <div style='text-align:center;margin:2rem 0;'>
            <span style='font-size:2rem;letter-spacing:0.5em;color:#e0e0e2;font-family:monospace;'>{$code}</span>
        </div>
        <p style='color:#a0a0a5;font-size:0.9rem;'>This code expires in 30 minutes.</p>
    ");
    return sendEmail($email, $subject, $body);
}

function emailTemplate(string $content): string {
    return "
    <div style='max-width:500px;margin:0 auto;padding:2rem;background:#0a0a0c;font-family:Georgia,serif;'>
        <div style='text-align:center;margin-bottom:2rem;'>
            <span style='color:#555;font-size:0.7rem;letter-spacing:0.4em;'>◆ — ◇ — ◆</span>
        </div>
        {$content}
        <div style='text-align:center;margin-top:2rem;border-top:1px solid #1a1a1e;padding-top:1.5rem;'>
            <span style='color:#555;font-size:0.65rem;letter-spacing:0.3em;'>THE ARCHIVES OF CLAN LAR</span>
        </div>
    </div>";
}

// --- JSON response ---
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// --- Time formatting ---
function timeAgo(string $datetime): string {
    $now = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->diff($then);
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}

// --- Avatar options ---
function avatarSymbols(): array {
    return [
        'default' => '◆',
        'crimson' => '✠',
        'ayleid'  => '⌘',
        'wyrd'    => '❧',
        'sheoth'  => '✦',
        'scroll'  => '☙',
        'star'    => '✧',
        'crown'   => '♛',
    ];
}

function getAvatarSymbol(string $key): string {
    $symbols = avatarSymbols();
    return $symbols[$key] ?? $symbols['default'];
}
