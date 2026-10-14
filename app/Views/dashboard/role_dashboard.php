<div class="section-title">
  <div>
    <h2><?= esc($roleTitle ?? 'Workspace') ?></h2>
    <p class="text-muted">Manage visitor activity for your assigned workspace.</p>
  </div>
  <a href="<?= site_url('visitors/checkin') ?>" class="btn btn-success">Check in visitor</a>
</div>

<?php $stats = $stats ?? ['onSite' => 0, 'checkedInToday' => 0, 'checkedOutToday' => 0]; ?>
<div class="stat-grid">
  <div class="stat-card stat-card--live">
    <div class="stat-card__label">Currently inside</div>
    <div class="stat-card__value"><?= (int) $stats['onSite'] ?></div>
    <span class="badge badge-success"><span class="badge__dot"></span>Live</span>
  </div>
  <div class="stat-card">
    <div class="stat-card__label">Check-ins today</div>
    <div class="stat-card__value"><?= (int) $stats['checkedInToday'] ?></div>
    <span class="stat-card__hint">Recorded arrivals</span>
  </div>
  <div class="stat-card">
    <div class="stat-card__label">Check-outs today</div>
    <div class="stat-card__value"><?= (int) $stats['checkedOutToday'] ?></div>
    <span class="stat-card__hint">Completed visits</span>
  </div>
</div>

<div class="grid-2">
  <a href="<?= site_url('visitors/checkin') ?>" class="card dashboard-action-card">
    <div class="dashboard-action-card__icon dashboard-action-card__icon--success">+</div>
    <div>
      <h3>Start a check-in</h3>
      <p>Register or find a visitor and record their arrival.</p>
    </div>
  </a>
  <a href="<?= site_url('visitors') ?>" class="card dashboard-action-card">
    <div class="dashboard-action-card__icon dashboard-action-card__icon--brand">V</div>
    <div>
      <h3>View visitors</h3>
      <p>Review visitor records and active visit status.</p>
    </div>
  </a>
  <a href="<?= site_url('visits') ?>" class="card dashboard-action-card">
    <div class="dashboard-action-card__icon dashboard-action-card__icon--info">H</div>
    <div>
      <h3>Review visit activity</h3>
      <p>Filter check-ins, check-outs, and current visit status.</p>
    </div>
  </a>
</div>
<style>
  .dashboard-action-card { display: flex; align-items: flex-start; gap: var(--space-4); padding: var(--space-6); color: inherit; transition: transform var(--transition-fast), box-shadow var(--transition-fast), border-color var(--transition-fast); }
  .dashboard-action-card:hover { text-decoration: none; transform: translateY(-2px); box-shadow: var(--shadow-sm); border-color: var(--color-royal); }
  .dashboard-action-card h3 { font-size: var(--text-md); margin-bottom: var(--space-1); }
  .dashboard-action-card p { color: var(--text-secondary); font-size: var(--text-sm); }
  .dashboard-action-card__icon { width: 38px; height: 38px; border-radius: var(--radius-md); display: grid; place-items: center; font-weight: var(--weight-bold); flex-shrink: 0; }
  .dashboard-action-card__icon--success { background: var(--color-success-bg); color: var(--status-success-text); }
  .dashboard-action-card__icon--brand { background: var(--surface-accent-subtle); color: var(--color-royal); }
  .dashboard-action-card__icon--info { background: var(--color-info-bg); color: var(--status-info-text); }
  .stat-card--live { border-color: var(--color-success-border); }
</style>
