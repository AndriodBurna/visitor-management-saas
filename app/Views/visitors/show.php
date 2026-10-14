<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Expects (passed by VisitorController::show):
//   $title      string
//   $activeMenu string
//   $visitor    array  ['id','organization_id','full_name','phone','email','company',
//                        'identification_type','identification_number','photo',
//                        'created_at','updated_at','organization_name']
//   $visits     array  rows: ['id','organization_id','branch_id','visitor_id','host_id','purpose',
//                              'check_in_at','check_out_at','status','notes','created_at','updated_at',
//                              'branch_name','host_name']
$visitor = $visitor ?? [];
$visits  = $visits ?? [];

$statusMap = [
    'checked_in'  => ['class' => 'badge-success', 'label' => 'On site'],
    'checked_out' => ['class' => 'badge-neutral', 'label' => 'Checked out'],
    'pending'     => ['class' => 'badge-warning', 'label' => 'Pending'],
    'denied'      => ['class' => 'badge-danger',  'label' => 'Denied'],
];

$fullName = $visitor['full_name'] ?? '';
$activeVisit = null;
foreach ($visits as $visit) {
  if (($visit['status'] ?? '') === 'checked_in') {
    $activeVisit = $visit;
    break;
  }
}
?>

<div class="breadcrumb" style="margin-bottom: var(--space-3);">
  <a href="<?= site_url('visitors') ?>">All Visitors</a>
  <span class="breadcrumb__sep">/</span>
  <span><?= esc($fullName ?: 'Visitor') ?></span>
</div>

