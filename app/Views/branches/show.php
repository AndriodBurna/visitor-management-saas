<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Expects (passed by controller):
//   $organization  array  ['id','name', ...]
//   $branch        array  ['id','name','code','phone','address','status','createdAt']
//   $title         string
//   $activeMenu    string
$organization = $organization ?? [];
$branch       = $branch ?? [];

$statusMap = [
    'active'    => ['class' => 'badge-success', 'label' => 'Active'],
    'inactive'  => ['class' => 'badge-neutral', 'label' => 'Inactive'],
    'suspended' => ['class' => 'badge-danger',  'label' => 'Suspended'],
];
$status = $statusMap[$branch['status'] ?? 'active'] ?? $statusMap['active'];
?>

<div class="breadcrumb" style="margin-bottom: var(--space-3);">
  <a href="<?= site_url('organizations') ?>">Organizations</a>
  <span class="breadcrumb__sep">/</span>
  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '')) ?>"><?= esc($organization['name'] ?? 'Organization') ?></a>
  <span class="breadcrumb__sep">/</span>
  <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches') ?>">Locations</a>
  <span class="breadcrumb__sep">/</span>
  <span><?= esc($branch['name'] ?? 'Location') ?></span>
</div>

<div class="section-title">
  <div class="d-flex align-center gap-3">
    <div class="avatar avatar--lg" style="background: var(--surface-dark); border-radius: var(--radius-md);">
      <?= esc(strtoupper(substr($branch['name'] ?? '?', 0, 2))) ?>
    </div>
    <div>
      <div class="d-flex align-center gap-2">
        <h2><?= esc($branch['name'] ?? 'Location') ?></h2>
        <span class="badge <?= $status['class'] ?>"><span class="badge__dot"></span><?= $status['label'] ?></span>
      </div>
      <p class="text-muted" style="font-size: var(--text-sm); margin-top: 2px;">
        Part of <?= esc($organization['name'] ?? 'this organization') ?>
      </p>
    </div>
  </div>

  <div class="d-flex gap-3">
    <a href="<?= site_url('organizations/' . ($organization['id'] ?? '')) ?>" class="btn btn-secondary">Back to Organization</a>
    <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches') ?>" class="btn btn-secondary">Back to Locations</a>
    <a href="<?= site_url('organizations/' . ($organization['id'] ?? '') . '/branches/' . ($branch['id'] ?? '') . '/edit') ?>" class="btn btn-primary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit Location
    </a>
  </div>
</div>

<div class="card" style="max-width: 720px;">
  <div class="card-header">
    <div>
      <h2>Location details</h2>
      <p>Whether this is the only address this organization operates from, or one of many, its details are managed here.</p>
    </div>
  </div>

  <div class="card-body">
    <dl class="detail-list">
      <div class="detail-list__row">
        <dt>Location name</dt>
        <dd><?= esc($branch['name'] ?? '—') ?></dd>
      </div>
      <div class="detail-list__row">
        <dt>Location code</dt>
        <dd class="font-mono"><?= esc($branch['code'] ?? '—') ?></dd>
      </div>
      <div class="detail-list__row">
        <dt>Phone</dt>
        <dd><?= esc($branch['phone'] ?? '—') ?></dd>
      </div>
      <div class="detail-list__row">
        <dt>Address</dt>
        <dd><?= nl2br(esc($branch['address'] ?? '—')) ?></dd>
      </div>
      <div class="detail-list__row">
        <dt>Status</dt>
        <dd><span class="badge <?= $status['class'] ?>"><span class="badge__dot"></span><?= $status['label'] ?></span></dd>
      </div>
      <div class="detail-list__row">
        <dt>Date created</dt>
        <dd>
          <?= !empty($branch['created_at'])
              ? esc(date('d M Y', strtotime($branch['created_at'])))
              : '—' ?>
        </dd>
      </div>
      <div class="detail-list__row">
        <dt>Organization</dt>
        <dd><a href="<?= site_url('organizations/' . ($organization['id'] ?? '')) ?>"><?= esc($organization['name'] ?? '—') ?></a></dd>
      </div>
    </dl>
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
</style>
<?= $this->endSection() ?>
