<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Expects (passed by controller):
//   $organization  array  ['id','name','slug','email','phone','address','status']
//   $title         string
//   $activeMenu    string
$organization = $organization ?? [];

// old() takes precedence on validation failure, falls back to current stored value.
$val = static function (string $field) use ($organization) {
    $old = old($field);
    return $old !== null ? $old : ($organization[$field] ?? '');
};

// Each status maps to its existing badge treatment (used elsewhere across
// the app — organizations/index.php, show.php, etc.) plus a one-line,
// non-alarming description of what it means operationally. Submitted
// values remain exactly 'active' | 'inactive' | 'suspended'.
$statusDefinitions = [
    'active' => [
        'label'       => 'Active',
        'badgeClass'  => 'badge-success',
        'description' => 'Operates normally. Authorized staff retain access.',
    ],
    'inactive' => [
        'label'       => 'Inactive',
        'badgeClass'  => 'badge-neutral',
        'description' => 'Disabled on the platform. Staff cannot access the system.',
    ],
    'suspended' => [
        'label'       => 'Suspended',
        'badgeClass'  => 'badge-danger',
        'description' => 'Temporarily restricted. Staff cannot access the system until reactivated.',
    ],
];
$currentStatus = old('status') ?? ($organization['status'] ?? 'active');
?>

<div class="breadcrumb" style="margin-bottom: var(--space-3);">
  <a href="<?= site_url('organizations') ?>">Organizations</a>
  <span class="breadcrumb__sep">/</span>
  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '')) ?>"><?= esc($organization['name'] ?? 'Organization') ?></a>
  <span class="breadcrumb__sep">/</span>
  <span>Edit</span>
</div>

<div class="section-title">
  <div>
    <h2>Edit organization</h2>
    <p class="text-muted" style="font-size: var(--text-sm); margin-top: 2px;">
      Update details for <?= esc($organization['name'] ?? 'this organization') ?>
    </p>
  </div>
</div>

<form action="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/update') ?>" method="post">
  <?= csrf_field() ?>

  <div class="card" style="max-width: 720px;">
    <div class="card-header">
      <div>
        <h2>Organization details</h2>
        <p>These details apply whether this is a single-site business or a multi-location enterprise.</p>
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
            value="<?= esc($val('name')) ?>"
            required
            autofocus
          >
          <?php if (session('errors.name')): ?>
            <span class="form-error"><?= esc(session('errors.name')) ?></span>
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
            value="<?= esc($val('slug')) ?>"
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
              value="<?= esc($val('email')) ?>"
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
            placeholder="123 Market Street, Suite 400, San Francisco, CA 94103"
          ><?= esc($val('address')) ?></textarea>
          <?php if (session('errors.address')): ?>
            <span class="form-error"><?= esc(session('errors.address')) ?></span>
          <?php endif; ?>
        </div>

        <fieldset class="form-group form-group--full status-fieldset">
          <legend class="form-label">Status</legend>

          <div class="status-option-list" role="radiogroup" aria-label="Organization status" aria-describedby="status-hint">
            <?php foreach ($statusDefinitions as $value => $def): ?>
              <label class="status-option">
                <input
                  type="radio"
                  name="status"
                  value="<?= esc($value) ?>"
                  <?= $currentStatus === $value ? 'checked' : '' ?>
                  required
                >
                <span class="status-option__main">
                  <span class="badge <?= esc($def['badgeClass']) ?>">
                    <span class="badge__dot"></span><?= esc($def['label']) ?>
                  </span>
                  <span class="status-option__desc"><?= esc($def['description']) ?></span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>

          <?php if (session('errors.status')): ?>
            <span class="form-error"><?= esc(session('errors.status')) ?></span>
          <?php else: ?>
            <span class="form-hint" id="status-hint">Moving an organization away from Active takes effect immediately and blocks staff sign-in until it's set back to Active.</span>
          <?php endif; ?>
        </fieldset>
      </div>
    </div>

    <div class="card-footer">
      <a href="<?= site_url('organizations/' . ($organization['id'] ?? '')) ?>" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Save changes</button>
    </div>
  </div>
</form>
<?= $this->endSection() ?>
