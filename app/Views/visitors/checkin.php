<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Expects (passed by the controller):
//   $title          string
//   $activeMenu     string
//   $organizations  array  [['id','name','status'], ...]
//   $branches       array  [['id','organization_id','name','code','status'], ...]
//   $hosts          array  [['id','organization_id','branch_id','name','role','status'], ...]
$organizations = $organizations ?? [];
$branches      = $branches ?? [];
$hosts         = $hosts ?? [];

$currentOrg    = old('organization_id') ?? '';
$currentBranch = old('branch_id') ?? '';
$currentHost   = old('host_id') ?? '';

// Small, purely informational suffix — helps front-desk staff avoid picking
// an organization/branch/host that isn't active. It changes nothing about
// what's selectable; the backend remains the real source of truth.
$statusSuffix = static function (array $row): string {
    $status = $row['status'] ?? 'active';
    return $status !== 'active' ? ' — ' . ucfirst($status) : '';
};
?>

<div class="section-title">
  <div>
    <h2>New check-in</h2>
    <p class="text-muted" style="font-size: var(--text-sm); margin-top: 2px;">
      Register a visitor arriving at any organization on the platform
    </p>
  </div>
</div>

<form action="<?= site_url('visitors/checkin') ?>" method="post" id="checkin-form">
  <?= csrf_field() ?>

  <!-- ============================== 1. Visit destination ============================== -->
  <div class="card" style="margin-bottom: var(--space-5);">
    <div class="card-header">
      <div>
        <h2>Visit destination</h2>
        <p>Which organization and location this visit belongs to</p>
      </div>
    </div>

    <div class="card-body">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="organization_id">Organization</label>
          <select
            id="organization_id"
            name="organization_id"
            class="form-control <?= session('errors.organization_id') ? 'is-invalid' : '' ?>"
            required
          >
            <option value="">Select organization</option>
            <?php foreach ($organizations as $org): ?>
              <option value="<?= esc($org['id']) ?>" <?= (string) $currentOrg === (string) $org['id'] ? 'selected' : '' ?>>
                <?= esc($org['name']) ?><?= esc($statusSuffix($org)) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (session('errors.organization_id')): ?>
            <span class="form-error"><?= esc(session('errors.organization_id')) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="branch_id">Location</label>
          <select
            id="branch_id"
            name="branch_id"
            class="form-control <?= session('errors.branch_id') ? 'is-invalid' : '' ?>"
            required
          >
            <option value="">Select location</option>
            <?php foreach ($branches as $branch): ?>
              <option
                value="<?= esc($branch['id']) ?>"
                data-org="<?= esc($branch['organization_id']) ?>"
                <?= (string) $currentBranch === (string) $branch['id'] ? 'selected' : '' ?>
              >
                <?= esc($branch['name']) ?><?= !empty($branch['code']) ? ' (' . esc($branch['code']) . ')' : '' ?><?= esc($statusSuffix($branch)) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (session('errors.branch_id')): ?>
            <span class="form-error"><?= esc(session('errors.branch_id')) ?></span>
          <?php else: ?>
            <span class="form-hint">Options narrow down once an organization is selected.</span>
          <?php endif; ?>
        </div>

        <div class="form-group form-group--full">
          <label class="form-label" for="host_id">Host <span class="optional">(optional)</span></label>
          <select
            id="host_id"
            name="host_id"
            class="form-control <?= session('errors.host_id') ? 'is-invalid' : '' ?>"
          >
            <option value="">No specific host</option>
            <?php foreach ($hosts as $host): ?>
              <option
                value="<?= esc($host['id']) ?>"
                data-org="<?= esc($host['organization_id']) ?>"
                data-branch="<?= esc($host['branch_id'] ?? '') ?>"
                <?= (string) $currentHost === (string) $host['id'] ? 'selected' : '' ?>
              >
                <?= esc($host['name']) ?><?= !empty($host['role']) ? ' — ' . esc(ucwords(str_replace('_', ' ', $host['role']))) : '' ?><?= esc($statusSuffix($host)) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (session('errors.host_id')): ?>
            <span class="form-error"><?= esc(session('errors.host_id')) ?></span>
          <?php else: ?>
            <span class="form-hint">Organization-wide staff stay selectable regardless of location; location-specific staff only appear once that location is chosen.</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================== 2. Visitor information ============================== -->
  <div class="card" style="margin-bottom: var(--space-5);">
    <div class="card-header">
      <div>
        <h2>Visitor information</h2>
        <p>Who is arriving</p>
      </div>
    </div>

    <div class="card-body">
      <div class="form-grid">
        <div class="form-group form-group--full">
          <label class="form-label" for="full_name">Visitor full name</label>
          <input
            type="text"
            id="full_name"
            name="full_name"
            class="form-control <?= session('errors.full_name') ? 'is-invalid' : '' ?>"
            placeholder="Jane Cooper"
            value="<?= old('full_name') ?>"
            required
            autofocus
          >
          <?php if (session('errors.full_name')): ?>
            <span class="form-error"><?= esc(session('errors.full_name')) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
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
             placeholder="+256 700 000000"
              value="<?= old('phone') ?>"
            >
          </div>
          <?php if (session('errors.phone')): ?>
            <span class="form-error"><?= esc(session('errors.phone')) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="email">Email <span class="optional">(optional)</span></label>
          <div class="input-icon-wrap">
            <span class="input-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 6 12 13 2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
            </span>
            <input
              type="email"
              id="email"
              name="email"
              class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>"
              placeholder="jane@acme.com"
              value="<?= old('email') ?>"
            >
          </div>
          <?php if (session('errors.email')): ?>
            <span class="form-error"><?= esc(session('errors.email')) ?></span>
          <?php else: ?>
            <span class="form-hint">Must be a valid email address if provided.</span>
          <?php endif; ?>
        </div>

        <div class="form-group form-group--full">
          <label class="form-label" for="company">Company <span class="optional">(optional)</span></label>
          <input
            type="text"
            id="company"
            name="company"
            class="form-control <?= session('errors.company') ? 'is-invalid' : '' ?>"
            placeholder="Acme Corporation"
            value="<?= old('company') ?>"
          >
          <?php if (session('errors.company')): ?>
            <span class="form-error"><?= esc(session('errors.company')) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================== 3. Identification ============================== -->
  <div class="card" style="margin-bottom: var(--space-5);">
    <div class="card-header">
      <div>
        <h2>Identification</h2>
        <p>Optional, for organizations that record visitor ID details</p>
      </div>
    </div>

    <div class="card-body">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="identification_type">Identification type <span class="optional">(optional)</span></label>
          <input
            type="text"
            id="identification_type"
            name="identification_type"
            class="form-control <?= session('errors.identification_type') ? 'is-invalid' : '' ?>"
            placeholder="Passport, National ID, Driver's license…"
            value="<?= old('identification_type') ?>"
          >
          <?php if (session('errors.identification_type')): ?>
            <span class="form-error"><?= esc(session('errors.identification_type')) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="identification_number">Identification number <span class="optional">(optional)</span></label>
          <input
            type="text"
            id="identification_number"
            name="identification_number"
            class="form-control font-mono <?= session('errors.identification_number') ? 'is-invalid' : '' ?>"
            placeholder="A1234567"
            value="<?= old('identification_number') ?>"
          >
          <?php if (session('errors.identification_number')): ?>
            <span class="form-error"><?= esc(session('errors.identification_number')) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================== 4. Visit details ============================== -->
  <div class="card" style="margin-bottom: var(--space-5);">
    <div class="card-header">
      <div>
        <h2>Visit details</h2>
        <p>Reason for the visit and any additional context</p>
      </div>
    </div>

    <div class="card-body">
      <div class="form-grid">
        <div class="form-group form-group--full">
          <label class="form-label" for="purpose">Purpose of visit</label>
          <textarea
            id="purpose"
            name="purpose"
            class="form-control <?= session('errors.purpose') ? 'is-invalid' : '' ?>"
            placeholder="Briefly describe the reason for this visit"
            required
          ><?= old('purpose') ?></textarea>
          <?php if (session('errors.purpose')): ?>
            <span class="form-error"><?= esc(session('errors.purpose')) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group form-group--full mb-0">
          <label class="form-label" for="notes">Notes <span class="optional">(optional)</span></label>
          <textarea
            id="notes"
            name="notes"
            class="form-control <?= session('errors.notes') ? 'is-invalid' : '' ?>"
            placeholder="Anything else front-desk or the host should know"
          ><?= old('notes') ?></textarea>
          <?php if (session('errors.notes')): ?>
            <span class="form-error"><?= esc(session('errors.notes')) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-footer">
      <a href="<?= site_url('visitors') ?>" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Check in visitor</button>
    </div>
  </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  /**
   * Organization → Location → Host cascading filter.
   *
   * This is a usability enhancement only. All organizations, branches and
   * hosts are already rendered into the DOM by the server; this script just
   * hides/disables the options that don't belong to the current selection
   * so front-desk staff aren't offered mismatched combinations. It does not
   * call any endpoint and does not replace backend validation — the server
   * is the real authority on which organization/branch/host combinations
   * are valid.
   */
  (function () {
    var orgSelect    = document.getElementById('organization_id');
    var branchSelect = document.getElementById('branch_id');
    var hostSelect   = document.getElementById('host_id');

    if (!orgSelect || !branchSelect || !hostSelect) return;

    function setOptionVisibility(select, isVisible) {
      Array.prototype.forEach.call(select.options, function (option) {
        if (!option.value) return; // always keep the placeholder option
        var visible = isVisible(option);
        option.hidden = !visible;
        option.disabled = !visible;
      });
    }

    function selectionStillValid(select) {
      if (!select.value) return true;
      var selected = select.options[select.selectedIndex];
      return !!selected && !selected.hidden;
    }

    function applyFilters(resetInvalidSelections) {
      var orgId = orgSelect.value;

      setOptionVisibility(branchSelect, function (option) {
        return !orgId || option.getAttribute('data-org') === orgId;
      });
      if (resetInvalidSelections && !selectionStillValid(branchSelect)) {
        branchSelect.value = '';
      }

      var branchId = branchSelect.value;

      setOptionVisibility(hostSelect, function (option) {
        if (orgId && option.getAttribute('data-org') !== orgId) return false;
        if (!branchId) return true; // no location chosen yet — show all org hosts
        var hostBranch = option.getAttribute('data-branch') || '';
        return hostBranch === '' || hostBranch === branchId;
      });
      if (resetInvalidSelections && !selectionStillValid(hostSelect)) {
        hostSelect.value = '';
      }
    }

    orgSelect.addEventListener('change', function () { applyFilters(true); });
    branchSelect.addEventListener('change', function () { applyFilters(true); });

    // Run once on load (without resetting anything) so values preserved via
    // old() after a validation failure are shown with the correct,
    // already-narrowed option set.
    applyFilters(false);
  })();
</script>
<?= $this->endSection() ?>