<div class="section-title">
  <div class="d-flex align-center gap-3">
    <div class="avatar avatar--lg" style="border-radius: var(--radius-md); overflow: hidden;">
      <?php if (!empty($visitor['photo'])): ?>
        <img src="<?= esc($visitor['photo']) ?>" alt="" style="width:100%; height:100%; object-fit: cover;">
      <?php else: ?>
        <?= esc(strtoupper(substr($fullName, 0, 1)) ?: '?') ?>
      <?php endif; ?>
    </div>
    <div>
      <h2><?= esc($fullName ?: 'Visitor') ?></h2>
      <?php if (!empty($visitor['organization_name'])): ?>
        <p class="text-muted" style="font-size: var(--text-sm); margin-top: 2px;">
          <?php if (!empty($visitor['organization_id'])): ?>
            <a href="<?= site_url('organizations/' . $visitor['organization_id']) ?>"><?= esc($visitor['organization_name']) ?></a>
          <?php else: ?>
            <?= esc($visitor['organization_name']) ?>
          <?php endif; ?>
        </p>
      <?php endif; ?>
    </div>
  </div>

  <div class="d-flex align-center gap-2">
    <?php if (session('user_role') === 'super_admin'): ?>
      <a href="<?= site_url('visitors/' . ($visitor['id'] ?? '') . '/edit') ?>" class="btn btn-secondary">Edit visitor</a>
    <?php endif; ?>
    <?php if ($activeVisit): ?>
      <form action="<?= site_url('visitors/' . ($visitor['id'] ?? '') . '/checkout') ?>" method="post" onsubmit="return confirm('Check out this visitor now?');">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-warning">Check out visitor</button>
      </form>
    <?php endif; ?>
    <a href="<?= site_url('visitors') ?>" class="btn btn-secondary">Back to All Visitors</a>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-header">
      <div>
        <h2>Visitor details</h2>
        <p>Identification and contact information on file</p>
      </div>
    </div>

    <div class="card-body">
      <dl class="detail-list">
        <div class="detail-list__row">
          <dt>Full name</dt>
          <dd><?= esc($fullName ?: '—') ?></dd>
        </div>
        <div class="detail-list__row">
          <dt>Organization</dt>
          <dd>
            <?php if (!empty($visitor['organization_id']) && !empty($visitor['organization_name'])): ?>
              <a href="<?= site_url('organizations/' . $visitor['organization_id']) ?>"><?= esc($visitor['organization_name']) ?></a>
            <?php else: ?>
              <?= esc($visitor['organization_name'] ?? '—') ?>
            <?php endif; ?>
          </dd>
        </div>
        <div class="detail-list__row">
          <dt>Company</dt>
          <dd><?= esc($visitor['company'] ?? '—') ?></dd>
        </div>
        <div class="detail-list__row">
          <dt>Phone</dt>
          <dd><?= esc($visitor['phone'] ?? '—') ?></dd>
        </div>
        <div class="detail-list__row">
          <dt>Email</dt>
          <dd><?= esc($visitor['email'] ?? '—') ?></dd>
        </div>
        <div class="detail-list__row">
          <dt>Identification type</dt>
          <dd><?= esc($visitor['identification_type'] ?? '—') ?></dd>
        </div>
        <div class="detail-list__row">
          <dt>Identification number</dt>
          <dd class="font-mono"><?= esc($visitor['identification_number'] ?? '—') ?></dd>
        </div>
        <div class="detail-list__row">
          <dt>Date added</dt>
          <dd>
            <?= !empty($visitor['created_at'])
                ? esc(date('d M Y', strtotime($visitor['created_at'])))
                : '—' ?>
          </dd>
        </div>
      </dl>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h2>Photo</h2>
    </div>
    <div class="card-body" style="text-align: center;">
      <?php if (!empty($visitor['photo'])): ?>
        <img src="<?= esc($visitor['photo']) ?>" alt="Photo of <?= esc($fullName) ?>" style="width: 100%; max-width: 220px; aspect-ratio: 1; object-fit: cover; border-radius: var(--radius-md); border: 1px solid var(--border-default);">
      <?php else: ?>
        <div class="empty-state" style="padding: var(--space-8) var(--space-4);">
          <div class="empty-state__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
          </div>
          <p style="margin: 0;">No photo on file</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="section-title">
  <div>
    <h2>Visit history</h2>
    <p class="text-muted" style="font-size: var(--text-sm); margin-top: 2px;">
      <?= count($visits) ?> recorded visit<?= count($visits) === 1 ? '' : 's' ?>
    </p>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="data-table visit-history-table">
      <thead>
        <tr>
          <th>Branch / Location</th>
          <th>Host</th>
          <th>Purpose</th>
          <th>Status</th>
          <th>Check-in</th>
          <th>Check-out</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($visits)): ?>
          <tr><td colspan="7">
            <div class="empty-state">
              <div class="empty-state__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              </div>
              <h3>No visit history yet</h3>
              <p>This visitor has no recorded visits across any organization.</p>
            </div>
          </td></tr>
        <?php else: ?>
          <?php foreach ($visits as $visit): ?>
            <?php $status = $statusMap[$visit['status'] ?? ''] ?? ['class' => 'badge-neutral', 'label' => ucfirst($visit['status'] ?? '—')]; ?>
            <tr>
              <td><?= esc($visit['branch_name'] ?? '—') ?></td>
              <td class="cell-muted"><?= esc($visit['host_name'] ?? '—') ?></td>
              <td class="cell-muted"><?= esc($visit['purpose'] ?? '—') ?></td>
              <td><span class="badge <?= $status['class'] ?>"><span class="badge__dot"></span><?= esc($status['label']) ?></span></td>
              <td class="cell-muted">
                <?= !empty($visit['check_in_at'])
                    ? esc(date('d M Y, H:i', strtotime($visit['check_in_at'])))
                    : '—' ?>
              </td>
              <td class="cell-muted">
                <?= !empty($visit['check_out_at'])
                    ? esc(date('d M Y, H:i', strtotime($visit['check_out_at'])))
                    : '—' ?>
              </td>
              <td class="cell-muted"><?= esc($visit['notes'] ?? '—') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
  .detail-list__row {
    display: grid;
    grid-template-columns: 160px 1fr;
    gap: var(--space-4);
    padding: var(--space-3) 0;
    border-bottom: 1px solid var(--border-default);
  }
  .detail-list__row:last-child { border-bottom: none; padding-bottom: 0; }
  .detail-list__row dt { font-size: var(--text-sm); color: var(--text-muted); font-weight: var(--weight-medium); }
  .detail-list__row dd { margin: 0; font-size: var(--text-sm); color: var(--text-primary); }
  @media (max-width: 640px) {
    .detail-list__row { grid-template-columns: 1fr; gap: var(--space-1); }
  }

  /* =========================================================
   Visitor Visit History Table
   ========================================================= */

.visit-history-table {
    width: 100%;
    table-layout: auto;
}

.visit-history-table th,
.visit-history-table td {
    vertical-align: middle;
}

/* Branch / Location */
.visit-history-table th:nth-child(1),
.visit-history-table td:nth-child(1) {
    min-width: 190px;
}

/* Host */
.visit-history-table th:nth-child(2),
.visit-history-table td:nth-child(2) {
    min-width: 130px;
}

/* Purpose */
.visit-history-table th:nth-child(3),
.visit-history-table td:nth-child(3) {
    min-width: 150px;
}

/* Status */
.visit-history-table th:nth-child(4),
.visit-history-table td:nth-child(4) {
    min-width: 110px;
    white-space: nowrap;
}

/* Check-in */
.visit-history-table th:nth-child(5),
.visit-history-table td:nth-child(5) {
    min-width: 175px;
    white-space: nowrap;
}

/* Check-out */
.visit-history-table th:nth-child(6),
.visit-history-table td:nth-child(6) {
    min-width: 175px;
    white-space: nowrap;
}

/* Notes */
.visit-history-table th:nth-child(7),
.visit-history-table td:nth-child(7) {
    min-width: 200px;
}
</style>
<?= $this->endSection() ?>
