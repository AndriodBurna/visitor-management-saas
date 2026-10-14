<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Expects (passed by controller):
//   $platformStats = ['organizations' => 0, 'locations' => 0, 'staff' => 0, 'visitors' => 0, 'visits' => 0]
//   $stats         = ['onSiteNow' => 0, 'checkedInToday' => 0, 'preRegistered' => 0, 'avgVisitMinutes' => 0]
//   $recentVisitors = [ ['name'=>'','company'=>'','host'=>'','status'=>'checked_in|checked_out|pending|denied','time'=>'','initials'=>''], ... ]
//   $upcomingVisits = [ ['name'=>'','host'=>'','time'=>'','purpose'=>''], ... ]
$platformStats = $platformStats ?? ['organizations' => 0, 'locations' => 0, 'staff' => 0, 'visitors' => 0, 'visits' => 0];
$stats          = $stats ?? ['onSiteNow' => 0, 'checkedInToday' => 0, 'preRegistered' => 0, 'avgVisitMinutes' => 0];
$recentVisitors = $recentVisitors ?? [];
$upcomingVisits = $upcomingVisits ?? [];
$recentAudit = $recentAudit ?? [];
$dailyVisits = $dailyVisits ?? [];
$organizationHealth = $organizationHealth ?? [];

$visitsByDay = [];
foreach ($dailyVisits as $dailyVisit) {
  $visitsByDay[$dailyVisit['visit_date']] = (int) $dailyVisit['total'];
}
$dailyLabels = [];
$dailyValues = [];
for ($dayOffset = 6; $dayOffset >= 0; $dayOffset--) {
  $date = date('Y-m-d', strtotime('-' . $dayOffset . ' days'));
  $dailyLabels[] = date('D', strtotime($date));
  $dailyValues[] = $visitsByDay[$date] ?? 0;
}
$dailyMax = max(1, ...$dailyValues);

$statusMap = [
    'checked_in'  => ['class' => 'badge-success', 'label' => 'On site'],
    'checked_out' => ['class' => 'badge-neutral', 'label' => 'Checked out'],
    'pending'     => ['class' => 'badge-warning', 'label' => 'Pending'],
    'denied'      => ['class' => 'badge-danger',  'label' => 'Denied'],
];
?>

<div class="super-dashboard-intro">
  <div>
    <span class="super-dashboard-intro__eyebrow">Platform command center</span>
    <h1>Good <?= date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening') ?>, Admin</h1>
    <p>Here is the latest pulse from your visitor network.</p>
  </div>
  <div class="super-dashboard-intro__actions">
    <a href="<?= site_url('organizations') ?>" class="btn btn-secondary btn-sm">Manage organizations</a>
    <a href="<?= site_url('visitors/checkin') ?>" class="btn btn-primary btn-sm"><span aria-hidden="true">+</span> New check-in</a>
  </div>
</div>

<div class="platform-pulse">
  <div class="platform-pulse__signal"><span></span><span></span><span></span></div>
  <div>
    <strong>Platform is operational</strong>
    <p>Monitoring activity across <?= (int) $platformStats['locations'] ?> active locations</p>
  </div>
  <div class="platform-pulse__meta"><span class="badge badge-success"><span class="badge__dot"></span>Live now</span><span><?= date('D, d M Y') ?></span></div>
</div>

<!-- ============================== Platform Overview ============================== -->
<div class="dashboard-section-head">
  <div class="dashboard-section-head__icon">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
  </div>
  <div>
    <h2>Platform overview</h2>
    <p>Everything provisioned across every organization on this platform</p>
  </div>
</div>

