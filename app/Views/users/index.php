<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Expects (passed by controller):
//   $organization  array  ['id','name', ...]
//   $users         array  rows: ['id','name','email','phone','role','branchName','status','createdAt']
//   $title         string
//   $activeMenu    string
$organization = $organization ?? [];
$users        = $users ?? [];
$filters      = $filters ?? ['q' => '', 'role' => '', 'status' => ''];

$roleMap = [
    'organization_admin' => 'Organization Admin',
    'branch_manager'     => 'Branch Manager',
    'receptionist'       => 'Receptionist',
];

$statusMap = [
    'active'    => ['class' => 'badge-success', 'label' => 'Active'],
    'inactive'  => ['class' => 'badge-neutral', 'label' => 'Inactive'],
    'suspended' => ['class' => 'badge-danger',  'label' => 'Suspended'],
];
?>

<div class="breadcrumb" style="margin-bottom: var(--space-3);">
  <a href="<?= site_url('organizations') ?>">Organizations</a>
  <span class="breadcrumb__sep">/</span>
  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '')) ?>"><?= esc($organization['name'] ?? 'Organization') ?></a>
  <span class="breadcrumb__sep">/</span>
  <span>Staff</span>
</div>

<div class="section-title">
  <div>
    <h2>Staff</h2>
    <p class="text-muted" style="font-size: var(--text-sm); margin-top: 2px;">
      <?= count($users) ?> staff account<?= count($users) === 1 ? '' : 's' ?> at <?= esc($organization['name'] ?? 'this organization') ?>
    </p>
  </div>
  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/users/create') ?>" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
    Add Staff Member
  </a>
</div>

<div class="card">
  <form class="table-toolbar" method="get" action="<?= current_url() ?>">
    <div class="table-toolbar__filters">
      <div class="input-icon-wrap" style="width: 280px;">
        <span class="input-icon">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </span>
        <input type="search" name="q" class="form-control" placeholder="Search staff" value="<?= esc($filters['q']) ?>">
      </div>

      <select name="role" class="form-control" aria-label="Filter by role">
        <option value="">All roles</option>
        <?php foreach ($roleMap as $value => $label): ?>
          <option value="<?= esc($value) ?>" <?= $filters['role'] === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
        <?php endforeach; ?>
      </select>

      <select name="status" class="form-control" aria-label="Filter by account status">
        <option value="">All statuses</option>
        <?php foreach ($statusMap as $value => $status): ?>
          <option value="<?= esc($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= esc($status['label']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
      <?php if ($filters['q'] || $filters['role'] || $filters['status']): ?>
        <a href="<?= current_url() ?>" class="btn btn-ghost btn-sm">Clear</a>
      <?php endif; ?>
    </div>

    <span class="text-muted" style="font-size: var(--text-sm);">
      <?= count($users) ?> staff account<?= count($users) === 1 ? '' : 's' ?>
    </span>
  </form>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Staff member</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Role</th>
          <th>Location</th>
          <th>Status</th>
          <th>Date created</th>
          <th class="cell-actions">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)): ?>
          <tr><td colspan="8">
            <div class="empty-state">
              <div class="empty-state__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
              </div>
              <h3>No staff accounts yet</h3>
              <p>Add administrators, branch managers, and receptionists so your team can start managing visitors.</p>
              <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/users/create') ?>" class="btn btn-primary btn-sm">Add Staff Member</a>
            </div>
          </td></tr>
        <?php else: ?>
          <?php foreach ($users as $user): ?>
            <?php
              $status = $statusMap[$user['status'] ?? 'active'] ?? $statusMap['active'];
              $roleLabel = $roleMap[$user['role'] ?? ''] ?? ucwords(str_replace('_', ' ', $user['role'] ?? '—'));
            ?>
            <tr>
              <td>
                <div class="table-person">
                  <div class="avatar"><?= esc(strtoupper(substr($user['name'] ?? '?', 0, 1))) ?></div>
                  <div class="table-person__meta">
                    <div class="table-person__name"><?= esc($user['name'] ?? '') ?></div>
                  </div>
                </div>
              </td>
              <td class="cell-muted"><?= esc($user['email'] ?? '—') ?></td>
              <td class="cell-muted"><?= esc($user['phone'] ?? '—') ?></td>
              <td><span class="badge badge-info"><?= esc($roleLabel) ?></span></td>
              <td class="cell-muted"><?= esc($user['branch_name'] ?? 'All locations') ?></td>
              <td><span class="badge <?= $status['class'] ?>"><span class="badge__dot"></span><?= $status['label'] ?></span></td>
              <td class="cell-muted"><?= !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '—' ?></td>
              <td class="cell-actions">
                <div class="action-group">
                  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/users/' . ($user['id'] ?? '')) ?>" class="action-btn">
                    <svg class="action-btn__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>View</span>
                  </a>
                  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/users/' . ($user['id'] ?? '') . '/edit') ?>" class="action-btn action-btn--accent">
                    <svg class="action-btn__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <span>Edit</span>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if (!empty($users)): ?>
    <div class="pagination-bar">
      <span>Showing <?= count($users) ?> staff account<?= count($users) === 1 ? '' : 's' ?></span>
      <?php if (isset($pager)): ?>
        <div class="pagination"><?= $pager->links() ?></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>
