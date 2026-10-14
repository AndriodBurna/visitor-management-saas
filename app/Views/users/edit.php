<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Expects (passed by controller):
//   $organization  array  ['id','name', ...]
//   $user          array  ['id','name','email','phone','role','status','branch_id', ...]
//   $branches      array  [['id','name'], ...] locations belonging to this organization
//   $title         string
//   $activeMenu    string
$organization = $organization ?? [];
$user         = $user ?? [];
$branches     = $branches ?? [];

// old() takes precedence on validation failure, falls back to current stored value.
$val = static function (string $field) use ($user) {
    $old = old($field);
    return $old !== null ? $old : ($user[$field] ?? '');
};

$roleOptions = [
    'organization_admin' => 'Organization Admin',
    'branch_manager'     => 'Branch Manager',
    'receptionist'       => 'Receptionist',
];
$currentRole = old('role') ?? ($user['role'] ?? '');

$statusOptions = [
    'active'    => 'Active',
    'inactive'  => 'Inactive',
    'suspended' => 'Suspended',
];
$currentStatus = old('status') ?? ($user['status'] ?? 'active');
$currentBranch = old('branch_id') ?? ($user['branch_id'] ?? '');
?>

<div class="breadcrumb" style="margin-bottom: var(--space-3);">
  <a href="<?= site_url('organizations') ?>">Organizations</a>
  <span class="breadcrumb__sep">/</span>
  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '')) ?>"><?= esc($organization['name'] ?? 'Organization') ?></a>
  <span class="breadcrumb__sep">/</span>
  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/users') ?>">Staff</a>
  <span class="breadcrumb__sep">/</span>
  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/users/' . ($user['id'] ?? '')) ?>"><?= esc($user['name'] ?? 'Staff member') ?></a>
  <span class="breadcrumb__sep">/</span>
  <span>Edit</span>
</div>

<div class="section-title">
  <div>
    <h2>Edit staff member</h2>
    <p class="text-muted" style="font-size: var(--text-sm); margin-top: 2px;">
      Update details for <?= esc($user['name'] ?? 'this staff member') ?>
    </p>
  </div>
</div>

<form action="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/users/' . ($user['id'] ?? '') . '/update') ?>" method="post" id="staff-edit-form">
  <?= csrf_field() ?>

  <div class="grid-2">
    <div class="card" style="margin-bottom: var(--space-5);">
      <div class="card-header">
        <div>
          <h2>Account details</h2>
          <p>Used for sign-in and day-to-day identification within the console.</p>
        </div>
      </div>

      <div class="card-body">
        <div class="form-grid">
          <div class="form-group form-group--full">
            <label class="form-label" for="name">Full name</label>
            <input
              type="text"
              id="name"
              name="name"
              class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
              value="<?= esc($val('name')) ?>"
              required
              autofocus
            >
            <?php if (session('errors.name')): ?>
              <span class="form-error"><?= esc(session('errors.name')) ?></span>
            <?php endif; ?>
          </div>

          <div class="form-group form-group--full">
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
                value="<?= esc($val('email')) ?>"
                required
              >
            </div>
            <?php if (session('errors.email')): ?>
              <span class="form-error"><?= esc(session('errors.email')) ?></span>
            <?php endif; ?>
          </div>

          <div class="form-group form-group--full">
            <label class="form-label" for="phone">Phone <span class="optional">(optional)</span></label>
            <div class="input-icon-wrap">
              <span class="input-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              </span>
              <input
                type="tel"
                id="phone"
                name="phone"
                class="form-control <?= session('errors.phone') ? 'is-invalid' : '' ?>"
                value="<?= esc($val('phone')) ?>"
              >
            </div>
            <?php if (session('errors.phone')): ?>
              <span class="form-error"><?= esc(session('errors.phone')) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="card" style="margin-bottom: var(--space-5);">
      <div class="card-header">
        <h2>Role, access &amp; status</h2>
      </div>

      <div class="card-body">
        <div class="form-group">
          <label class="form-label" for="role">Role</label>
          <select id="role" name="role" class="form-control <?= session('errors.role') ? 'is-invalid' : '' ?>" required>
            <?php foreach ($roleOptions as $value => $label): ?>
              <option value="<?= esc($value) ?>" <?= $currentRole === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (session('errors.role')): ?>
            <span class="form-error"><?= esc(session('errors.role')) ?></span>
          <?php else: ?>
            <span class="form-hint">Organization Admins manage every location. Branch Managers and Receptionists are tied to one.</span>
          <?php endif; ?>
        </div>

        <div class="form-group" id="branch-field-group">
          <label class="form-label" for="branch_id">
            Location
            <span class="optional" id="branch-optional-tag">(not required for Organization Admin)</span>
          </label>
          <select id="branch_id" name="branch_id" class="form-control <?= session('errors.branch_id') ? 'is-invalid' : '' ?>">
            <option value="">Select location</option>
            <?php foreach ($branches as $branch): ?>
              <option value="<?= esc($branch['id']) ?>" <?= (string) $currentBranch === (string) $branch['id'] ? 'selected' : '' ?>>
                <?= esc($branch['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (session('errors.branch_id')): ?>
            <span class="form-error"><?= esc(session('errors.branch_id')) ?></span>
          <?php else: ?>
            <span class="form-hint" id="branch-hint">Required for Branch Managers and Receptionists.</span>
          <?php endif; ?>
        </div>

        <div class="form-group mb-0">
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
              Use status to indicate the current state of this staff account.
            </span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-footer">
      <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/users/' . ($user['id'] ?? '')) ?>" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Save changes</button>
    </div>
  </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  /**
   * Usability-only enhancement: visually flags whether a location is
   * expected for the selected role. This does NOT enforce or replace
   * backend validation — the server remains the source of truth for
   * whether branch_id is required for a given role.
   */
  (function () {
    var roleSelect = document.getElementById('role');
    var branchGroup = document.getElementById('branch-field-group');
    var optionalTag = document.getElementById('branch-optional-tag');
    var hint = document.getElementById('branch-hint');

    var rolesRequiringBranch = ['branch_manager', 'receptionist'];

    function syncBranchState() {
      if (!roleSelect || !branchGroup) return;
      var requiresBranch = rolesRequiringBranch.indexOf(roleSelect.value) !== -1;

      if (optionalTag) optionalTag.style.display = requiresBranch ? 'none' : 'inline';
      if (hint) {
        hint.textContent = requiresBranch
          ? 'This role is tied to a single location.'
          : 'Required for Branch Managers and Receptionists.';
      }
      var branchSelect = document.getElementById('branch_id');
      if (branchSelect) {
        branchSelect.toggleAttribute('required', requiresBranch);
      }
    }

    if (roleSelect) {
      roleSelect.addEventListener('change', syncBranchState);
      syncBranchState();
    }
  })();
</script>
<?= $this->endSection() ?>
