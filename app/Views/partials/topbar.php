<?php
use CodeIgniter\I18n\Time;

// Expects (all optional, safely defaulted):
//   $title              string  page title shown in the heading
//   $breadcrumbs        array   [['label' => 'Visitors', 'url' => site_url('visitors')], ['label' => 'Check-In']]
//   $userName           string  from your auth session
//   $userRole           string  RAW role value from your auth session, e.g. 'organization_admin'.
//                                Display-only mapping happens below; the raw session value is never changed.
//   $notificationCount  int
$title       = $title ?? 'Dashboard';
$breadcrumbs = $breadcrumbs ?? [];
$userName    = $userName ?? (session('user_name') ?? 'User');
$userRole    = $userRole ?? (session('user_role') ?? '');
$userAvatar  = session('user_avatar') ?? '';
$initials    = $initials ?? strtoupper(substr($userName, 0, 1));

// Presentation-only role labels. The raw $userRole value (used for any
// backend/authorization logic elsewhere) is never modified — this is purely
// what gets displayed to the person.
$roleLabelMap = [
    'super_admin'         => 'Super Admin',
    'organization_admin'  => 'Organization Admin',
    'branch_manager'      => 'Branch Manager',
    'receptionist'        => 'Receptionist',
];
$roleLabel = $roleLabelMap[$userRole] ?? ($userRole ? ucwords(str_replace('_', ' ', $userRole)) : '');
$localNow = Time::now('Africa/Kampala');
$hour = (int) $localNow->format('G');
$timeGreeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
?>
<header class="app-topbar">
  <div class="topbar-left">
    <button type="button" class="topbar-menu-btn" data-sidebar-open aria-label="Open menu">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>

    <div class="topbar-welcome" aria-live="polite">
      <span class="topbar-welcome__eyebrow">Welcome back,</span>
      <strong data-time-greeting><?= esc($timeGreeting) ?>, <?= esc($roleLabel ?: $userName) ?></strong>
    </div>
  </div>

  <div class="topbar-right">
    <div class="topbar-clock" aria-label="Current Kampala time">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>
      <time data-live-clock datetime="<?= esc($localNow->toDateTimeString()) ?>"><?= esc($localNow->format('H:i:s')) ?></time>
      <span class="topbar-clock__zone">EAT</span>
    </div>
    <form class="topbar-search" method="get" action="<?= site_url('visitors') ?>">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="search" name="q" placeholder="Search visitors" aria-label="Search visitors" value="<?= esc((string) service('request')->getGet('q')) ?>">
    </form>

    <!-- Quick theme toggle. aria-pressed / title are kept in sync by app.js
         whenever the theme changes, from any control. -->
    <button type="button" class="icon-btn" data-theme-toggle aria-pressed="false" aria-label="Toggle color theme" title="Switch to dark mode">
      <span class="icon-sun" aria-hidden="true">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><line x1="12" y1="2" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="6.34" y2="6.34"/><line x1="17.66" y1="17.66" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="4" y2="12"/><line x1="20" y1="12" x2="22" y2="12"/><line x1="4.93" y1="19.07" x2="6.34" y2="17.66"/><line x1="17.66" y1="6.34" x2="19.07" y2="4.93"/></svg>
      </span>
      <span class="icon-moon" aria-hidden="true">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </span>
    </button>

    <a href="<?= session('user_role') === 'super_admin' ? site_url('admin/alerts') : '#' ?>" class="icon-btn" aria-label="Notifications" title="Open alerts">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      <?php if (!empty($notificationCount)): ?><span class="icon-btn__dot"></span><?php endif; ?>
    </a>

    <div class="topbar-divider"></div>

    <div class="user-menu-wrapper">
      <div class="user-menu" data-dropdown-trigger role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">
        <div class="avatar topbar-avatar"><?php if ($userAvatar): ?><img src="<?= base_url($userAvatar) ?>" alt="Profile photo"><?php else: ?><?= esc($initials) ?><?php endif; ?></div>
        <div class="user-menu__meta">
          <span class="user-menu__name"><?= esc($userName) ?></span>
          <?php if ($roleLabel): ?><span class="user-menu__role"><?= esc($roleLabel) ?></span><?php endif; ?>
        </div>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
      </div>

      <div class="dropdown-menu" role="menu" aria-label="Account menu">
        <div class="dropdown-menu__header">
          <div class="dropdown-menu__name"><?= esc($userName) ?></div>
          <?php if ($roleLabel): ?><div class="dropdown-menu__role"><?= esc($roleLabel) ?></div><?php endif; ?>
        </div>

        <div class="dropdown-menu__section">
          <div class="dropdown-menu__label">Appearance</div>
          <div class="theme-switch" role="group" aria-label="Theme">
            <button type="button" class="theme-switch__option" data-theme-option="light" aria-pressed="false">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><line x1="12" y1="2" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="6.34" y2="6.34"/><line x1="17.66" y1="17.66" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="4" y2="12"/><line x1="20" y1="12" x2="22" y2="12"/><line x1="4.93" y1="19.07" x2="6.34" y2="17.66"/><line x1="17.66" y1="6.34" x2="19.07" y2="4.93"/></svg>
              Light
            </button>
            <button type="button" class="theme-switch__option" data-theme-option="dark" aria-pressed="false">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
              Dark
            </button>
          </div>
        </div>

        <div class="dropdown-menu__divider"></div>

        <div class="dropdown-menu__section">
          <a href="<?= site_url('account/profile') ?>" class="dropdown-menu__item">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
            My profile
          </a>
          <a href="<?= site_url('account/password') ?>" class="dropdown-menu__item">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Security settings
          </a>
        </div>

        <div class="dropdown-menu__divider"></div>

        <div class="dropdown-menu__section">
          <!-- Logout stays a real POST form to the existing backend route.
               No session/auth logic is implemented here in JavaScript. -->
          <form action="<?= site_url('logout') ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="dropdown-menu__item dropdown-menu__item--danger">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
              Log out
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</header>
