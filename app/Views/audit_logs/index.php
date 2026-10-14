<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
use CodeIgniter\I18n\Time;

$logs = $logs ?? [];
$filters = $filters ?? ['q' => '', 'action' => ''];
$actionOptions = $actionOptions ?? [];
$actionLabels = [
    'login' => 'Sign in', 'logout' => 'Sign out', 'create' => 'Created',
    'update' => 'Updated', 'visitor_created' => 'Visitor created', 'check_in' => 'Check-in', 'checkout' => 'Check-out',
];
$actionClasses = [
    'login' => 'audit-action--info', 'logout' => 'audit-action--neutral',
    'create' => 'audit-action--success', 'visitor_created' => 'audit-action--success', 'update' => 'audit-action--warning',
    'check_in' => 'audit-action--success', 'checkout' => 'audit-action--orange',
];
?>
<div class="section-title">
  <div><span class="eyebrow">Platform security</span><h2>Audit log</h2><p class="text-muted">Trace important actions across your Visitor Management workspace.</p></div>
  <div class="d-flex align-center gap-2"><a href="<?= site_url('audit-log/export') . '?' . http_build_query($filters) ?>" class="btn btn-secondary btn-sm">Export CSV</a>
  <span class="audit-trust"><span class="audit-trust__dot"></span>Recording activity</span>
  </div>
</div>
<div class="card">
  <form class="table-toolbar" method="get" action="<?= site_url('audit-log') ?>">
    <div class="table-toolbar__filters">
      <div class="input-icon-wrap audit-search"><span class="input-icon" aria-hidden="true">⌕</span><input type="search" name="q" class="form-control" placeholder="Search actor, entity, description" value="<?= esc($filters['q']) ?>"></div>
      <select name="action" class="form-control" aria-label="Filter by action"><option value="">All actions</option><?php foreach ($actionOptions as $option): ?><option value="<?= esc($option) ?>" <?= $filters['action'] === $option ? 'selected' : '' ?>><?= esc($actionLabels[$option] ?? ucwords(str_replace('_', ' ', $option))) ?></option><?php endforeach; ?></select>
      <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
      <?php if ($filters['q'] || $filters['action']): ?><a href="<?= site_url('audit-log') ?>" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
    </div>
    <span class="audit-count"><?= count($logs) ?> entr<?= count($logs) === 1 ? 'y' : 'ies' ?></span>
  </form>
  <div class="table-wrap"><table class="data-table audit-table"><thead><tr><th>When</th><th>Performed by</th><th>Action</th><th>Target</th><th>Activity</th><th>IP address</th></tr></thead><tbody>
    <?php if (!$logs): ?><tr><td colspan="6"><div class="empty-state"><div class="empty-state__icon">A</div><h3>No audit entries found</h3><p>Try a different filter or wait for the next recorded platform action.</p></div></td></tr>
    <?php else: foreach ($logs as $log): ?><?php $action = $log['action'] ?? ''; ?><tr>
      <?php $localTime = !empty($log['created_at']) ? Time::parse($log['created_at'], 'UTC')->setTimezone('Africa/Kampala') : null; ?>
      <td class="audit-time"><strong><?= $localTime ? esc($localTime->format('d M Y')) : '—' ?></strong><span><?= $localTime ? esc($localTime->format('H:i')) : '' ?> Kampala time</span></td>
      <td><div class="table-person"><span class="avatar audit-avatar"><?= esc(strtoupper(substr($log['actor_name'] ?? 'S', 0, 1))) ?></span><span class="table-person__meta"><span class="table-person__name"><?= esc($log['actor_name'] ?? 'System') ?></span><span class="table-person__sub">ID <?= !empty($log['actor_id']) ? (int) $log['actor_id'] : 'system' ?></span></span></div></td>
      <td><span class="audit-action <?= esc($actionClasses[$action] ?? 'audit-action--neutral') ?>"><span class="audit-action__dot"></span><?= esc($actionLabels[$action] ?? ucwords(str_replace('_', ' ', $action))) ?></span></td>
      <td class="cell-muted"><?= esc($log['entity'] ?? '—') ?><?= !empty($log['entity_id']) ? ' #' . (int) $log['entity_id'] : '' ?></td>
      <td><span class="audit-description"><?= esc($log['description'] ?? '—') ?></span><?php if (!empty($log['organization_id']) || !empty($log['branch_id'])): ?><span class="table-person__sub">Scope: <?= !empty($log['organization_id']) ? 'Org #' . (int) $log['organization_id'] : 'Platform' ?><?= !empty($log['branch_id']) ? ' · Branch #' . (int) $log['branch_id'] : '' ?></span><?php endif; ?></td>
      <td class="audit-ip"><span class="font-mono"><?= esc($log['ipaddress'] ?? $log['ip_address'] ?? 'Not recorded') ?></span></td>
    </tr><?php endforeach; endif; ?>
  </tbody></table></div>
  <?php if ($logs): ?><div class="pagination-bar"><span>Showing <?= count($logs) ?> entries</span><div class="pagination"><?= $pager->links() ?></div></div><?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
  .eyebrow { display: block; color: var(--color-royal); font-size: var(--text-xs); font-weight: var(--weight-bold); letter-spacing: .08em; text-transform: uppercase; margin-bottom: var(--space-1); }
  .audit-trust { display: inline-flex; align-items: center; gap: var(--space-2); color: var(--status-success-text); background: var(--color-success-bg); border: 1px solid var(--color-success-border); border-radius: var(--radius-full); padding: 6px 12px; font-size: var(--text-xs); font-weight: var(--weight-semibold); }
  .audit-trust__dot, .audit-action__dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
  .audit-search { width: 320px; max-width: 100%; }
  .audit-count { color: var(--text-muted); font-size: var(--text-sm); white-space: nowrap; }
  .audit-table td { vertical-align: middle; }
  .audit-time { white-space: nowrap; color: var(--text-primary); }
  .audit-time strong, .audit-time span { display: block; }
  .audit-time span { color: var(--text-muted); font-size: var(--text-xs); margin-top: 2px; }
  .audit-avatar { background: var(--surface-accent-subtle); color: var(--color-royal); }
  .audit-action { display: inline-flex; align-items: center; gap: 7px; border: 1px solid currentColor; border-radius: var(--radius-full); padding: 4px 9px; font-size: var(--text-xs); font-weight: var(--weight-semibold); white-space: nowrap; }
  .audit-action--info { color: var(--status-info-text); background: var(--color-info-bg); }
  .audit-action--neutral { color: var(--badge-neutral-text); background: var(--badge-neutral-bg); }
  .audit-action--success { color: var(--status-success-text); background: var(--color-success-bg); }
  .audit-action--warning { color: var(--status-warning-text); background: var(--color-warning-bg); }
  .audit-action--orange { color: #C2410C; background: #FFF1E7; }
  .audit-description { display: block; max-width: 320px; }
  .audit-ip { white-space: nowrap; color: var(--text-secondary); font-size: var(--text-xs); }
  @media (max-width: 720px) { .audit-trust { display: none; } .audit-search { width: 100%; } .audit-table { min-width: 900px; } }
</style>
<?= $this->endSection() ?>