<?php
$pageTitle = $pageTitle ?? SITE_NAME;
$pageDesc = $pageDesc ?? 'ESO Housing portfolio by @Vaelarn. PC–EU.';
$pageAccent = $pageAccent ?? 'rgba(160,160,170,0.4)';
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDesc) ?>">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDesc) ?>">
  <meta property="og:type" content="website">
  <meta name="theme-color" content="#060608">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&family=IBM+Plex+Mono:wght@300;400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>◆</text></svg>">
  <style>
    :root { --house-accent: <?= $pageAccent ?>; }
    .page-content { padding-top: 5rem; min-height: 80vh; }
    .form-group { margin-bottom: 1.5rem; }
    .form-label { display:block; font-family:var(--font-label); font-size:0.6rem; letter-spacing:0.25em; text-transform:uppercase; color:var(--text-dim); margin-bottom:0.5rem; }
    .form-input, .form-select, .form-textarea {
      width:100%; padding:0.75rem 1rem; background:rgba(16,16,20,0.8); border:1px solid var(--border-card);
      color:var(--text-primary); font-family:var(--font-body); font-size:0.95rem; outline:none;
      transition:border-color 0.3s ease;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus { border-color:rgba(160,160,170,0.3); }
    .form-textarea { min-height:120px; resize:vertical; line-height:1.7; }
    .form-select { cursor:pointer; }
    .form-select option { background:#0a0a0c; }
    .btn {
      display:inline-block; font-family:var(--font-label); font-size:0.6rem; letter-spacing:0.3em; text-transform:uppercase;
      padding:0.7em 2.5em; border:1px solid rgba(160,160,170,0.2); background:transparent; color:var(--text-secondary);
      cursor:pointer; transition:all 0.4s ease; text-decoration:none;
    }
    .btn:hover { border-color:rgba(160,160,170,0.5); color:var(--text-primary); transform:translateY(-1px); }
    .btn--primary { border-color:var(--house-accent); color:var(--text-primary); }
    .btn--primary:hover { background:rgba(160,160,170,0.05); }
    .alert { padding:1rem 1.5rem; margin-bottom:1.5rem; font-family:var(--font-body); font-size:0.9rem; border:1px solid; }
    .alert--error { border-color:rgba(200,80,80,0.3); color:#e08080; background:rgba(200,80,80,0.05); }
    .alert--success { border-color:rgba(80,200,120,0.3); color:#80c880; background:rgba(80,200,120,0.05); }
    .alert--info { border-color:rgba(100,160,200,0.3); color:#80b8d0; background:rgba(100,160,200,0.05); }
    .user-badge {
      display:inline-flex; align-items:center; gap:0.5rem;
      font-family:var(--font-label); font-size:0.6rem; letter-spacing:0.15em; color:var(--text-secondary);
    }
    .user-badge__avatar { font-size:1rem; }
    .card {
      background:rgba(12,12,16,0.6); border:1px solid var(--border-card); padding:2rem;
      transition:border-color 0.3s ease;
    }
    .card:hover { border-color:rgba(160,160,170,0.12); }
    <?php if (isset($extraCss)) echo $extraCss; ?>
  </style>
</head>
<body>
  <div class="loader" id="loader">
    <div class="loader__ornament">◆ — ◇ — ◆</div>
    <div class="loader__brand">Clan Lar</div>
  </div>
  <div class="page-wrapper">
    <nav class="nav" id="nav">
      <a href="index.html" class="nav-brand">◆ Clan Lar</a>
      <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation"><span></span><span></span><span></span></button>
      <ul class="nav-links" id="navLinks">
        <li><a href="index.html">Houses</a></li>
        <li><a href="news.php">News</a></li>
        <?php if ($user): ?>
          <li><a href="cabinet.php">Cabinet</a></li>
          <li><a href="api/logout.php">Logout</a></li>
        <?php else: ?>
          <li><a href="login.php">Enter</a></li>
        <?php endif; ?>
      </ul>
      <button class="nav__search" id="searchBtn">⌕ Search</button>
    </nav>
