<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Expects (passed by VisitorController::index):
//   $title        string
//   $activeMenu   string
//   $visitors     array  rows: ['id','organization_id','full_name','phone','email','company',
//                                'identification_type','identification_number','photo',
//                                'created_at','updated_at','organization_name',
//                                'last_visit_at','is_on_site']
//   $filters      array  ['q' => '', 'status' => '', 'date' => '']
//
$visitors = $visitors ?? [];
$filters  = $filters ?? ['q' => '', 'status' => '', 'date' => ''];
?>

<div class="section-title">
  <div>
    <h2>All visitors</h2>
    <p class="text-muted" style="font-size: var(--text-sm); margin-top: 2px;">
      Every visitor recorded across all organizations on this platform
    </p>
  </div>
  <a href="<?= site_url('visitors/export') . '?' . http_build_query($filters) ?>" class="btn btn-secondary">Export CSV</a>
</div>

<div class="card">
  <form class="table-toolbar" method="get" action="<?= site_url('visitors') ?>">
    <div class="table-toolbar__filters">
      <div class="input-icon-wrap" style="width: 360px; max-width: 100%;">
        <span class="input-icon">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </span>
        <input type="text" name="q" class="form-control" placeholder="Search by name, company, email or phone" value="<?= esc($filters['q'] ?? '') ?>">
      </div>

      <select name="status" class="form-control" aria-label="Filter by visitor status">
        <option value="">All visitor statuses</option>
        <option value="on_site" <?= ($filters['status'] ?? '') === 'on_site' ? 'selected' : '' ?>>On site</option>
        <option value="not_on_site" <?= ($filters['status'] ?? '') === 'not_on_site' ? 'selected' : '' ?>>Not on site</option>
      </select>
      <input type="date" name="date" class="form-control" aria-label="Filter by visit date" value="<?= esc($filters['date'] ?? '') ?>">

      <button type="submit" class="btn btn-secondary btn-sm">Search</button>
      <?php if (!empty($filters['q']) || !empty($filters['status']) || !empty($filters['date'])): ?>
        <a href="<?= site_url('visitors') ?>" class="btn btn-ghost btn-sm">Clear</a>
      <?php endif; ?>
    </div>

    <span class="text-muted" style="font-size: var(--text-sm);">
      <?= count($visitors) ?> visitor<?= count($visitors) === 1 ? '' : 's' ?>
    </span>
  </form>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Visitor</th>
          <th>Organization</th>
          <th>Company</th>
          <th>Phone / Email</th>
          <th>Identification</th>
          <th>Status</th>
          <th>Last visit</th>
          <th>Date added</th>
          <th class="cell-actions">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($visitors)): ?>
          <tr><td colspan="9">
            <div class="empty-state">
              <div class="empty-state__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              </div>
              <?php if (!empty($filters['q'])): ?>
                <h3>No visitors match your search</h3>
                <p>Try a different name, company, email or phone number.</p>
                <a href="<?= site_url('visitors') ?>" class="btn btn-secondary btn-sm">Clear search</a>
              <?php else: ?>
                <h3>No visitors recorded yet</h3>
                <p>Visitors will appear here as they're checked in across your organizations.</p>
              <?php endif; ?>
            </div>
          </td></tr>
        <?php else: ?>
          <?php foreach ($visitors as $visitor): ?>
            <?php
              $fullName = $visitor['full_name'] ?? '';
              $idType   = $visitor['identification_type'] ?? '';
              $idNumber = $visitor['identification_number'] ?? '';

              $isOnSite = !empty($visitor['is_on_site']);

              $lastVisitAt = $visitor['last_visit_at'] ?? null;
              $lastVisitFormatted = '—';
              if (!empty($lastVisitAt)) {
                  $lastVisitTimestamp = strtotime($lastVisitAt);
                  if ($lastVisitTimestamp !== false) {
                      $lastVisitFormatted = date('d M Y, H:i', $lastVisitTimestamp);
                  }
              }
            ?>
            <tr>
              <td>
                <div class="table-person">
                  <div class="avatar">
                    <?php if (!empty($visitor['photo'])): ?>
                      <img src="<?= esc($visitor['photo']) ?>" alt="" style="width:100%; height:100%; object-fit: cover; border-radius: inherit;">
                    <?php else: ?>
                      <?= esc(strtoupper(substr($fullName, 0, 1)) ?: '?') ?>
                    <?php endif; ?>
                  </div>
                  <div class="table-person__meta">
                    <div class="table-person__name"><?= esc($fullName ?: '—') ?></div>
                  </div>
                </div>
              </td>
              <td class="cell-muted"><?= esc($visitor['organization_name'] ?? '—') ?></td>
              <td class="cell-muted"><?= esc($visitor['company'] ?? '—') ?></td>
              <td class="cell-muted">
                <div><?= esc($visitor['phone'] ?? '—') ?></div>
                <div style="font-size: var(--text-xs); color: var(--text-muted);"><?= esc($visitor['email'] ?? '—') ?></div>
              </td>
              <td class="cell-muted">
                <?php if ($idType || $idNumber): ?>
                  <div><?= esc($idType ?: '—') ?></div>
                  <?php if ($idNumber): ?>
                    <div class="font-mono" style="font-size: var(--text-xs); color: var(--text-muted);"><?= esc($idNumber) ?></div>
                  <?php endif; ?>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td>
                <?php if ($isOnSite): ?>
                  <span class="badge badge-success"><span class="badge__dot"></span>On site</span>
                <?php else: ?>
                  <span class="badge badge-neutral"><span class="badge__dot"></span>Not on site</span>
                <?php endif; ?>
              </td>
              <td class="cell-muted"><?= esc($lastVisitFormatted) ?></td>
              <td class="cell-muted">
                <?= !empty($visitor['created_at'])
                    ? esc(date('d M Y', strtotime($visitor['created_at'])))
                    : '—' ?>
              </td>
              <td class="cell-actions">
                <div class="action-group">
                  <a href="<?= site_url('visitors/' . ($visitor['id'] ?? '')) ?>" class="action-btn">
                    <svg class="action-btn__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>View</span>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if (!empty($visitors)): ?>
    <div class="pagination-bar">
      <span>Showing <?= count($visitors) ?> visitor<?= count($visitors) === 1 ? '' : 's' ?></span>
      <?php if (isset($pager)): ?>
        <div class="pagination"><?= $pager->links() ?></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>
