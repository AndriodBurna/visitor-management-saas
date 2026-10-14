<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Expects (passed by controller):
//   $organization  array  ['id','name','email','phone', ...]
//   $branches      array  rows: ['id','name','code','phone','address','status','createdAt']
//   $title         string
//   $activeMenu    string
$organization = $organization ?? [];
$branches     = $branches ?? [];
$filters      = $filters ?? ['q' => '', 'status' => ''];

$statusMap = [
    'active'    => ['class' => 'badge-success', 'label' => 'Active'],
    'inactive'  => ['class' => 'badge-neutral', 'label' => 'Inactive'],
    'suspended' => ['class' => 'badge-danger',  'label' => 'Suspended'],
];
?>

<div class="breadcrumb" style="margin-bottom: var(--space-3);">
  <a href="<?= site_url('organizations') ?>">Organizations</a>
  <span class="breadcrumb__sep">/</span>
  <span><?= esc($organization['name'] ?? 'Organization') ?></span>
</div>

<div class="section-title">
  <div>
    <h2><?= esc($organization['name'] ?? 'Organization') ?></h2>
    <p class="text-muted" style="font-size: var(--text-sm); margin-top: 2px;">
      <?= count($branches) ?> branch<?= count($branches) === 1 ? '' : 'es' ?>
      <?php if (!empty($organization['email'])): ?>
        &middot; <?= esc($organization['email']) ?>
      <?php endif; ?>
    </p>
  </div>
  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches/create') ?>" class="btn btn-primary">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Add Branch
  </a>
</div>

<div class="card">
  <form class="table-toolbar" method="get" action="<?= current_url() ?>">
    <div class="table-toolbar__filters">
      <div class="input-icon-wrap" style="width: 280px;">
        <span class="input-icon">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </span>
        <input type="search" name="q" class="form-control" placeholder="Search branches" value="<?= esc($filters['q']) ?>">
      </div>

      <select name="status" class="form-control" aria-label="Filter by branch status">
        <option value="">All statuses</option>
        <?php foreach ($statusMap as $value => $status): ?>
          <option value="<?= esc($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= esc($status['label']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
      <?php if ($filters['q'] || $filters['status']): ?>
        <a href="<?= current_url() ?>" class="btn btn-ghost btn-sm">Clear</a>
      <?php endif; ?>
    </div>

    <span class="text-muted" style="font-size: var(--text-sm);">
      <?= count($branches) ?> branch<?= count($branches) === 1 ? '' : 'es' ?>
    </span>
  </form>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Branch</th>
          <th>Code</th>
          <th>Phone</th>
          <th>Address</th>
          <th>Status</th>
          <th>Date created</th>
          <th class="cell-actions">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($branches)): ?>
          <tr><td colspan="7">
            <div class="empty-state">
              <div class="empty-state__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/><path d="M9 9h1M9 13h1M14 9h1M14 13h1"/></svg>
              </div>
              <h3>No branches yet</h3>
              <p>Add the first branch for <?= esc($organization['name'] ?? 'this organization') ?> to start managing visitors by location.</p>
              <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches/create') ?>" class="btn btn-primary btn-sm">Add Branch</a>
            </div>
          </td></tr>
        <?php else: ?>
          <?php foreach ($branches as $branch): ?>
            <?php $status = $statusMap[$branch['status'] ?? 'active'] ?? $statusMap['active']; ?>
            <tr>
              <td>
                <div class="table-person">
                  <div class="avatar" style="background: var(--surface-dark);">
                    <?= esc(strtoupper(substr($branch['name'] ?? '?', 0, 2))) ?>
                  </div>
                  <div class="table-person__meta">
                    <div class="table-person__name"><?= esc($branch['name'] ?? '') ?></div>
                  </div>
                </div>
              </td>
              <td class="cell-muted font-mono"><?= esc($branch['code'] ?? '—') ?></td>
              <td class="cell-muted"><?= esc($branch['phone'] ?? '—') ?></td>
              <td class="cell-muted"><?= esc($branch['address'] ?? '—') ?></td>
              <td><span class="badge <?= $status['class'] ?>"><span class="badge__dot"></span><?= $status['label'] ?></span></td>
              <td class="cell-muted"><?= !empty($branch['created_at']) ? date('d M Y', strtotime($branch['created_at'])) : '—' ?></td>
              <td class="cell-actions">
                <div class="action-group">
                  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches/' . ($branch['id'] ?? '')) ?>" class="action-btn">
                    <svg class="action-btn__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>View</span>
                  </a>
                  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches/' . ($branch['id'] ?? '') . '/edit') ?>" class="action-btn action-btn--accent">
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

  <?php if (!empty($branches)): ?>
    <div class="pagination-bar">
      <span>Showing <?= count($branches) ?> branch<?= count($branches) === 1 ? '' : 'es' ?></span>
      <?php if (isset($pager)): ?>
        <div class="pagination"><?= $pager->links() ?></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>
