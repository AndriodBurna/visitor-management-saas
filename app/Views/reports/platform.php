<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$summary = $summary ?? ['total' => 0, 'checkedOut' => 0, 'checkedIn' => 0, 'scheduled' => 0];
$byOrganization = $byOrganization ?? [];
$daily = $daily ?? [];
$dailyMap = [];
foreach ($daily as $row) { $dailyMap[$row['visit_date']] = (int) $row['total']; }
$rangeDays = max(1, (int) ((strtotime($to) - strtotime($from)) / 86400) + 1);
$peak = max(1, ...array_values($dailyMap));
?>
<div class="section-title">
  <div><span class="eyebrow">Platform intelligence</span><h2>Activity reports</h2><p class="text-muted">Compare visitor traffic and completed activity across every organization.</p></div>
  <a href="<?= site_url('visits/export') . '?' . http_build_query(['date' => $to]) ?>" class="btn btn-secondary btn-sm">Export visit CSV</a>
</div>
<div class="card report-filter-card">
  <form class="report-filters" method="get" action="<?= site_url('admin/reports') ?>">
    <label>From <input type="date" name="from" class="form-control" value="<?= esc($from) ?>"></label>
    <label>To <input type="date" name="to" class="form-control" value="<?= esc($to) ?>"></label>
    <button type="submit" class="btn btn-primary btn-sm">Apply range</button>
  </form>
  <span class="text-muted report-range"><?= $rangeDays ?> day<?= $rangeDays === 1 ? '' : 's' ?> selected</span>
</div>
<div class="stat-grid stat-grid--4 section">
  <?php foreach ([['Total visits', 'total', 'All check-ins in range'], ['Checked out', 'checkedOut', 'Completed visits'], ['On site now', 'checkedIn', 'Currently active'], ['Scheduled', 'scheduled', 'Pre-registered visits']] as [$label, $key, $hint]): ?>
    <div class="stat-card stat-card--platform"><div class="stat-card__label"><?= esc($label) ?></div><div class="stat-card__value"><?= (int) $summary[$key] ?></div><span class="stat-card__hint"><?= esc($hint) ?></span></div>
  <?php endforeach; ?>
</div>
<div class="grid-2 report-grid">
  <div class="card"><div class="card-header"><div><h2>Daily traffic</h2><p>Check-ins between <?= esc($from) ?> and <?= esc($to) ?></p></div></div><div class="report-chart">
    <?php if (!$dailyMap): ?><div class="empty-state"><h3>No visits in this range</h3><p>Try expanding the date range to see activity.</p></div><?php else: foreach ($dailyMap as $date => $value): ?><div class="report-bar"><div class="report-bar__value"><?= $value ?></div><div class="report-bar__track"><div class="report-bar__fill" style="height:<?= max(6, (int) round(($value / $peak) * 100)) ?>%"></div></div><div class="report-bar__label"><?= esc(date('d M', strtotime($date))) ?></div></div><?php endforeach; endif; ?>
  </div></div>
  <div class="card"><div class="card-header"><div><h2>Organization activity</h2><p>Highest traffic organizations in this range</p></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Organization</th><th>Visits</th><th>Completed</th><th>On site</th></tr></thead><tbody>
    <?php if (!$byOrganization): ?><tr><td colspan="4"><div class="empty-state"><h3>No organization activity</h3><p>There are no checked-in visits for this period.</p></div></td></tr><?php else: foreach ($byOrganization as $organization): ?><tr><td><strong><?= esc($organization['organization_name'] ?? 'Unassigned') ?></strong></td><td><?= (int) $organization['total'] ?></td><td class="cell-muted"><?= (int) $organization['completed'] ?></td><td><span class="badge badge-success"><?= (int) $organization['on_site'] ?></span></td></tr><?php endforeach; endif; ?>
  </tbody></table></div></div>
</div>
<?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
  .eyebrow { display:block; color:var(--color-royal); font-size:var(--text-xs); font-weight:var(--weight-bold); letter-spacing:.08em; text-transform:uppercase; margin-bottom:var(--space-1); }
  .report-filter-card { display:flex; align-items:flex-end; justify-content:space-between; gap:var(--space-5); margin-bottom:var(--space-5); padding:var(--space-4) var(--space-5); }
  .report-filters { display:flex; align-items:flex-end; gap:var(--space-3); flex-wrap:wrap; }
  .report-filters label { display:flex; flex-direction:column; gap:6px; color:var(--text-secondary); font-size:var(--text-xs); font-weight:var(--weight-semibold); }
  .report-filters input { min-width:150px; }
  .report-range { font-size:var(--text-sm); white-space:nowrap; }
  .report-grid { margin-bottom:var(--space-6); }
  .report-chart { display:flex; align-items:flex-end; gap:var(--space-2); min-height:250px; padding:var(--space-5); overflow-x:auto; }
  .report-bar { display:flex; flex:1 0 34px; flex-direction:column; align-items:center; justify-content:flex-end; gap:6px; min-height:210px; }
  .report-bar__value { color:var(--text-secondary); font-size:var(--text-xs); font-weight:var(--weight-semibold); }
  .report-bar__track { display:flex; align-items:flex-end; width:100%; height:150px; background:var(--surface-sunken); border-radius:var(--radius-sm); overflow:hidden; }
  .report-bar__fill { width:100%; background:var(--color-royal); border-radius:var(--radius-sm); min-height:6px; }
  .report-bar__label { color:var(--text-muted); font-size:10px; white-space:nowrap; }
  @media (max-width:720px) { .report-filter-card { align-items:stretch; flex-direction:column; } .report-range { align-self:flex-start; } .report-grid { display:block; } .report-grid > .card:first-child { margin-bottom:var(--space-5); } }
</style>
<?= $this->endSection() ?>
