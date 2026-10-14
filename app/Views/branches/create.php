<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$organization = $organization ?? [];
?>

<div class="breadcrumb" style="margin-bottom: var(--space-3);">
  <a href="<?= site_url('organizations') ?>">Organizations</a>
  <span class="breadcrumb__sep">/</span>
  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches') ?>"><?= esc($organization['name'] ?? 'Organization') ?></a>
  <span class="breadcrumb__sep">/</span>
  <span>Add Location</span>
</div>

<div class="section-title">
  <div>
    <h2>Add branch or location</h2>
    <p class="text-muted" style="font-size: var(--text-sm); margin-top: 2px;">
      Add a physical location where <?= esc($organization['name'] ?? 'this organization') ?> receives visitors.
    </p>
  </div>
</div>

<form action="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches') ?>" method="post">
  <?= csrf_field() ?>

  <div class="card" style="max-width: 760px;">
    <div class="card-header">
      <div>
        <h2>Location details</h2>
        <p>
          Suitable for a main office, shop, clinic, school campus, hotel, branch office, or any other visitor-facing location.
          New locations are activated automatically.
        </p>
      </div>
    </div>

    <div class="card-body">
      <div class="form-grid">
        <div class="form-group form-group--full">
          <label class="form-label" for="name">Branch / location name</label>
          <input
            type="text"
            id="name"
            name="name"
            class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
            placeholder="Main Office"
            value="<?= old('name') ?>"
            required
            autofocus
          >
          <?php if (session('errors.name')): ?>
            <span class="form-error"><?= esc(session('errors.name')) ?></span>
          <?php else: ?>
            <span class="form-hint">
              Use a name your staff will immediately recognize, such as Main Office, Kampala Branch, Reception Block, or Downtown Store.
            </span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="code">Location code</label>
          <input
            type="text"
            id="code"
            name="code"
            class="form-control font-mono <?= session('errors.code') ? 'is-invalid' : '' ?>"
            placeholder="MAIN"
            value="<?= old('code') ?>"
            required
          >
          <?php if (session('errors.code')): ?>
            <span class="form-error"><?= esc(session('errors.code')) ?></span>
          <?php else: ?>
            <span class="form-hint">
              A short internal reference. A single-location business can simply use MAIN.
            </span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="phone">Location phone <span class="optional">(optional)</span></label>
          <div class="input-icon-wrap">
            <span class="input-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </span>
            <input
              type="tel"
              id="phone"
              name="phone"
              class="form-control <?= session('errors.phone') ? 'is-invalid' : '' ?>"
              placeholder="+256 700 000000"
              value="<?= old('phone') ?>"
            >
          </div>
          <?php if (session('errors.phone')): ?>
            <span class="form-error"><?= esc(session('errors.phone')) ?></span>
          <?php else: ?>
            <span class="form-hint">Use the direct reception or front-desk number if the location has one.</span>
          <?php endif; ?>
        </div>

        <div class="form-group form-group--full">
          <label class="form-label" for="address">Physical address <span class="optional">(optional)</span></label>
          <textarea
            id="address"
            name="address"
            class="form-control <?= session('errors.address') ? 'is-invalid' : '' ?>"
            placeholder="Plot 12, Kampala Road, Kampala"
          ><?= old('address') ?></textarea>
          <?php if (session('errors.address')): ?>
            <span class="form-error"><?= esc(session('errors.address')) ?></span>
          <?php else: ?>
            <span class="form-hint">Add enough detail for staff, visitors, and security teams to identify the location.</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="card-footer">
      <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches') ?>" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Create location</button>
    </div>
  </div>
</form>
<?= $this->endSection() ?>
