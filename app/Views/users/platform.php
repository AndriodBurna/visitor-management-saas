<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$users = $users ?? [];
$filters = $filters ?? ['q' => '', 'role' => '', 'status' => ''];
$roles = ['organization_admin' => 'Organization Admin', 'branch_manager' => 'Branch Manager', 'receptionist' => 'Receptionist'];
$statuses = ['active' => ['class' => 'badge-success', 'label' => 'Active'], 'inactive' => ['class' => 'badge-neutral', 'label' => 'Inactive'], 'suspended' => ['class' => 'badge-danger', 'label' => 'Suspended']];
?>
<div class="section-title">
  <div><span class="eyebrow">Platform administration</span><h2>Staff directory</h2><p class="text-muted">Review every organization admin, branch manager, and receptionist from one place.</p></div>
  <a href="<?= site_url('organizations') ?>" class="btn btn-secondary btn-sm">Manage organizations</a>
</div>
<div class="card">
  <form class="table-toolbar" method="get" action="<?= site_url('admin/staff') ?>">
    <div class="table-toolbar__filters">
      <div class="input-icon-wrap platform-staff-search"><span class="input-icon">⌕</span><input type="search" name="q" class="form-control" placeholder="Search name, email, organization" value="<?= esc($filters['q']) ?>"></div>
      <select name="role" class="form-control" aria-label="Filter by role"><option value="">All roles</option><?php foreach ($roles as $value => $label): ?><option value="<?= esc($value) ?>" <?= $filters['role'] === $value ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select>
      <select name="status" class="form-control" aria-label="Filter by account status"><option value="">All statuses</option><?php foreach ($statuses as $value => $status): ?><option value="<?= esc($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= esc($status['label']) ?></option><?php endforeach; ?></select>
      <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
      <?php if ($filters['q'] || $filters['role'] || $filters['status']): ?><a href="<?= site_url('admin/staff') ?>" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
    </div>
    <span class="text-muted" style="font-size:var(--text-sm);"><?= count($users) ?> account<?= count($users) === 1 ? '' : 's' ?></span>
  </form>
  <div class="table-wrap"><table class="data-table"><thead><tr><th>Staff member</th><th>Organization</th><th>Location</th><th>Role</th><th>Status</th><th>Last sign-in</th><th>Date created</th></tr></thead><tbody>
    <?php if (!$users): ?><tr><td colspan="7"><div class="empty-state"><div class="empty-state__icon">U</div><h3>No staff accounts found</h3><p>Try changing the search or filters, or add staff from an organization.</p></div></td></tr>
    <?php else: foreach ($users as $user): ?>
      <?php $status = $statuses[$user['status'] ?? 'active'] ?? $statuses['active']; $roleLabel = $roles[$user['role'] ?? ''] ?? ucwords(str_replace('_', ' ', $user['role'] ?? '—')); ?>
      <tr>
        <td><div class="table-person"><div class="avatar"><?= esc(strtoupper(substr($user['name'] ?? '?', 0, 1))) ?></div><div class="table-person__meta"><div class="table-person__name"><?= esc($user['name'] ?? '') ?></div><div class="table-person__sub"><?= esc($user['email'] ?? '—') ?></div></div></div></td>
          <td><a href="<?= site_url('admin/staff/' . (int) $user['id']) ?>" class="table-person"><div class="avatar"><?= esc(strtoupper(substr($user['name'] ?? '?', 0, 1))) ?></div><div class="table-person__meta"><div class="table-person__name"><?= esc($user['name'] ?? '') ?></div><div class="table-person__sub"><?= esc($user['email'] ?? '—') ?></div></div></a></td>
        <td><?= esc($user['organization_name'] ?? 'Unassigned') ?></td>
        <td class="cell-muted"><?= esc($user['branch_name'] ?? 'All locations') ?></td>
        <td><span class="badge badge-info"><?= esc($roleLabel) ?></span></td>
        <td><span class="badge <?= $status['class'] ?>"><span class="badge__dot"></span><?= esc($status['label']) ?></span></td>
        <td class="cell-muted"><?= !empty($user['last_login_at']) ? date('d M Y, H:i', strtotime($user['last_login_at'])) : 'Never' ?></td>
        <td class="cell-muted"><?= !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '—' ?></td>
      </tr>
    <?php endforeach; endif; ?>
  </tbody></table></div>
  <?php if ($users): ?><div class="pagination-bar"><span>Showing <?= count($users) ?> account<?= count($users) === 1 ? '' : 's' ?></span><div class="pagination"><?= $pager->links() ?></div></div><?php endif; ?>
</div>
<?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
  .eyebrow { display:block; color:var(--color-royal); font-size:var(--text-xs); font-weight:var(--weight-bold); letter-spacing:.08em; text-transform:uppercase; margin-bottom:var(--space-1); }
  .platform-staff-search { width:320px; max-width:100%; }
  @media (max-width:720px) { .platform-staff-search { width:100%; } .data-table { min-width:900px; } }
</style>
<?= $this->endSection() ?>