<div class="stat-grid stat-grid--5 section platform-metrics">
  <div class="stat-card stat-card--platform stat-card--primary">
    <div class="stat-card__label">
      Total Organizations
      <span class="stat-card__icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
      </span>
    </div>
    <div class="stat-card__value"><?= (int) $platformStats['organizations'] ?></div>
    <a href="<?= site_url('organizations') ?>" class="stat-card__link">View all organizations →</a>
  </div>

  <div class="stat-card stat-card--platform stat-card--secondary">
    <div class="stat-card__label">
      Total Locations
      <span class="stat-card__icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      </span>
    </div>
    <div class="stat-card__value"><?= (int) $platformStats['locations'] ?></div>
    <span class="stat-card__hint">Across all organizations</span>
  </div>

  <div class="stat-card stat-card--platform stat-card--tertiary">
    <div class="stat-card__label">
      Total Staff Accounts
      <span class="stat-card__icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </span>
    </div>
    <div class="stat-card__value"><?= (int) $platformStats['staff'] ?></div>
    <span class="stat-card__hint">Admins, managers &amp; receptionists</span>
  </div>

  <div class="stat-card stat-card--platform stat-card--quaternary">
    <div class="stat-card__label">Total Visitors</div>
    <div class="stat-card__value"><?= (int) $platformStats['visitors'] ?></div>
    <a href="<?= site_url('visitors') ?>" class="stat-card__link">Open visitor directory</a>
  </div>

  <div class="stat-card stat-card--platform stat-card--quinary">
    <div class="stat-card__label">Total Visits</div>
    <div class="stat-card__value"><?= (int) $platformStats['visits'] ?></div>
    <a href="<?= site_url('visits') ?>" class="stat-card__link">Review visit activity</a>
  </div>
</div>

<!-- ============================== Visitor Operations ============================== -->
<div class="dashboard-section-head">
  <div class="dashboard-section-head__icon">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
  </div>
  <div>
    <h2>Visitor operations</h2>
    <p>Live front-desk activity across the platform, right now</p>
  </div>
</div>

<div class="stat-grid section">
  <div class="stat-card">
    <div class="stat-card__label">
      Visitors On Site
      <span class="stat-card__icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      </span>
    </div>
    <div class="stat-card__value"><?= (int) $stats['onSiteNow'] ?></div>
    <span class="badge badge-success"><span class="badge__dot"></span>Live</span>
  </div>

  <div class="stat-card">
    <div class="stat-card__label">Checked In Today</div>
    <div class="stat-card__value"><?= (int) $stats['checkedInToday'] ?></div>
    <span class="stat-card__hint">Across all locations</span>
  </div>

  <div class="stat-card">
    <div class="stat-card__label">Pre-Registered</div>
    <div class="stat-card__value"><?= (int) $stats['preRegistered'] ?></div>
    <span class="stat-card__hint">Scheduled for today</span>
  </div>

  <div class="stat-card">
    <div class="stat-card__label">Average Visit Duration</div>
    <div class="stat-card__value"><?= (int) $stats['avgVisitMinutes'] ?>m</div>
    <span class="stat-card__hint">Based on completed visits</span>
  </div>
</div>

<div class="dashboard-insights section">
  <div class="card traffic-card">
    <div class="card-header">
      <div><h2>Visit traffic</h2><p>Check-ins recorded over the last seven days</p></div>
      <span class="insight-total"><?= array_sum($dailyValues) ?> <small>visits</small></span>
    </div>
    <div class="traffic-chart" aria-label="Seven-day visit traffic">
      <?php foreach ($dailyValues as $index => $value): ?>
        <div class="traffic-chart__day">
          <span class="traffic-chart__value"><?= $value ?></span>
          <div class="traffic-chart__track"><div class="traffic-chart__bar" style="height: <?= max(5, (int) round(($value / $dailyMax) * 100)) ?>%;"></div></div>
          <span class="traffic-chart__label"><?= esc($dailyLabels[$index]) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card health-card">
    <div class="card-header">
      <div><h2>Organization health</h2><p>Most active tenants on the platform</p></div>
      <a href="<?= site_url('organizations') ?>" class="btn btn-secondary btn-sm">All organizations</a>
    </div>
    <div class="health-list">
      <?php if (empty($organizationHealth)): ?>
        <div class="empty-state"><h3>No organizations yet</h3><p>Create an organization to see platform health.</p></div>
      <?php else: ?>
        <?php foreach ($organizationHealth as $organization): ?>
          <?php $healthStatus = $organization['status'] ?? 'active'; ?>
          <a class="health-row" href="<?= site_url('organizations/' . (int) $organization['id']) ?>">
            <span class="health-row__avatar"><?= esc(strtoupper(substr($organization['name'] ?? '?', 0, 1))) ?></span>
            <span class="health-row__name"><?= esc($organization['name'] ?? '') ?><small><?= (int) ($organization['locations'] ?? 0) ?> locations · <?= (int) ($organization['staff'] ?? 0) ?> staff</small></span>
            <span class="health-row__metric"><strong><?= (int) ($organization['on_site'] ?? 0) ?></strong><small>on site</small></span>
            <span class="badge <?= $healthStatus === 'active' ? 'badge-success' : ($healthStatus === 'suspended' ? 'badge-danger' : 'badge-neutral') ?>"><?= esc(ucfirst($healthStatus)) ?></span>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ============================== Activity ============================== -->
