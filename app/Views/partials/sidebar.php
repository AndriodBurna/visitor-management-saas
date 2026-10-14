<?php
// Expects (all optional, safely defaulted):
//   $activeMenu   string  key of the current section, e.g. 'dashboard', 'visitors', 'checkin', 'reports', 'settings'
//   $orgName      string  tenant / organization name shown under the product mark
//   $pendingCount int     badge count for "Pending Approvals" nav item
$activeMenu = $activeMenu ?? '';
$orgName    = $orgName ?? 'Your Organization';
?>
<aside class="app-sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-brand__mark">VM</div>
    <div class="sidebar-brand__name"><?= esc($orgName) ?></div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section">
      <div class="nav-section__label">Overview</div>
      <a href="<?= site_url('dashboard') ?>" class="nav-link <?= $activeMenu === 'dashboard' ? 'is-active' : '' ?>">
        <span class="nav-link__icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
        </span>
        <span class="nav-link__label">Dashboard</span>
      </a>
    </div>

    <div class="nav-section">
      <div class="nav-section__label">Visitors</div>
      <a href="<?= site_url('visitors/checkin') ?>" class="nav-link <?= $activeMenu === 'checkin' ? 'is-active' : '' ?>">
        <span class="nav-link__icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
        </span>
        <span class="nav-link__label">New Check-In</span>
      </a>
      <a href="<?= site_url('visitors') ?>" class="nav-link <?= $activeMenu === 'visitors' ? 'is-active' : '' ?>">
        <span class="nav-link__icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </span>
        <span class="nav-link__label">All Visitors</span>
      </a>
      <a href="<?= site_url('visits') ?>" class="nav-link <?= $activeMenu === 'visits' ? 'is-active' : '' ?>">
        <span class="nav-link__icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/><path d="m9 16 2 2 4-4"/></svg>
        </span>
        <span class="nav-link__label">Visit Activity</span>
      </a>
    </div>

    <?php if (session('user_role') === 'super_admin'): ?>
      <div class="nav-section">
        <div class="nav-section__label">Administration</div>
        <a href="<?= site_url('organizations') ?>" class="nav-link <?= $activeMenu === 'organizations' ? 'is-active' : '' ?>">
          <span class="nav-link__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg>
          </span>
          <span class="nav-link__label">Organizations</span>
        </a>
        <a href="<?= site_url('audit-log') ?>" class="nav-link <?= $activeMenu === 'audit' ? 'is-active' : '' ?>">
          <span class="nav-link__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h6"/></svg>
          </span>
          <span class="nav-link__label">Audit Log</span>
        </a>
        <a href="<?= site_url('admin/staff') ?>" class="nav-link <?= $activeMenu === 'platform-staff' ? 'is-active' : '' ?>">
          <span class="nav-link__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 3 3.87 4 4 0 0 1-1 2.65M21 21v-2a4 4 0 0 0-3-3.87"/></svg>
          </span>
          <span class="nav-link__label">Staff Directory</span>
        </a>
        <a href="<?= site_url('admin/system-health') ?>" class="nav-link <?= $activeMenu === 'system-health' ? 'is-active' : '' ?>">
          <span class="nav-link__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/><circle cx="12" cy="12" r="4"/></svg>
          </span>
          <span class="nav-link__label">System Health</span>
        </a>
        <a href="<?= site_url('admin/reports') ?>" class="nav-link <?= $activeMenu === 'reports' ? 'is-active' : '' ?>">
          <span class="nav-link__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V5M4 19h17"/><path d="m7 15 4-5 3 3 5-7"/></svg>
          </span>
          <span class="nav-link__label">Platform Reports</span>
        </a>
        <a href="<?= site_url('admin/alerts') ?>" class="nav-link <?= $activeMenu === 'notifications' ? 'is-active' : '' ?>">
          <span class="nav-link__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          </span>
          <span class="nav-link__label">Alerts Center</span>
        </a>
        <a href="<?= site_url('admin/locations') ?>" class="nav-link <?= $activeMenu === 'platform-locations' ? 'is-active' : '' ?>">
          <span class="nav-link__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
          </span>
          <span class="nav-link__label">Locations</span>
        </a>
        <a href="<?= site_url('admin/settings') ?>" class="nav-link <?= $activeMenu === 'platform-settings' ? 'is-active' : '' ?>">
          <span class="nav-link__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="m19.4 15 .1.1a2 2 0 1 1-2.8 2.8l-.1-.1a2 2 0 0 0-3.4 1.4V19a2 2 0 1 1-4 0v-.2a2 2 0 0 0-3.4-1.4l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A2 2 0 0 0 3.6 11H3.5a2 2 0 1 1 0-4h.2a2 2 0 0 0 1.4-3.4L5 3.5a2 2 0 1 1 2.8-2.8l.1.1A2 2 0 0 0 11.3 2h.2a2 2 0 1 1 4 0v.2a2 2 0 0 0 3.4 1.4l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1A2 2 0 0 0 20.4 10h.1a2 2 0 1 1 0 4h-.2a2 2 0 0 0-1.4 3.4Z"/></svg>
          </span>
          <span class="nav-link__label">Platform Settings</span>
        </a>
        <a href="<?= site_url('admin/exports') ?>" class="nav-link <?= $activeMenu === 'data-exports' ? 'is-active' : '' ?>">
          <span class="nav-link__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5"/><path d="M5 21h14a2 2 0 0 0 2-2v-2M3 17v2a2 2 0 0 0 2 2"/></svg>
          </span>
          <span class="nav-link__label">Data &amp; Exports</span>
        </a>
        <a href="<?= site_url('admin/roles-permissions') ?>" class="nav-link <?= $activeMenu === 'roles-permissions' ? 'is-active' : '' ?>">
          <span class="nav-link__icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v5c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V6l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
          </span>
          <span class="nav-link__label">Roles &amp; Permissions</span>
        </a>
      </div>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <!-- Compact, secondary logout action. The primary, more discoverable
         logout control lives in the topbar user dropdown (partials/topbar.php)
         — this is intentionally the same visual weight as "Collapse" below it,
         not a second dominant logout button. Real POST form, no JS session logic. -->
    <form action="<?= site_url('logout') ?>" method="post">
      <?= csrf_field() ?>
      <button type="submit" class="sidebar-collapse-btn" aria-label="Log out">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span class="nav-link__label">Log out</span>
      </button>
    </form>

    <button type="button" class="sidebar-collapse-btn" data-sidebar-collapse aria-label="Collapse sidebar">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/></svg>
      <span class="nav-link__label">Collapse</span>
    </button>
  </div>
</aside>
