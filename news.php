<?php
require_once __DIR__ . '/config/db.php';

$db = getDB();
$user = currentUser();

// Single post or list?
$slug = $_GET['post'] ?? null;

if ($slug) {
    $stmt = $db->prepare('SELECT n.*, u.username, u.display_name, u.avatar FROM news n JOIN users u ON n.author_id = u.id WHERE n.slug = ? AND n.published = 1');
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
    if (!$post) { header('Location: news.php'); exit; }
    $pageTitle = e($post['title']) . ' — ' . SITE_NAME;
    
    // Load comments
    $stmt = $db->prepare('SELECT c.*, u.username, u.display_name, u.avatar FROM comments c JOIN users u ON c.user_id = u.id WHERE c.news_id = ? ORDER BY c.created_at ASC');
    $stmt->execute([$post['id']]);
    $comments = $stmt->fetchAll();
} else {
    $pageTitle = 'News — ' . SITE_NAME;
    $stmt = $db->prepare('SELECT n.*, u.username, u.display_name FROM news n JOIN users u ON n.author_id = u.id WHERE n.published = 1 ORDER BY n.created_at DESC');
    $stmt->execute();
    $posts = $stmt->fetchAll();
}

$extraCss = '
    .news-list { max-width:var(--content-width); margin:0 auto; padding:0 1.5rem; }
    .news-card { display:block; text-decoration:none; margin-bottom:2rem; border:1px solid var(--border-card); overflow:hidden; transition:border-color 0.3s; }
    .news-card:hover { border-color:rgba(160,160,170,0.15); }
    .news-card__image { width:100%; height:200px; object-fit:cover; filter:brightness(0.5) saturate(0.6); transition:filter 0.4s; }
    .news-card:hover .news-card__image { filter:brightness(0.65) saturate(0.7); }
    .news-card__body { padding:1.5rem; }
    .news-card__title { font-family:var(--font-display); font-size:1.2rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--text-primary); margin-bottom:0.5rem; }
    .news-card__excerpt { font-family:var(--font-body); font-style:italic; color:var(--text-secondary); line-height:1.7; }
    .news-card__meta { font-family:var(--font-label); font-size:0.55rem; letter-spacing:0.2em; color:var(--text-dim); margin-top:0.75rem; text-transform:uppercase; }
    .post-single { max-width:var(--content-width); margin:0 auto; padding:0 1.5rem; }
    .post-hero { position:relative; overflow:hidden; margin:-5rem -1.5rem 2rem; }
    .post-hero img { width:100%; height:350px; object-fit:cover; filter:brightness(0.4) saturate(0.5); }
    .post-hero::after { content:""; position:absolute; inset:0; background:linear-gradient(to top, var(--bg-void), transparent 50%); }
    .post-hero__title { position:absolute; bottom:2rem; left:2rem; right:2rem; z-index:1;
      font-family:var(--font-display); font-size:clamp(1.3rem,3vw,2rem); letter-spacing:0.2em; text-transform:uppercase; color:var(--text-primary); }
    .post-content { font-family:var(--font-body); font-size:1.05rem; line-height:1.9; color:var(--text-secondary); }
    .post-content p { margin-bottom:1.25rem; }
    .post-content em { color:var(--text-primary); }
    .post-content strong { color:var(--text-primary); font-weight:500; }
    .post-meta { font-family:var(--font-label); font-size:0.55rem; letter-spacing:0.2em; color:var(--text-dim); text-transform:uppercase; margin-bottom:2rem; }
    .comments-section { margin-top:4rem; border-top:1px solid var(--border-card); padding-top:2rem; }
    .comment { display:flex; gap:1rem; padding:1.25rem 0; border-bottom:1px solid rgba(160,160,170,0.04); }
    .comment__avatar { font-size:1.5rem; line-height:1; flex-shrink:0; margin-top:0.2rem; }
    .comment__body { flex:1; min-width:0; }
    .comment__header { display:flex; align-items:baseline; gap:0.75rem; margin-bottom:0.4rem; }
    .comment__name { font-family:var(--font-label); font-size:0.6rem; letter-spacing:0.15em; color:var(--text-primary); text-transform:uppercase; }
    .comment__time { font-family:var(--font-label); font-size:0.5rem; letter-spacing:0.1em; color:var(--text-dim); }
    .comment__text { font-family:var(--font-body); font-size:0.95rem; color:var(--text-secondary); line-height:1.7; }
    .comment-form { margin-top:1.5rem; }
    .comment-form textarea { min-height:80px; }
    .no-comments { text-align:center; padding:2rem; font-family:var(--font-body); font-style:italic; color:var(--text-dim); }
';

