<?php
require_once __DIR__ . '/config/db.php';
$user = requireAuth();

$db = getDB();

// Get user's comments count
$stmt = $db->prepare('SELECT COUNT(*) as cnt FROM comments WHERE user_id = ?');
$stmt->execute([$user['id']]);
$commentCount = $stmt->fetch()['cnt'];

// Get user's messages count
$stmt = $db->prepare('SELECT COUNT(*) as cnt FROM messages WHERE user_id = ?');
$stmt->execute([$user['id']]);
$messageCount = $stmt->fetch()['cnt'];

$pageTitle = 'Cabinet — ' . SITE_NAME;
$pageDesc = 'Your personal archive within Clan Lar.';

$extraCss = '
    .cabinet { max-width:680px; margin:0 auto; padding:0 1.5rem; }
    .cab-header { text-align:center; margin-bottom:3rem; }
    .cab-avatar { font-size:4rem; line-height:1; margin-bottom:1rem; display:block; }
    .cab-username { font-family:var(--font-display); font-size:clamp(1.2rem,2.5vw,1.6rem); letter-spacing:0.25em; text-transform:uppercase; color:var(--text-primary); }
    .cab-role { font-family:var(--font-label); font-size:0.55rem; letter-spacing:0.3em; text-transform:uppercase; color:var(--text-dim); margin-top:0.3rem; display:inline-block; padding:0.3em 1em; border:1px solid var(--border-card); }
    .cab-stats { display:flex; gap:2rem; justify-content:center; margin-top:1.5rem; }
    .cab-stat { text-align:center; }
    .cab-stat__value { font-family:var(--font-display); font-size:1.5rem; color:var(--text-primary); }
    .cab-stat__label { font-family:var(--font-label); font-size:0.5rem; letter-spacing:0.2em; text-transform:uppercase; color:var(--text-dim); margin-top:0.2rem; }
    .cab-section { margin-bottom:3rem; }
    .cab-section__title { font-family:var(--font-display); font-size:0.85rem; letter-spacing:0.3em; text-transform:uppercase; color:var(--text-secondary); margin-bottom:1.25rem; text-align:center; }
    .cab-nav { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:3rem; }
    .cab-nav__item { display:flex; flex-direction:column; align-items:center; gap:0.5rem; padding:1.5rem 1rem; border:1px solid var(--border-card); text-decoration:none; transition:all 0.3s ease; cursor:pointer; }
    .cab-nav__item:hover { border-color:rgba(160,160,170,0.2); transform:translateY(-2px); }
    .cab-nav__icon { font-size:1.5rem; }
    .cab-nav__label { font-family:var(--font-label); font-size:0.6rem; letter-spacing:0.2em; text-transform:uppercase; color:var(--text-secondary); }
    .cab-nav__sub { font-family:var(--font-body); font-size:0.8rem; color:var(--text-dim); font-style:italic; }
    .avatar-grid { display:flex; flex-wrap:wrap; gap:0.75rem; justify-content:center; margin:1rem 0; }
    .avatar-option { width:3rem; height:3rem; display:flex; align-items:center; justify-content:center; font-size:1.5rem; border:1px solid var(--border-card); cursor:pointer; transition:all 0.3s ease; }
    .avatar-option:hover, .avatar-option.selected { border-color:rgba(160,160,170,0.4); background:rgba(160,160,170,0.05); }
    @media(max-width:640px) { .cab-nav { grid-template-columns:1fr; } }
';

