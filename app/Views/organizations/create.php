<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="section-title">
  <div>
    <h2>Add organization</h2>
    <p class="text-muted" style="font-size: var(--text-sm); margin-top: 2px;">
      Provision a new tenant on the platform
    </p>
  </div>
</div>

<form action="<?= site_url('organizations') ?>" method="post">
  <?= csrf_field() ?>

  <div class="card" style="max-width: 720px;">
    <div class="card-header">
      <div>
        <h2>Organization details</h2>
        <p>This information identifies the organization across the platform. New organizations are activated automatically — you can change the status any time afterward from the organization's edit page.</p>
      </div>
    </div>

    <div class="card-body">
      <div class="form-grid">
        <div class="form-group form-group--full">
          <label class="form-label" for="name">Organization name</label>
          <input
            type="text"
            id="name"
            name="name"
            class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
            placeholder="Acme General Hospital"
            value="<?= old('name') ?>"
            required
            autofocus
          >
          <?php if (session('errors.name')): ?>
            <span class="form-error"><?= esc(session('errors.name')) ?></span>
          <?php else: ?>
            <span class="form-hint">The organization's official, public-facing name.</span>
          <?php endif; ?>
        </div>

        <div class="form-group form-group--full">
          <label class="form-label" for="slug">Slug</label>
          <input
            type="text"
            id="slug"
            name="slug"
            class="form-control font-mono <?= session('errors.slug') ? 'is-invalid' : '' ?>"
            placeholder="acme-general-hospital"
            value="<?= old('slug') ?>"
            required
          >
          <?php if (session('errors.slug')): ?>
            <span class="form-error"><?= esc(session('errors.slug')) ?></span>
          <?php else: ?>
            <span class="form-hint">Used in URLs and system references. Lowercase letters, numbers and hyphens only.</span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="email">Email</label>
          <div class="input-icon-wrap">
            <span class="input-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 6 12 13 2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
            </span>
            <input
              type="email"
              id="email"
              name="email"
              class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>"
              placeholder="admin@acmehospital.org"
              value="<?= old('email') ?>"
              required
            >
          </div>
          <?php if (session('errors.email')): ?>
            <span class="form-error"><?= esc(session('errors.email')) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="phone">Phone</label>
          <div class="input-icon-wrap">
            <span class="input-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </span>
            <input
              type="tel"
              id="phone"
              name="phone"
              class="form-control <?= session('errors.phone') ? 'is-invalid' : '' ?>"
              placeholder="+1 (555) 000-0000"
              value="<?= old('phone') ?>"
            >
          </div>
          <?php if (session('errors.phone')): ?>
            <span class="form-error"><?= esc(session('errors.phone')) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group form-group--full">
          <label class="form-label" for="address">Address <span class="optional">(optional)</span></label>
          <textarea
            id="address"
            name="address"
            class="form-control <?= session('errors.address') ? 'is-invalid' : '' ?>"
            placeholder="123 Market Street, Suite 400, San Francisco, CA 94103"
          ><?= old('address') ?></textarea>
          <?php if (session('errors.address')): ?>
            <span class="form-error"><?= esc(session('errors.address')) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="card-footer">
      <a href="<?= site_url('organizations') ?>" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Create organization</button>
    </div>
  </div>
</form>
<?= $this->endSection() ?>
