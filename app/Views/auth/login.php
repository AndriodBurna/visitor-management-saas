<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign in · <?= esc($appName ?? 'Visitor Management') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/css/tokens.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>
  <div class="auth-shell">

    <section class="auth-visual">
      <div class="auth-visual__brand">
        <div class="sidebar-brand__mark">VM</div>
        <div class="sidebar-brand__name" style="color:#fff;">Visitor Management</div>
      </div>

      <div class="auth-visual__content">
        <h2>A secure front desk for every building you manage.</h2>
        <p>Pre-register guests, screen against watchlists, print badges, and keep a complete audit trail — built for institutions that can't afford to get this wrong.</p>

        <div class="auth-visual__stats">
          <div>
            <!-- <div class="auth-visual__stat-value">99.98%</div> -->
            <div class="auth-visual__stat-label">Platform uptime</div>
          </div>
          <div>
            <!-- <div class="auth-visual__stat-value">SOC 2</div> -->
            <div class="auth-visual__stat-label">Type II compliant</div>
          </div>
          <div>
            <div class="auth-visual__stat-value">24/7</div>
            <div class="auth-visual__stat-label">Support coverage</div>
          </div>
        </div>
      </div>

      <div class="auth-visual__foot">© <?= date('Y') ?> <?= esc($appName ?? 'Visitor Management') ?>. All rights reserved.</div>
    </section>

    <section class="auth-form-panel">
      <div class="auth-form-card">
        <h1>Sign in to your workspace</h1>
        <p class="subtitle">Enter your credentials to access the visitor console.</p>

        <?= $this->include('partials/flash_messages') ?>

        <!-- Wire this form to your existing auth controller/route. No JS validation logic implied. -->
        <form action="<?= site_url('login') ?>" method="post" novalidate>
          <?= csrf_field() ?>

          <div class="form-group">
            <label class="form-label" for="email">Work email</label>
            <div class="input-icon-wrap">
              <span class="input-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z" opacity="0"/><path d="M22 6l-10 7L2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
              </span>
              <input class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" type="email" id="email" name="email" placeholder="you@organization.com" value="<?= old('email') ?>" required autofocus>
            </div>
            <?php if (session('errors.email')): ?><span class="form-error"><?= esc(session('errors.email')) ?></span><?php endif; ?>
          </div>

          <div class="form-group">
            <div class="d-flex justify-between align-center">
              <label class="form-label" for="password">Password</label>
              <!-- <a href="<?= site_url('forgot-password') ?>" style="font-size: var(--text-xs);">Forgot password?</a> -->
            </div>
            <div class="input-icon-wrap">
              <span class="input-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </span>
              <input class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>" type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <?php if (session('errors.password')): ?><span class="form-error"><?= esc(session('errors.password')) ?></span><?php endif; ?>
          </div>

          <div class="checkbox-row" style="margin-bottom: var(--space-6);">
            <input type="checkbox" id="remember" name="remember" value="1">
            <label for="remember">Keep me signed in on this device</label>
          </div>

          <button type="submit" class="btn btn-primary btn-block btn-lg">Sign in</button>
        </form>

        <div class="auth-divider">Protected sign-in</div>
        <p class="text-muted" style="font-size: var(--text-xs); text-align:center;">
          Access is limited to authorized personnel. All sign-in attempts are logged for security and compliance.
        </p>
      </div>
    </section>
  </div>
</body>
</html>
