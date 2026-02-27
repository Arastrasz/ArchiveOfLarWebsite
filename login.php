<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Enter the Archives — ' . SITE_NAME;
$pageDesc = 'Login or register to join the Archives of Clan Lar.';
$extraCss = '
    .auth-container { max-width:420px; margin:3rem auto; padding:0 1.5rem; }
    .auth-tabs { display:flex; gap:0; margin-bottom:2rem; border-bottom:1px solid var(--border-card); }
    .auth-tab { flex:1; text-align:center; padding:0.75rem; font-family:var(--font-label); font-size:0.6rem;
      letter-spacing:0.25em; text-transform:uppercase; color:var(--text-dim); cursor:pointer;
      border-bottom:2px solid transparent; transition:all 0.3s ease; background:none; border-top:none; border-left:none; border-right:none; }
    .auth-tab.active { color:var(--text-primary); border-bottom-color:var(--text-secondary); }
    .auth-panel { display:none; }
    .auth-panel.active { display:block; }
    .verify-code-inputs { display:flex; gap:0.5rem; justify-content:center; margin:1.5rem 0; }
    .verify-code-inputs input { width:3rem; height:3.5rem; text-align:center; font-family:var(--font-label);
      font-size:1.5rem; letter-spacing:0; background:rgba(16,16,20,0.8); border:1px solid var(--border-card);
      color:var(--text-primary); outline:none; transition:border-color 0.3s; }
    .verify-code-inputs input:focus { border-color:rgba(160,160,170,0.4); }
    .auth-divider { text-align:center; padding:1.5rem 0; color:var(--text-dim); font-family:var(--font-label); font-size:0.55rem; letter-spacing:0.3em; }
    .resend-link { background:none; border:none; color:var(--text-dim); font-family:var(--font-body); font-size:0.85rem;
      font-style:italic; cursor:pointer; text-decoration:underline; transition:color 0.3s; }
    .resend-link:hover { color:var(--text-secondary); }
    #formMessages { min-height:1rem; }
';
include __DIR__ . '/includes/header.php';

$step = $_GET['step'] ?? 'login';
?>

    <div class="page-content">
      <div class="auth-container">
        <div style="text-align:center; margin-bottom:2rem;">
          <div class="divider"><span>◆ — ◇ — ◆</span></div>
          <h1 class="section-title" style="font-size:clamp(1.2rem,2.5vw,1.6rem);">Enter the Archives</h1>
          <div class="divider"><span>◇ — ◆ — ◇</span></div>
        </div>

        <div id="formMessages"></div>

        <!-- Tabs -->
        <div class="auth-tabs" id="authTabs">
          <button class="auth-tab <?= $step === 'login' ? 'active' : '' ?>" data-tab="login">Login</button>
          <button class="auth-tab <?= $step === 'register' ? 'active' : '' ?>" data-tab="register">Register</button>
          <button class="auth-tab <?= $step === 'verify' ? 'active' : '' ?>" data-tab="verify">Verify</button>
        </div>

        <!-- LOGIN -->
        <div class="auth-panel <?= $step === 'login' ? 'active' : '' ?>" id="panel-login">
          <form id="loginForm">
            <?= csrfField() ?>
            <div class="form-group">
              <label class="form-label">Username or Email</label>
              <input type="text" name="login" class="form-input" required autocomplete="username">
            </div>
            <div class="form-group">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-input" required autocomplete="current-password">
            </div>
            <div style="text-align:center; margin-top:2rem;">
              <button type="submit" class="btn btn--primary">Enter →</button>
            </div>
          </form>
        </div>

        <!-- REGISTER -->
        <div class="auth-panel <?= $step === 'register' ? 'active' : '' ?>" id="panel-register">
          <form id="registerForm">
            <?= csrfField() ?>
            <div class="form-group">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-input" required minlength="3" maxlength="50" autocomplete="username" pattern="[a-zA-Z0-9_-]+">
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-input" required autocomplete="email">
            </div>
            <div class="form-group">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-input" required minlength="8" autocomplete="new-password">
            </div>
            <div class="form-group">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="password_confirm" class="form-input" required autocomplete="new-password">
            </div>
            <div style="text-align:center; margin-top:2rem;">
              <button type="submit" class="btn btn--primary">Register →</button>
            </div>
          </form>
        </div>

        <!-- VERIFY -->
        <div class="auth-panel <?= $step === 'verify' ? 'active' : '' ?>" id="panel-verify">
          <div style="text-align:center;">
            <p style="font-family:var(--font-body); color:var(--text-secondary); font-style:italic; margin-bottom:0.5rem;">
              A verification code was sent to your email.
            </p>
            <?php if (!empty($_SESSION['pending_email'])): ?>
              <p style="font-family:var(--font-label); font-size:0.6rem; letter-spacing:0.15em; color:var(--text-dim);">
                <?= e(substr($_SESSION['pending_email'], 0, 3)) ?>***
              </p>
            <?php endif; ?>
          </div>
          <form id="verifyForm">
            <?= csrfField() ?>
            <div class="verify-code-inputs" id="codeInputs">
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
              <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" required>
            </div>
            <input type="hidden" name="code" id="codeHidden">
            <div style="text-align:center;">
              <button type="submit" class="btn btn--primary">Verify →</button>
            </div>
          </form>
          <div style="text-align:center; margin-top:1.5rem;">
            <button class="resend-link" id="resendBtn">Didn't receive a code? Resend</button>
          </div>
        </div>

        <div class="auth-divider">◇ — — ◆ — — ◇</div>
      </div>
    </div>

