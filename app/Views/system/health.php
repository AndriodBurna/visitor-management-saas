<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$database = $database ?? ['status' => 'unknown', 'detail' => 'Not checked'];
$storage = $storage ?? [];
$counts = $counts ?? [];
$runtime = $runtime ?? [];
$statusClass = static fn (string $status): string => match ($status) {
    'healthy' => 'badge-success',
    'warning' => 'badge-warning',
    'critical' => 'badge-danger',
    default => 'badge-neutral',
};
$statusLabel = static fn (string $status): string => ucfirst($status);
?>
<div class="section-title">
  <div><span class="eyebrow">Platform operations</span><h2>System health</h2><p class="text-muted">A live readout of the services that keep the visitor platform running.</p></div>
  <span class="badge <?= $statusClass($database['status']) ?>"><span class="badge__dot"></span><?= esc($statusLabel($database['status'])) ?></span>
</div>

<div class="stat-grid stat-grid--5 section">
  <?php foreach ([['Organizations', 'organizations'], ['Locations', 'locations'], ['Staff accounts', 'staff'], ['Visitors', 'visitors'], ['Recorded visits', 'visits']] as [$label, $key]): ?>
    <div class="stat-card stat-card--platform"><div class="stat-card__label"><?= esc($label) ?></div><div class="stat-card__value"><?= (int) ($counts[$key] ?? 0) ?></div><span class="stat-card__hint">Platform total</span></div>
  <?php endforeach; ?>
</div>

<div class="grid-2 system-health-grid">
  <div class="card">
    <div class="card-header"><div><h2>Core services</h2><p>Connection and storage readiness</p></div></div>
    <div class="health-check-list">
      <div class="health-check"><div><strong>Database</strong><small><?= esc($database['detail']) ?></small></div><span class="badge <?= $statusClass($database['status']) ?>"><?= esc($statusLabel($database['status'])) ?></span></div>
      <?php foreach ($storage as $item): ?>
        <div class="health-check"><div><strong><?= esc($item['label']) ?> storage</strong><small><?= esc($item['path']) ?> · <?= esc($item['detail']) ?></small></div><span class="badge <?= $statusClass($item['status']) ?>"><?= esc($statusLabel($item['status'])) ?></span></div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><div><h2>Runtime</h2><p>Current application environment</p></div></div>
    <dl class="runtime-list">
      <?php foreach ($runtime as $label => $value): ?><div><dt><?= esc(ucfirst($label)) ?></dt><dd><?= esc((string) $value) ?></dd></div><?php endforeach; ?>
    </dl>
  </div>
</div>

<div class="card system-health-note">
  <div class="card-header"><div><h2>Audit coverage</h2><p>Security events currently retained by the platform.</p></div><a href="<?= site_url('audit-log') ?>" class="btn btn-secondary btn-sm">Open audit log</a></div>
  <div class="audit-coverage"><strong><?= (int) ($counts['audit'] ?? 0) ?></strong><span>recorded audit events</span></div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
  .eyebrow { display:block; color:var(--color-royal); font-size:var(--text-xs); font-weight:var(--weight-bold); letter-spacing:.08em; text-transform:uppercase; margin-bottom:var(--space-1); }
  .system-health-grid { margin-bottom:var(--space-5); }
  .health-check-list { padding:0 var(--space-5) var(--space-3); }
  .health-check { display:flex; align-items:center; justify-content:space-between; gap:var(--space-4); padding:var(--space-4) 0; border-bottom:1px solid var(--border-default); }
  .health-check:last-child { border-bottom:0; }
  .health-check strong, .health-check small { display:block; }
  .health-check small { color:var(--text-muted); font-size:var(--text-xs); margin-top:3px; word-break:break-word; }
  .runtime-list { margin:0; padding:0 var(--space-5) var(--space-3); }
  .runtime-list div { display:flex; justify-content:space-between; gap:var(--space-4); padding:var(--space-4) 0; border-bottom:1px solid var(--border-default); }
  .runtime-list div:last-child { border-bottom:0; }
  .runtime-list dt { color:var(--text-secondary); }
  .runtime-list dd { margin:0; color:var(--text-primary); font-family:monospace; font-size:var(--text-sm); text-align:right; }
  .system-health-note { margin-bottom:var(--space-6); }
  .audit-coverage { display:flex; align-items:baseline; gap:var(--space-3); padding:0 var(--space-5) var(--space-5); }
  .audit-coverage strong { font-size:var(--text-3xl); }
  .audit-coverage span { color:var(--text-muted); }
  @media (max-width:720px) { .section-title { align-items:flex-start; } .runtime-list div { display:block; } .runtime-list dd { text-align:left; margin-top:4px; } }
</style>
<?= $this->endSection() ?>
