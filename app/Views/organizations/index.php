<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Expects:
//   $organizations  array  rows: ['id','name','email','phone','status','createdAt']
//   $totalOrganizations  int  (optional) total count for the summary line
//   $pager          CI4 Pager instance (optional)
$organizations = $organizations ?? [];
$filters = $filters ?? ['q' => '', 'status' => ''];

$statusMap = [
    'active'   => ['class' => 'badge-success', 'label' => 'Active'],
    'inactive' => ['class' => 'badge-neutral', 'label' => 'Inactive'],
    'pending'  => ['class' => 'badge-warning', 'label' => 'Pending'],
    'suspended'=> ['class' => 'badge-danger',  'label' => 'Suspended'],
];
?>

<div class="section-title">
  <div>
    <h2>Organizations</h2>
    <p class="text-muted" style="font-size: var(--text-sm); margin-top: 2px;">
      Manage every tenant provisioned on this platform
    </p>
  </div>
  <div class="d-flex gap-2">
  <a href="<?= site_url('organizations/export') . '?' . http_build_query($filters) ?>" class="btn btn-secondary">
    Export CSV
  </a>
  <a href="<?= site_url('organizations/create') ?>" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Add Organization
  </a>
  </div>
</div>

<div class="card">
  <form class="table-toolbar" method="get" action="<?= site_url('organizations') ?>">
    <div class="table-toolbar__filters">
      <div class="input-icon-wrap" style="width: 280px;">
        <span class="input-icon">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </span>
        <input type="search" name="q" class="form-control" placeholder="Search organizations" value="<?= esc($filters['q']) ?>">
      </div>

      <select name="status" class="form-control" aria-label="Filter by organization status">
        <option value="">All statuses</option>
        <?php foreach ($statusMap as $value => $status): ?>
          <?php if ($value === 'pending') continue; ?>
          <option value="<?= esc($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= esc($status['label']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
      <?php if ($filters['q'] || $filters['status']): ?>
        <a href="<?= site_url('organizations') ?>" class="btn btn-ghost btn-sm">Clear</a>
      <?php endif; ?>
    </div>

    <span class="text-muted" style="font-size: var(--text-sm);">
      <?= count($organizations) ?><?= isset($totalOrganizations) ? ' of ' . (int) $totalOrganizations : '' ?> organizations
    </span>
  </form>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Organization</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Status</th>
          <th>Date created</th>
          <th class="cell-actions">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($organizations)): ?>
          <tr><td colspan="6">
            <div class="empty-state">
              <div class="empty-state__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
              </div>
              <h3>No organizations yet</h3>
              <p>Add your first organization to start provisioning visitor management for a tenant.</p>
              <a href="<?= site_url('organizations/create') ?>" class="btn btn-primary btn-sm">Add Organization</a>
            </div>
          </td></tr>
        <?php else: ?>
          <?php foreach ($organizations as $org): ?>
            <?php $status = $statusMap[$org['status'] ?? 'active'] ?? $statusMap['active']; ?>
            <tr>
              <td>
                <div class="table-person">
                  <div class="avatar" style="background: var(--surface-dark);">
                    <?= esc(strtoupper(substr($org['name'] ?? '?', 0, 2))) ?>
                  </div>
                  <div class="table-person__meta">
                    <div class="table-person__name"><?= esc($org['name'] ?? '') ?></div>
                    <?php if (!empty($org['slug'])): ?>
                      <div class="table-person__sub font-mono"><?= esc($org['slug']) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td class="cell-muted"><?= esc($org['email'] ?? '—') ?></td>
              <td class="cell-muted"><?= esc($org['phone'] ?? '—') ?></td>
              <td><span class="badge <?= $status['class'] ?>"><span class="badge__dot"></span><?= $status['label'] ?></span></td>
              <td class="cell-muted"><?= !empty($org['created_at']) ? date('d M Y', strtotime($org['created_at'])) : '—' ?></td>
              <td class="cell-actions">
                <div class="action-group">
                  <a href="<?= site_url('organizations/' . ($org['id'] ?? '')) ?>" class="action-btn">
                    <svg class="action-btn__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>View</span>
                  </a>
                  <a href="<?= site_url('organizations/' . ($org['id'] ?? '') . '/branches') ?>" class="action-btn">
                    <svg class="action-btn__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>Locations</span>
                  </a>
                  <a href="<?= site_url('organizations/' . ($org['id'] ?? '') . '/edit') ?>" class="action-btn action-btn--accent">
                    <svg class="action-btn__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <span>Edit</span>
                  </a>
                  <a href="<?= site_url('organizations/' . ($org['id'] ?? '') . '/lifecycle') ?>" class="action-btn">
                    <span>Lifecycle</span>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if (!empty($organizations)): ?>
    <div class="pagination-bar">
      <span>Showing <?= count($organizations) ?> organizations<?= isset($totalOrganizations) ? ' of ' . (int) $totalOrganizations : '' ?></span>
      <?php if (isset($pager)): ?>
        <div class="pagination"><?= $pager->links() ?></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>