include __DIR__ . '/includes/header.php';
?>

    <div class="page-content">
    <?php if ($slug && $post): ?>
      <!-- SINGLE POST -->
      <div class="post-single">
        <?php if ($post['image']): ?>
        <div class="post-hero">
          <img src="<?= e($post['image']) ?>" alt="<?= e($post['title']) ?>">
          <h1 class="post-hero__title"><?= e($post['title']) ?></h1>
        </div>
        <?php else: ?>
        <h1 class="section-title" style="text-align:center; margin-bottom:1rem;"><?= e($post['title']) ?></h1>
        <?php endif; ?>

        <div class="post-meta">
          <?= e($post['display_name'] ?: $post['username']) ?> · <?= date('j M Y', strtotime($post['created_at'])) ?>
        </div>

        <div class="post-content">
          <?= $post['content'] ?>
        </div>

        <!-- COMMENTS -->
        <div class="comments-section">
          <h2 class="section-title" style="font-size:1rem; text-align:center;">
            Comments <span style="color:var(--text-dim);">(<?= count($comments) ?>)</span>
          </h2>
          <div class="divider"><span>◇ — ◆ — ◇</span></div>

          <div id="commentsList">
            <?php if (empty($comments)): ?>
              <div class="no-comments" id="noComments">No comments yet. Be the first.</div>
            <?php else: ?>
              <?php foreach ($comments as $c): ?>
              <div class="comment">
                <div class="comment__avatar"><?= getAvatarSymbol($c['avatar']) ?></div>
                <div class="comment__body">
                  <div class="comment__header">
                    <span class="comment__name"><?= e($c['display_name'] ?: $c['username']) ?></span>
                    <span class="comment__time"><?= timeAgo($c['created_at']) ?></span>
                  </div>
                  <div class="comment__text"><?= nl2br(e($c['content'])) ?></div>
                </div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <?php if ($user): ?>
          <form class="comment-form" id="commentForm">
            <?= csrfField() ?>
            <input type="hidden" name="news_id" value="<?= $post['id'] ?>">
            <div class="form-group">
              <textarea name="content" class="form-textarea" placeholder="Write a comment…" maxlength="2000" required></textarea>
            </div>
            <button type="submit" class="btn btn--primary">Post Comment</button>
          </form>
          <?php else: ?>
          <div style="text-align:center; margin-top:1.5rem;">
            <a href="login.php" class="btn">Login to Comment →</a>
          </div>
          <?php endif; ?>
        </div>
      </div>

    <?php else: ?>
      <!-- NEWS LIST -->
      <div class="news-list">
        <div style="text-align:center; margin-bottom:3rem;">
          <div class="divider"><span>◆ — ◇ — ◆</span></div>
          <h1 class="section-title">News</h1>
          <div class="divider"><span>◇ — ◆ — ◇</span></div>
        </div>

        <?php if (empty($posts)): ?>
          <p style="text-align:center; color:var(--text-dim); font-family:var(--font-body); font-style:italic;">No news yet.</p>
        <?php else: ?>
          <?php foreach ($posts as $p): ?>
          <a href="news.php?post=<?= e($p['slug']) ?>" class="news-card">
            <?php if ($p['image']): ?>
              <img class="news-card__image" src="<?= e($p['image']) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
            <?php endif; ?>
            <div class="news-card__body">
              <div class="news-card__title"><?= e($p['title']) ?></div>
              <div class="news-card__excerpt"><?= e($p['excerpt'] ?: substr(strip_tags($p['content']), 0, 200) . '…') ?></div>
              <div class="news-card__meta">
                <?= e($p['display_name'] ?: $p['username']) ?> · <?= date('j M Y', strtotime($p['created_at'])) ?>
              </div>
            </div>
          </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    </div>

<?php
$extraJs = '<script>
document.getElementById("commentForm")?.addEventListener("submit", async e => {
  e.preventDefault();
  const form = e.target;
  const fd = new FormData(form);
  try {
    const res = await fetch("api/comment.php", { method: "POST", body: fd, headers: {"X-Requested-With":"XMLHttpRequest"} });
    const data = await res.json();
    if (data.success) {
      const c = data.comment;
      document.getElementById("noComments")?.remove();
      document.getElementById("commentsList").insertAdjacentHTML("beforeend",
        `<div class="comment"><div class="comment__avatar">${c.avatar}</div><div class="comment__body"><div class="comment__header"><span class="comment__name">${c.display_name}</span><span class="comment__time">${c.time}</span></div><div class="comment__text">${c.content}</div></div></div>`
      );
      form.querySelector("textarea").value = "";
    } else { alert(data.error); }
  } catch(e) { alert("Failed to post comment"); }
});
</script>';
include __DIR__ . '/includes/footer.php';
?>
