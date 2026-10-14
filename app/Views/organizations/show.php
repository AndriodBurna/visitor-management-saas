<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Expects (passed by controller):
//   $organization  array  ['id','name','slug','email','phone','address','status','createdAt']
//   $title         string
//   $activeMenu    string
$organization = $organization ?? [];
$stats = $stats ?? ['branches' => 0, 'staff' => 0, 'visitors' => 0, 'visits' => 0, 'on_site' => 0];

$statusMap = [
    'active'    => ['class' => 'badge-success', 'label' => 'Active'],
    'inactive'  => ['class' => 'badge-neutral', 'label' => 'Inactive'],
    'suspended' => ['class' => 'badge-danger',  'label' => 'Suspended'],
];
$status = $statusMap[$organization['status'] ?? 'active'] ?? $statusMap['active'];
?>

<div class="breadcrumb" style="margin-bottom: var(--space-3);">
  <a href="<?= site_url('organizations') ?>">Organizations</a>
  <span class="breadcrumb__sep">/</span>
  <span><?= esc($organization['name'] ?? 'Organization') ?></span>
</div>

<div class="section-title">
  <div class="d-flex align-center gap-3">
    <div class="avatar avatar--lg" style="background: var(--surface-dark); border-radius: var(--radius-md);">
      <?= esc(strtoupper(substr($organization['name'] ?? '?', 0, 2))) ?>
    </div>
    <div>
      <div class="d-flex align-center gap-2">
        <h2><?= esc($organization['name'] ?? 'Organization') ?></h2>
        <span class="badge <?= $status['class'] ?>"><span class="badge__dot"></span><?= $status['label'] ?></span>
      </div>
      <?php if (!empty($organization['slug'])): ?>
        <p class="text-muted font-mono" style="font-size: var(--text-sm); margin-top: 2px;"><?= esc($organization['slug']) ?></p>
      <?php endif; ?>
    </div>
  </div>

  <div class="d-flex gap-3">
    <a href="<?= site_url('organizations') ?>" class="btn btn-secondary">Back to Organizations</a>
    <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/lifecycle') ?>" class="btn btn-secondary">Lifecycle</a>
    <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches') ?>" class="btn btn-secondary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
      Manage Locations
    </a>
    <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/edit') ?>" class="btn btn-primary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit Organization
    </a>
    <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/users') ?>"class="btn btn-secondary">
        Staff & Users
    </a>
  </div>
</div>

<div class="stat-grid organization-stats">
  <div class="stat-card"><div class="stat-card__label">Locations</div><div class="stat-card__value"><?= (int) $stats['branches'] ?></div><a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches') ?>" class="stat-card__hint">Manage locations</a></div>
  <div class="stat-card"><div class="stat-card__label">Staff accounts</div><div class="stat-card__value"><?= (int) $stats['staff'] ?></div><a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/users') ?>" class="stat-card__hint">Manage staff</a></div>
  <div class="stat-card"><div class="stat-card__label">Registered visitors</div><div class="stat-card__value"><?= (int) $stats['visitors'] ?></div><span class="stat-card__hint">People on file</span></div>
  <div class="stat-card stat-card--live"><div class="stat-card__label">Currently inside</div><div class="stat-card__value"><?= (int) $stats['on_site'] ?></div><a href="<?= site_url('visits') ?>" class="stat-card__hint">View activity</a></div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-header">
      <div>
        <h2>Organization details</h2>
        <p>Core information used across the platform, from a single front desk to a multi-location deployment.</p>
      </div>
    </div>

    <div class="card-body">
      <dl class="detail-list">
        <div class="detail-list__row">
          <dt>Organization name</dt>
          <dd><?= esc($organization['name'] ?? '—') ?></dd>
        </div>
        <div class="detail-list__row">
          <dt>Slug</dt>
          <dd class="font-mono"><?= esc($organization['slug'] ?? '—') ?></dd>
        </div>
        <div class="detail-list__row">
          <dt>Email</dt>
          <dd><?= esc($organization['email'] ?? '—') ?></dd>
        </div>
        <div class="detail-list__row">
          <dt>Phone</dt>
          <dd><?= esc($organization['phone'] ?? '—') ?></dd>
        </div>
        <div class="detail-list__row">
          <dt>Address</dt>
          <dd><?= nl2br(esc($organization['address'] ?? '—')) ?></dd>
        </div>
        <div class="detail-list__row">
          <dt>Status</dt>
          <dd><span class="badge <?= $status['class'] ?>"><span class="badge__dot"></span><?= $status['label'] ?></span></dd>
        </div>
        <div class="detail-list__row">
          <dt>Date created</dt>
          <dd>
            <?= !empty($organization['created_at'])
                ? esc(date('d M Y', strtotime($organization['created_at'])))
                : '—' ?>
          </dd>
        </div>
      </dl>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h2>Locations</h2>
    </div>
    <div class="card-body">
      <p class="text-secondary" style="font-size: var(--text-sm); line-height: var(--leading-relaxed); margin-bottom: var(--space-5);">
        Whether this organization operates from a single front desk or across
        many sites, locations are managed separately so hosts, badges and
        visitor logs stay organized per address.
      </p>
      <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches') ?>" class="btn btn-secondary btn-block">
        View locations
      </a>
    </div>
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
  .organization-stats { grid-template-columns: repeat(4, 1fr); margin-top: var(--space-6); }
  .organization-stats .stat-card__hint { display: inline-block; margin-top: var(--space-2); }
  .organization-stats .stat-card--live { border-color: var(--color-success-border); }
  @media (max-width: 960px) { .organization-stats { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 640px) { .organization-stats { grid-template-columns: 1fr; } }
</style>
<?= $this->endSection() ?>
