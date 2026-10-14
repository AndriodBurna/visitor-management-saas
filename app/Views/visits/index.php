<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$visits = $visits ?? [];
$filters = $filters ?? ['status' => '', 'date' => '', 'q' => ''];
$statusMap = [
    'scheduled'   => ['class' => 'badge-info', 'label' => 'Scheduled'],
    'checked_in'  => ['class' => 'badge-success', 'label' => 'Checked in'],
    'checked_out' => ['class' => 'badge-neutral', 'label' => 'Checked out'],
    'cancelled'   => ['class' => 'badge-neutral', 'label' => 'Cancelled'],
    'rejected'    => ['class' => 'badge-danger', 'label' => 'Rejected'],
];
?>

<div class="section-title">
  <div>
    <h2>Visit activity</h2>
    <p class="text-muted">Review visitor movement and visit outcomes for your workspace.</p>
  </div>
  <div class="d-flex align-center gap-2">
    <?php if (session('user_role') === 'super_admin'): ?>
      <a href="<?= site_url('visits/export?' . http_build_query($filters)) ?>" class="btn btn-secondary">Export CSV</a>
    <?php endif; ?>
    <a href="<?= site_url('visitors/checkin') ?>" class="btn btn-success">Check in visitor</a>
  </div>
</div>

<div class="card">
  <form class="table-toolbar" method="get" action="<?= site_url('visits') ?>">
    <div class="table-toolbar__filters">
      <div class="input-icon-wrap visit-search-field">
        <span class="input-icon" aria-hidden="true">⌕</span>
        <input type="search" name="q" class="form-control" placeholder="Search visitor, host, purpose" value="<?= esc($filters['q']) ?>">
      </div>
      <select name="status" class="form-control" aria-label="Filter by status">
        <option value="">All statuses</option>
        <?php foreach ($statusMap as $value => $status): ?>
          <option value="<?= esc($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= esc($status['label']) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="date" name="date" class="form-control" value="<?= esc($filters['date']) ?>" aria-label="Filter by date">
      <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
      <?php if ($filters['q'] || $filters['status'] || $filters['date']): ?>
        <a href="<?= site_url('visits') ?>" class="btn btn-ghost btn-sm">Clear</a>
      <?php endif; ?>
    </div>
    <span class="text-muted"><?= count($visits) ?> visit<?= count($visits) === 1 ? '' : 's' ?></span>
  </form>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>Visitor</th><th>Host</th><th>Location</th><th>Purpose</th><th>Status</th><th>Check-in</th><th>Check-out</th><th class="cell-actions">Action</th></tr>
      </thead>
      <tbody>
        <?php if (!$visits): ?>
          <tr><td colspan="8"><div class="empty-state"><div class="empty-state__icon">V</div><h3>No visits found</h3><p>Try changing the filters or record a new visitor arrival.</p><a href="<?= site_url('visitors/checkin') ?>" class="btn btn-primary btn-sm">Check in visitor</a></div></td></tr>
        <?php else: ?>
          <?php foreach ($visits as $visit): ?>
            <?php $status = $statusMap[$visit['status'] ?? ''] ?? ['class' => 'badge-neutral', 'label' => ucfirst((string) ($visit['status'] ?? 'Unknown'))]; ?>
            <tr>
              <td><a href="<?= site_url('visitors/' . (int) $visit['visitor_id']) ?>" class="table-person"><span class="avatar"><?= esc(strtoupper(substr($visit['visitor_name'] ?? '?', 0, 1))) ?></span><span class="table-person__meta"><span class="table-person__name"><?= esc($visit['visitor_name'] ?? '—') ?></span><span class="table-person__sub"><?= esc($visit['company'] ?? '') ?></span></span></a></td>
              <td class="cell-muted"><?= esc($visit['host_name'] ?? '—') ?></td>
              <td class="cell-muted"><?= esc($visit['branch_name'] ?? '—') ?></td>
              <td class="cell-muted"><?= esc($visit['purpose'] ?? '—') ?></td>
              <td><span class="badge <?= esc($status['class']) ?>"><span class="badge__dot"></span><?= esc($status['label']) ?></span></td>
              <td class="cell-muted"><?= !empty($visit['check_in_at']) ? esc(date('d M Y, H:i', strtotime($visit['check_in_at']))) : '—' ?></td>
              <td class="cell-muted"><?= !empty($visit['check_out_at']) ? esc(date('d M Y, H:i', strtotime($visit['check_out_at']))) : '—' ?></td>
              <td class="cell-actions">
                <?php if (($visit['status'] ?? '') === 'checked_in'): ?>
                  <form action="<?= site_url('visits/' . (int) $visit['id'] . '/checkout') ?>" method="post" onsubmit="return confirm('Check out this visitor now?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-warning btn-sm">Check out</button>
                  </form>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($visits): ?><div class="pagination-bar"><span>Showing <?= count($visits) ?> visits</span><div class="pagination"><?= $pager->links() ?></div></div><?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>.visit-search-field { width: 280px; max-width: 100%; }.visit-search-field .input-icon { font-size: 20px; line-height: 1; }</style>
<?= $this->endSection() ?>