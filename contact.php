<?php
require_once __DIR__ . '/config/db.php';
$user = currentUser();

$pageTitle = 'Contact Andrey — ' . SITE_NAME;
$pageDesc = 'Send a message about reviews, problems, or visual creations.';

$extraCss = '
    .contact { max-width:560px; margin:0 auto; padding:0 1.5rem; }
    .contact-intro { text-align:center; margin-bottom:2.5rem; }
    .contact-intro p { font-family:var(--font-body); font-style:italic; color:var(--text-secondary); line-height:1.8; max-width:440px; margin:0 auto; }
    .category-cards { display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin:1.5rem 0; }
    .cat-card { padding:1rem; border:1px solid var(--border-card); cursor:pointer; text-align:center; transition:all 0.3s ease; }
    .cat-card:hover { border-color:rgba(160,160,170,0.2); }
    .cat-card.selected { border-color:rgba(160,160,170,0.4); background:rgba(160,160,170,0.04); }
    .cat-card__icon { font-size:1.2rem; display:block; margin-bottom:0.4rem; }
    .cat-card__label { font-family:var(--font-label); font-size:0.55rem; letter-spacing:0.2em; text-transform:uppercase; color:var(--text-secondary); }
    .cat-card__desc { font-family:var(--font-body); font-size:0.75rem; color:var(--text-dim); font-style:italic; margin-top:0.2rem; }
    @media(max-width:480px) { .category-cards { grid-template-columns:1fr; } }
    .sent-confirmation { text-align:center; padding:3rem 1rem; display:none; }
    .sent-confirmation.show { display:block; }
    .sent-icon { font-size:3rem; margin-bottom:1rem; display:block; }
';

include __DIR__ . '/includes/header.php';
?>

    <div class="page-content">
      <div class="contact">

        <div class="contact-intro">
          <div class="divider"><span>◆ — ◇ — ◆</span></div>
          <h1 class="section-title" style="font-size:clamp(1.2rem,2.5vw,1.6rem);">Contact Andrey</h1>
          <div class="divider"><span>◇ — ◆ — ◇</span></div>
          <p style="margin-top:1.5rem;">
            Questions about builds, bug reports, visual creation requests, or anything else — it all comes here.
            Messages go directly to the admin inbox and email.
          </p>
        </div>

        <div id="contactMsg"></div>

        <div id="contactFormWrap">
          <form id="contactForm">
            <?= csrfField() ?>

            <!-- Category selector -->
            <div class="form-group">
              <label class="form-label">What's this about?</label>
              <div class="category-cards" id="catCards">
                <div class="cat-card" data-cat="review">
                  <span class="cat-card__icon">☙</span>
                  <span class="cat-card__label">Review</span>
                  <span class="cat-card__desc">Feedback on a house</span>
                </div>
                <div class="cat-card" data-cat="problem">
                  <span class="cat-card__icon">⚠</span>
                  <span class="cat-card__label">Problem</span>
                  <span class="cat-card__desc">Bug or site issue</span>
                </div>
                <div class="cat-card" data-cat="visual_creation">
                  <span class="cat-card__icon">✦</span>
                  <span class="cat-card__label">Visual Creation</span>
                  <span class="cat-card__desc">Build or design request</span>
                </div>
                <div class="cat-card" data-cat="collaboration">
                  <span class="cat-card__icon">◇</span>
                  <span class="cat-card__label">Collaboration</span>
                  <span class="cat-card__desc">Work together</span>
                </div>
                <div class="cat-card" data-cat="other" style="grid-column:1/-1;">
                  <span class="cat-card__icon">◆</span>
                  <span class="cat-card__label">Other</span>
                  <span class="cat-card__desc">Anything else</span>
                </div>
              </div>
              <input type="hidden" name="category" id="catInput" value="" required>
            </div>

            <?php if (!$user): ?>
            <!-- Guest fields -->
            <div class="form-group">
              <label class="form-label">Your Name</label>
              <input type="text" name="name" class="form-input" required maxlength="100">
            </div>
            <div class="form-group">
              <label class="form-label">Your Email</label>
              <input type="email" name="email" class="form-input" required>
            </div>
            <?php endif; ?>

            <div class="form-group">
              <label class="form-label">Subject</label>
              <input type="text" name="subject" class="form-input" required minlength="3" maxlength="255" placeholder="Brief subject line">
            </div>

            <div class="form-group">
              <label class="form-label">Message</label>
              <textarea name="content" class="form-textarea" required minlength="10" placeholder="Write your message here…" style="min-height:160px;"></textarea>
            </div>

            <div style="text-align:center; margin-top:2rem;">
              <button type="submit" class="btn btn--primary">Send Message →</button>
            </div>
          </form>
        </div>

        <!-- Success state -->
        <div class="sent-confirmation" id="sentConfirm">
          <span class="sent-icon">✓</span>
          <h2 class="section-title" style="font-size:1.2rem;">Message Sent</h2>
          <p style="font-family:var(--font-body); font-style:italic; color:var(--text-secondary); margin-top:1rem;">
            Andrey will receive your message at the admin inbox and via email.
          </p>
          <div style="margin-top:2rem;">
            <a href="cabinet.php" class="btn">← Back to Cabinet</a>
          </div>
        </div>

        <div class="divider" style="margin-top:3rem;"><span>◆ — ◇ — ◆</span></div>
      </div>
    </div>

<?php
$extraJs = '<script>
document.addEventListener("DOMContentLoaded", () => {
  // Category selection
  const cards = document.querySelectorAll(".cat-card");
  const catInput = document.getElementById("catInput");
  cards.forEach(c => {
    c.addEventListener("click", () => {
      cards.forEach(x => x.classList.remove("selected"));
      c.classList.add("selected");
      catInput.value = c.dataset.cat;
    });
  });

  // Submit
  document.getElementById("contactForm")?.addEventListener("submit", async e => {
    e.preventDefault();
    if (!catInput.value) {
      document.getElementById("contactMsg").innerHTML = "<div class=\"alert alert--error\">Please select a category</div>";
      return;
    }
    const fd = new FormData(e.target);
    const msg = document.getElementById("contactMsg");
    try {
      const res = await fetch("api/contact.php", { method:"POST", body:fd, headers:{"X-Requested-With":"XMLHttpRequest"} });
      const data = await res.json();
      if (data.success) {
        document.getElementById("contactFormWrap").style.display = "none";
        document.getElementById("sentConfirm").classList.add("show");
      } else {
        msg.innerHTML = "<div class=\"alert alert--error\">" + (data.error || "Send failed") + "</div>";
      }
    } catch(err) { msg.innerHTML = "<div class=\"alert alert--error\">Connection error. Try again.</div>"; }
  });
});
</script>';
include __DIR__ . '/includes/footer.php';
?>