include __DIR__ . '/includes/header.php';
$avatars = avatarSymbols();
?>

    <div class="page-content">
      <div class="cabinet">

        <!-- HEADER -->
        <div class="cab-header">
          <div class="divider"><span>◆ — ◇ — ◆</span></div>
          <span class="cab-avatar"><?= getAvatarSymbol($user['avatar']) ?></span>
          <div class="cab-username"><?= e($user['display_name'] ?: $user['username']) ?></div>
          <div class="cab-role"><?= e($user['role']) ?></div>
          <?php if ($user['bio']): ?>
            <p style="font-family:var(--font-body); font-style:italic; color:var(--text-secondary); margin-top:1rem; max-width:400px; margin-left:auto; margin-right:auto;">
              <?= e($user['bio']) ?>
            </p>
          <?php endif; ?>
          <div class="cab-stats">
            <div class="cab-stat">
              <div class="cab-stat__value"><?= $commentCount ?></div>
              <div class="cab-stat__label">Comments</div>
            </div>
            <div class="cab-stat">
              <div class="cab-stat__value"><?= $messageCount ?></div>
              <div class="cab-stat__label">Messages</div>
            </div>
            <div class="cab-stat">
              <div class="cab-stat__value"><?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?></div>
              <div class="cab-stat__label">Joined</div>
            </div>
          </div>
          <div class="divider" style="margin-top:2rem;"><span>◇ — ◆ — ◇</span></div>
        </div>

        <!-- QUICK NAV -->
        <div class="cab-nav">
          <a href="contact.php" class="cab-nav__item">
            <span class="cab-nav__icon">✉</span>
            <span class="cab-nav__label">Contact Andrey</span>
            <span class="cab-nav__sub">Reviews, problems, creations</span>
          </a>
          <a href="news.php" class="cab-nav__item">
            <span class="cab-nav__icon">☙</span>
            <span class="cab-nav__label">News</span>
            <span class="cab-nav__sub">Latest from the Archives</span>
          </a>
          <a href="index.html" class="cab-nav__item">
            <span class="cab-nav__icon">◆</span>
            <span class="cab-nav__label">Houses</span>
            <span class="cab-nav__sub">Visit the portfolio</span>
          </a>
          <a href="#profile-section" class="cab-nav__item" onclick="document.getElementById('profile-section').scrollIntoView({behavior:'smooth'});return false;">
            <span class="cab-nav__icon">✧</span>
            <span class="cab-nav__label">Edit Profile</span>
            <span class="cab-nav__sub">Avatar, name, bio</span>
          </a>
        </div>

        <!-- PROFILE EDITOR -->
        <div class="cab-section" id="profile-section">
          <div class="cab-section__title">✧ &nbsp;Edit Profile</div>
          <div id="profileMsg"></div>
          <form id="profileForm">
            <?= csrfField() ?>

            <div class="form-group">
              <label class="form-label">Avatar</label>
              <div class="avatar-grid">
                <?php foreach ($avatars as $key => $symbol): ?>
                  <div class="avatar-option <?= $user['avatar'] === $key ? 'selected' : '' ?>"
                       data-avatar="<?= $key ?>" title="<?= $key ?>">
                    <?= $symbol ?>
                  </div>
                <?php endforeach; ?>
              </div>
              <input type="hidden" name="avatar" id="avatarInput" value="<?= e($user['avatar']) ?>">
            </div>

            <div class="form-group">
              <label class="form-label">Display Name</label>
              <input type="text" name="display_name" class="form-input" value="<?= e($user['display_name'] ?? '') ?>" maxlength="100" placeholder="<?= e($user['username']) ?>">
            </div>

            <div class="form-group">
              <label class="form-label">Bio</label>
              <textarea name="bio" class="form-textarea" maxlength="500" placeholder="A few words about yourself…"><?= e($user['bio'] ?? '') ?></textarea>
            </div>

            <div style="text-align:center; margin-top:1.5rem;">
              <button type="submit" class="btn btn--primary">Save Changes</button>
            </div>
          </form>
        </div>

        <!-- ACCOUNT INFO -->
        <div class="cab-section">
          <div class="cab-section__title">⌘ &nbsp;Account</div>
          <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
              <div>
                <div class="form-label" style="margin-bottom:0.25rem;">Username</div>
                <div style="font-family:var(--font-body); color:var(--text-primary);">@<?= e($user['username']) ?></div>
              </div>
              <div>
                <div class="form-label" style="margin-bottom:0.25rem;">Email</div>
                <div style="font-family:var(--font-body); color:var(--text-secondary);"><?= e($user['email']) ?></div>
              </div>
              <div>
                <div class="form-label" style="margin-bottom:0.25rem;">Status</div>
                <div style="font-family:var(--font-body); color:<?= $user['verified'] ? '#80c880' : '#e08080' ?>;">
                  <?= $user['verified'] ? '✓ Verified' : '✗ Unverified' ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div style="text-align:center; margin-top:2rem; padding-bottom:2rem;">
          <a href="api/logout.php" class="btn" style="color:var(--text-dim); border-color:rgba(160,160,170,0.08);">Logout</a>
        </div>

        <div class="divider"><span>◆ — ◇ — ◆</span></div>
      </div>
    </div>

<?php
$extraJs = '<script>
document.addEventListener("DOMContentLoaded", () => {
  // Avatar selection
  document.querySelectorAll(".avatar-option").forEach(opt => {
    opt.addEventListener("click", () => {
      document.querySelectorAll(".avatar-option").forEach(o => o.classList.remove("selected"));
      opt.classList.add("selected");
      document.getElementById("avatarInput").value = opt.dataset.avatar;
    });
  });

  // Profile form
  document.getElementById("profileForm")?.addEventListener("submit", async e => {
    e.preventDefault();
    const msg = document.getElementById("profileMsg");
    const fd = new FormData(e.target);
    try {
      const res = await fetch("api/profile.php", { method:"POST", body:fd, headers:{"X-Requested-With":"XMLHttpRequest"} });
      const data = await res.json();
      if (data.success) {
        msg.innerHTML = "<div class=\"alert alert--success\">" + data.message + "</div>";
        setTimeout(() => location.reload(), 1000);
      } else {
        msg.innerHTML = "<div class=\"alert alert--error\">" + (data.error || "Update failed") + "</div>";
      }
    } catch(err) { msg.innerHTML = "<div class=\"alert alert--error\">Connection error</div>"; }
  });
});
</script>';
include __DIR__ . '/includes/footer.php';
?>
