<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Expects (passed by controller):
//   $organization  array  ['id','name', ...]
//   $branch        array  ['id','name','code','phone','address','status']
//   $title         string
//   $activeMenu    string
$organization = $organization ?? [];
$branch       = $branch ?? [];

// old() takes precedence on validation failure, falls back to current stored value.
$val = static function (string $field) use ($branch) {
    $old = old($field);
    return $old !== null ? $old : ($branch[$field] ?? '');
};

$statusOptions = [
    'active'    => 'Active',
    'inactive'  => 'Inactive',
    'suspended' => 'Suspended',
];
$currentStatus = old('status') ?? ($branch['status'] ?? 'active');
?>

<div class="breadcrumb" style="margin-bottom: var(--space-3);">
  <a href="<?= site_url('organizations') ?>">Organizations</a>
  <span class="breadcrumb__sep">/</span>
  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '')) ?>"><?= esc($organization['name'] ?? 'Organization') ?></a>
  <span class="breadcrumb__sep">/</span>
  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches') ?>">Locations</a>
  <span class="breadcrumb__sep">/</span>
  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches/' . ($branch['id'] ?? '')) ?>"><?= esc($branch['name'] ?? 'Location') ?></a>
  <span class="breadcrumb__sep">/</span>
  <span>Edit</span>
</div>

<div class="section-title">
  <div>
    <h2>Edit location</h2>
    <p class="text-muted" style="font-size: var(--text-sm); margin-top: 2px;">
      Update details for <?= esc($branch['name'] ?? 'this location') ?>
    </p>
  </div>
</div>

<form action="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches/' . ($branch['id'] ?? '') . '/update') ?>" method="post">
  <?= csrf_field() ?>

  <div class="card" style="max-width: 720px;">
    <div class="card-header">
      <div>
        <h2>Location details</h2>
        <p>These details apply whether this is the organization's only address or one of many locations.</p>
      </div>
    </div>

    <div class="card-body">
      <div class="form-grid">
        <div class="form-group form-group--full">
          <label class="form-label" for="name">Location name</label>
          <input
            type="text"
            id="name"
            name="name"
            class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
            placeholder="Main Location"
            value="<?= esc($val('name')) ?>"
            required
            autofocus
          >
          <?php if (session('errors.name')): ?>
            <span class="form-error"><?= esc(session('errors.name')) ?></span>
          <?php else: ?>
            <span class="form-hint">How this location is identified within the organization.</span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="code">Location code</label>
          <input
            type="text"
            id="code"
            name="code"
            class="form-control font-mono <?= session('errors.code') ? 'is-invalid' : '' ?>"
            placeholder="MAIN-01"
            value="<?= esc($val('code')) ?>"
            required
          >
          <?php if (session('errors.code')): ?>
            <span class="form-error"><?= esc(session('errors.code')) ?></span>
          <?php else: ?>
            <span class="form-hint">A short internal reference code.</span>
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
              value="<?= esc($val('phone')) ?>"
            >
          </div>
          <?php if (session('errors.phone')): ?>
            <span class="form-error"><?= esc(session('errors.phone')) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group form-group--full">
          <label class="form-label" for="address">Address</label>
          <textarea
            id="address"
            name="address"
            class="form-control <?= session('errors.address') ? 'is-invalid' : '' ?>"
            placeholder="500 Market Street, Building B, San Francisco, CA 94105"
          ><?= esc($val('address')) ?></textarea>
          <?php if (session('errors.address')): ?>
            <span class="form-error"><?= esc(session('errors.address')) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group form-group--full">
          <label class="form-label" for="status">Status</label>
          <select id="status" name="status" class="form-control <?= session('errors.status') ? 'is-invalid' : '' ?>" required>
            <?php foreach ($statusOptions as $value => $label): ?>
              <option value="<?= esc($value) ?>" <?= $currentStatus === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (session('errors.status')): ?>
            <span class="form-error"><?= esc(session('errors.status')) ?></span>
          <?php else: ?>
            <span class="form-hint">
                Use status to indicate whether this location is currently active on the platform.
            </span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="card-footer">
      <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches/' . ($branch['id'] ?? '')) ?>" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Save changes</button>
    </div>
  </div>
</form>
<?= $this->endSection() ?>