<?php
$extraJs = '<script>
document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll(".auth-tab");
  const panels = document.querySelectorAll(".auth-panel");
  const msg = document.getElementById("formMessages");

  function showTab(name) {
    tabs.forEach(t => t.classList.toggle("active", t.dataset.tab === name));
    panels.forEach(p => p.classList.toggle("active", p.id === "panel-" + name));
  }
  tabs.forEach(t => t.addEventListener("click", () => showTab(t.dataset.tab)));

  function showMsg(text, type) {
    msg.innerHTML = `<div class="alert alert--${type}">${text}</div>`;
    msg.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }

  async function submitForm(form, url) {
    const fd = new FormData(form);
    try {
      const res = await fetch(url, { method: "POST", body: fd, headers: { "X-Requested-With": "XMLHttpRequest" } });
      const data = await res.json();
      if (data.success) {
        showMsg(data.message, "success");
        if (data.redirect) setTimeout(() => window.location.href = data.redirect, 1000);
      } else {
        const errText = data.errors ? data.errors.join("<br>") : data.error;
        showMsg(errText, "error");
        if (data.redirect) setTimeout(() => { window.location.href = data.redirect; }, 1500);
      }
    } catch (e) { showMsg("Connection error. Try again.", "error"); }
  }

  document.getElementById("loginForm")?.addEventListener("submit", e => { e.preventDefault(); submitForm(e.target, "api/login.php"); });
  document.getElementById("registerForm")?.addEventListener("submit", e => { e.preventDefault(); submitForm(e.target, "api/register.php"); });

  // Verify code inputs — auto-advance
  const codeInputs = document.querySelectorAll("#codeInputs input");
  codeInputs.forEach((inp, i) => {
    inp.addEventListener("input", e => {
      if (e.target.value && i < codeInputs.length - 1) codeInputs[i + 1].focus();
    });
    inp.addEventListener("keydown", e => {
      if (e.key === "Backspace" && !e.target.value && i > 0) codeInputs[i - 1].focus();
    });
    inp.addEventListener("paste", e => {
      const text = (e.clipboardData || window.clipboardData).getData("text").trim();
      if (/^[0-9]{6}$/.test(text)) {
        e.preventDefault();
        [...text].forEach((ch, j) => { if (codeInputs[j]) codeInputs[j].value = ch; });
        codeInputs[5].focus();
      }
    });
  });

  document.getElementById("verifyForm")?.addEventListener("submit", e => {
    e.preventDefault();
    const code = [...codeInputs].map(i => i.value).join("");
    document.getElementById("codeHidden").value = code;
    submitForm(e.target, "api/verify.php");
  });

  document.getElementById("resendBtn")?.addEventListener("click", async () => {
    try {
      const res = await fetch("api/resend.php", { method: "POST", headers: { "X-Requested-With": "XMLHttpRequest" } });
      const data = await res.json();
      showMsg(data.message || data.error, data.success ? "success" : "error");
    } catch (e) { showMsg("Failed to resend. Try again.", "error"); }
  });
});
</script>';
include __DIR__ . '/includes/footer.php';
?>