<div class="grid-2">
  <div class="card">
    <div class="card-header">
      <div>
        <h2>Recent activity</h2>
        <p>Latest check-ins and check-outs across all organizations</p>
      </div>
      <a href="<?= site_url('visitors') ?>" class="btn btn-secondary btn-sm">View all</a>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Visitor</th>
            <th>Host</th>
            <th>Status</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentVisitors)): ?>
            <tr><td colspan="4">
              <div class="empty-state">
                <div class="empty-state__icon">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
                <h3>No activity yet today</h3>
                <p>Check-ins will appear here as visitors arrive across your organizations.</p>
              </div>
            </td></tr>
          <?php else: ?>
            <?php foreach ($recentVisitors as $v): ?>
              <?php $status = $statusMap[$v['status']] ?? $statusMap['pending']; ?>
              <tr>
                <td>
                  <div class="table-person">
                    <div class="avatar"><?= esc($v['initials'] ?? strtoupper(substr($v['name'] ?? '?', 0, 1))) ?></div>
                    <div class="table-person__meta">
                      <div class="table-person__name"><?= esc($v['name'] ?? '') ?></div>
                      <div class="table-person__sub"><?= esc($v['company'] ?? '') ?></div>
                    </div>
                  </div>
                </td>
                <td class="cell-muted"><?= esc($v['host'] ?? '—') ?></td>
                <td><span class="badge <?= $status['class'] ?>"><span class="badge__dot"></span><?= $status['label'] ?></span></td>
                <td class="cell-muted"><?= esc($v['time'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <h2>Expected today</h2>
        <p>Pre-registered visitors across the platform</p>
      </div>
    </div>
    <div class="card-body--tight">
      <?php if (empty($upcomingVisits)): ?>
        <div class="empty-state">
          <div class="empty-state__icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <h3>Nothing scheduled</h3>
          <p>Pre-registered visits will show up here.</p>
        </div>
      <?php else: ?>
        <?php foreach ($upcomingVisits as $u): ?>
          <div class="d-flex justify-between align-center" style="padding: var(--space-3) 0; border-bottom: 1px solid var(--border-default);">
            <div class="d-flex align-center gap-3">
              <div class="avatar" style="background: var(--gray-100); color: var(--text-secondary);"><?= esc(strtoupper(substr($u['name'] ?? '?', 0, 1))) ?></div>
              <div class="table-person__meta">
                <div class="table-person__name"><?= esc($u['name'] ?? '') ?></div>
                <div class="table-person__sub">Visiting <?= esc($u['host'] ?? '—') ?> · <?= esc($u['purpose'] ?? '') ?></div>
              </div>
            </div>
            <div class="text-muted" style="font-size: var(--text-sm); font-weight: 500;"><?= esc($u['time'] ?? '') ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div><h2>Security activity</h2><p>Latest recorded platform actions</p></div>
      <a href="<?= site_url('audit-log') ?>" class="btn btn-secondary btn-sm">View log</a>
    </div>
    <div class="audit-feed">
      <?php if (!$recentAudit): ?>
        <div class="empty-state"><div class="empty-state__icon">A</div><h3>No activity recorded</h3><p>Platform actions will appear here.</p></div>
      <?php else: ?>
        <?php foreach ($recentAudit as $audit): ?>
          <div class="audit-feed__item">
            <div class="audit-feed__marker"></div>
            <div class="audit-feed__body"><strong><?= esc($audit['description'] ?? 'Platform action') ?></strong><span><?= esc($audit['actor_name'] ?? 'System') ?> · <?= !empty($audit['created_at']) ? esc(date('d M, H:i', strtotime($audit['created_at']))) : '—' ?></span></div>
            <span class="badge badge-neutral"><?= esc(ucwords(str_replace('_', ' ', $audit['action'] ?? 'event'))) ?></span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
  .super-dashboard-intro {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: var(--space-6);
    padding: var(--space-2) 0 var(--space-6);
  }
  .super-dashboard-intro__eyebrow {
    display: block;
    color: var(--color-royal);
    font-size: var(--text-xs);
    font-weight: var(--weight-bold);
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin-bottom: var(--space-2);
  }
  .super-dashboard-intro h1 {
    font-size: var(--text-3xl);
    line-height: var(--leading-tight);
    letter-spacing: 0;
  }
  .super-dashboard-intro p { color: var(--text-secondary); margin-top: var(--space-2); }
  .super-dashboard-intro__actions { display: flex; gap: var(--space-2); flex-wrap: wrap; justify-content: flex-end; }

  .platform-pulse {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    margin-bottom: var(--space-8);
    padding: var(--space-4) var(--space-5);
    border: 1px solid var(--color-success-border);
    border-left: 4px solid var(--color-success);
    border-radius: var(--radius-md);
    background: var(--color-success-bg);
  }
  .platform-pulse__signal { display: flex; align-items: center; gap: 3px; height: 28px; padding: 0 3px; }
  .platform-pulse__signal span { display: block; width: 3px; border-radius: var(--radius-full); background: var(--color-success); }
  .platform-pulse__signal span:nth-child(1) { height: 9px; opacity: 0.55; }
  .platform-pulse__signal span:nth-child(2) { height: 18px; }
  .platform-pulse__signal span:nth-child(3) { height: 13px; opacity: 0.75; }
  .platform-pulse strong { display: block; color: var(--status-success-text); font-size: var(--text-sm); }
  .platform-pulse p { color: var(--status-success-text); font-size: var(--text-xs); margin-top: 2px; opacity: 0.85; }
  .platform-pulse__meta { display: flex; align-items: center; gap: var(--space-4); margin-left: auto; color: var(--status-success-text); font-size: var(--text-xs); font-weight: var(--weight-medium); white-space: nowrap; }

  .dashboard-insights { display: grid; grid-template-columns: 1.05fr 1.95fr; gap: var(--space-5); }
  .traffic-card, .health-card { min-width: 0; }
  .insight-total { color: var(--text-primary); font-size: var(--text-xl); font-weight: var(--weight-bold); font-variant-numeric: tabular-nums; }
  .insight-total small { color: var(--text-muted); font-size: var(--text-xs); font-weight: var(--weight-medium); }
  .traffic-chart { display: flex; align-items: flex-end; gap: var(--space-3); height: 190px; padding: var(--space-5) var(--space-6) var(--space-6); }
  .traffic-chart__day { display: flex; flex: 1; align-items: center; flex-direction: column; justify-content: flex-end; height: 100%; gap: var(--space-2); }
  .traffic-chart__value { color: var(--text-secondary); font-size: 11px; font-variant-numeric: tabular-nums; min-height: 14px; }
  .traffic-chart__track { display: flex; align-items: flex-end; width: 100%; max-width: 30px; height: 112px; border-radius: 5px; background: var(--surface-sunken); overflow: hidden; }
  .traffic-chart__bar { width: 100%; min-height: 5px; border-radius: 5px 5px 0 0; background: linear-gradient(180deg, var(--color-sky), var(--color-royal)); transition: height var(--transition-base); }
  .traffic-chart__label { color: var(--text-muted); font-size: 11px; font-weight: var(--weight-medium); }
  .health-list { padding: 0 var(--space-5); }
  .health-row { display: flex; align-items: center; gap: var(--space-3); padding: var(--space-3) 0; border-bottom: 1px solid var(--border-default); color: inherit; }
  .health-row:last-child { border-bottom: 0; }
  .health-row:hover { text-decoration: none; }
  .health-row__avatar { display: grid; place-items: center; width: 32px; height: 32px; flex: 0 0 32px; border-radius: var(--radius-md); background: var(--surface-dark); color: var(--color-sky); font-size: var(--text-xs); font-weight: var(--weight-bold); }
  .health-row__name { display: flex; flex: 1; flex-direction: column; min-width: 0; color: var(--text-primary); font-size: var(--text-sm); font-weight: var(--weight-semibold); }
  .health-row__name small, .health-row__metric small { color: var(--text-muted); font-size: 11px; font-weight: var(--weight-normal); margin-top: 2px; }
  .health-row__name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .health-row__metric { display: flex; flex-direction: column; align-items: flex-end; color: var(--status-success-text); font-size: var(--text-sm); min-width: 58px; }
  .health-row__metric strong { font-variant-numeric: tabular-nums; }

  .dashboard-section-head {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    margin-bottom: var(--space-4);
  }
  .dashboard-section-head__icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-md);
    background: var(--surface-dark);
    color: var(--color-sky);
    /* color: #000; */
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .dashboard-section-head h2 {
    font-size: var(--text-md);
    line-height: var(--leading-tight);
  }
  .dashboard-section-head p {
    font-size: var(--text-sm);
    color: var(--text-muted);
    margin-top: 1px;
  }

  .stat-grid--5 { grid-template-columns: repeat(5, 1fr); }

  .stat-card--platform {
    background: var(--surface-dark);
    border-color: var(--surface-dark);
    min-height: 126px;
    position: relative;
    overflow: hidden;
  }
  .stat-card--platform::after { content: ''; position: absolute; width: 84px; height: 84px; right: -30px; bottom: -34px; border: 1px solid rgba(255,255,255,0.08); border-radius: 50%; box-shadow: 0 0 0 12px rgba(255,255,255,0.025), 0 0 0 24px rgba(255,255,255,0.02); }
  .stat-card--primary { border-top: 3px solid var(--color-royal); }
  .stat-card--secondary { border-top: 3px solid var(--color-sky); }
  .stat-card--tertiary { border-top: 3px solid var(--color-success); }
  .stat-card--quaternary { border-top: 3px solid var(--color-warning); }
  .stat-card--quinary { border-top: 3px solid var(--color-info); }
  .stat-card--platform .stat-card__label { color: var(--gray-400); }
  .stat-card--platform .stat-card__value { color: var(--color-white); }
  .stat-card--platform .stat-card__icon { background: rgba(56, 189, 248, 0.14); color: var(--color-sky); }
  .stat-card--platform .stat-card__hint { color: var(--gray-500); }
  .stat-card--platform .stat-card__link {
    display: inline-block;
    margin-top: var(--space-2);
    font-size: var(--text-xs);
    font-weight: var(--weight-medium);
    color: var(--color-sky);
  }
  .stat-card--platform .stat-card__link:hover { color: var(--color-white); }

  .stat-card__hint {
    display: inline-block;
    margin-top: var(--space-2);
    font-size: var(--text-xs);
    color: var(--text-muted);
  }

  .audit-feed { padding: 0 var(--space-6); }
  .audit-feed__item { display: flex; align-items: center; gap: var(--space-3); padding: var(--space-3) 0; border-bottom: 1px solid var(--border-default); }
  .audit-feed__item:last-child { border-bottom: 0; }
  .audit-feed__marker { width: 9px; height: 9px; border-radius: 50%; background: var(--color-royal); box-shadow: 0 0 0 4px var(--surface-accent-subtle); flex-shrink: 0; }
  .audit-feed__body { display: flex; flex-direction: column; gap: 2px; flex: 1; min-width: 0; }
  .audit-feed__body strong { font-size: var(--text-sm); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .audit-feed__body span { color: var(--text-muted); font-size: var(--text-xs); }
  @media (max-width: 640px) {
    .super-dashboard-intro { align-items: flex-start; flex-direction: column; padding-bottom: var(--space-5); }
    .super-dashboard-intro__actions { justify-content: flex-start; width: 100%; }
    .platform-pulse { align-items: flex-start; gap: var(--space-3); }
    .platform-pulse__meta { align-items: flex-end; flex-direction: column; gap: var(--space-1); }
    .audit-feed { padding: 0 var(--space-4); }
    .audit-feed__item .badge { display: none; }
  }

  @media (max-width: 1200px) {
    .stat-grid--5 { grid-template-columns: repeat(3, 1fr); }
  }
  @media (max-width: 768px) {
    .stat-grid--5 { grid-template-columns: 1fr; }
    .dashboard-insights { grid-template-columns: 1fr; }
  }
</style>
<?= $this->endSection() ?>
