<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->post('logout', 'AuthController::logout');
$routes->get('dashboard', 'DashboardController::index', ['filter' => 'auth']);

$routes->get('account/profile', 'AccountController::profile', ['filter' => 'auth']);
$routes->post('account/profile', 'AccountController::updateProfile', ['filter' => 'auth']);
$routes->get('account/password', 'AccountController::password', ['filter' => 'auth']);
$routes->post('account/password', 'AccountController::updatePassword', ['filter' => 'auth']);

$routes->get('admin/alerts', 'NotificationController::index', ['filter' => 'auth:super_admin']);

$routes->get('admin/locations', 'BranchController::platform', ['filter' => 'auth:super_admin']);

$routes->get('admin/settings', 'PlatformSettingsController::index', ['filter' => 'auth:super_admin']);
$routes->post('admin/settings', 'PlatformSettingsController::update', ['filter' => 'auth:super_admin']);

$routes->get('admin/exports', 'DataExportController::index', ['filter' => 'auth:super_admin']);

$routes->get('admin/roles-permissions', 'RolePermissionController::index', ['filter' => 'auth:super_admin']);
$routes->post('admin/roles-permissions', 'RolePermissionController::update', ['filter' => 'auth:super_admin']);

$routes->get(
    'dashboard/super-admin',
    'DashboardController::superAdmin',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'dashboard/organization',
    'DashboardController::organization',
    ['filter' => 'auth:organization_admin']
);

$routes->get(
    'dashboard/branch',
    'DashboardController::branch',
    ['filter' => 'auth:branch_manager']
);

$routes->get(
    'dashboard/reception',
    'DashboardController::reception',
    ['filter' => 'auth:receptionist']
);

$routes->get(
    'organizations',
    'OrganizationController::index',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'audit-log',
    'AuditLogController::index',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'audit-log/export',
    'AuditLogController::exportCsv',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'admin/staff',
    'UserController::platform',
    ['filter' => 'auth:super_admin']
);
$routes->get('admin/staff/(:num)', 'UserController::platformShow/$1', ['filter' => 'auth:super_admin']);
$routes->post('admin/staff/(:num)/status', 'UserController::platformStatus/$1', ['filter' => 'auth:super_admin']);
$routes->post('admin/staff/(:num)/reset-password', 'UserController::resetPassword/$1', ['filter' => 'auth:super_admin']);

$routes->get(
    'admin/system-health',
    'SystemController::health',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'admin/reports',
    'ReportController::platform',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'organizations/create',
    'OrganizationController::create',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'organizations/export',
    'OrganizationController::exportCsv',
    ['filter' => 'auth:super_admin']
);

$routes->post(
    'organizations',
    'OrganizationController::store',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'organizations/(:num)/branches',
    'BranchController::index/$1',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'organizations/(:num)/branches/create',
    'BranchController::create/$1',
    ['filter' => 'auth:super_admin']
);

$routes->post(
    'organizations/(:num)/branches',
    'BranchController::store/$1',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'organizations/(:num)',
    'OrganizationController::show/$1',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'organizations/(:num)/lifecycle',
    'OrganizationController::lifecycle/$1',
    ['filter' => 'auth:super_admin']
);

$routes->post(
    'organizations/(:num)/status',
    'OrganizationController::changeStatus/$1',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'organizations/(:num)/edit',
    'OrganizationController::edit/$1',
    ['filter' => 'auth:super_admin']
);

$routes->post(
    'organizations/(:num)/update',
    'OrganizationController::update/$1',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'organizations/(:num)/branches/(:num)',
    'BranchController::show/$1/$2',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'organizations/(:num)/branches/(:num)/edit',
    'BranchController::edit/$1/$2',
    ['filter' => 'auth:super_admin']
);

$routes->post(
    'organizations/(:num)/branches/(:num)/update',
    'BranchController::update/$1/$2',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'organizations/(:num)/users',
    'UserController::index/$1',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'organizations/(:num)/users/create',
    'UserController::create/$1',
    ['filter' => 'auth:super_admin']
);

$routes->post(
    'organizations/(:num)/users',
    'UserController::store/$1',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'organizations/(:num)/users/(:num)',
    'UserController::show/$1/$2',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'organizations/(:num)/users/(:num)/edit',
    'UserController::edit/$1/$2',
    ['filter' => 'auth:super_admin']
);

$routes->post(
    'organizations/(:num)/users/(:num)/update',
    'UserController::update/$1/$2',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'visitors',
    'VisitorController::index',
    ['filter' => 'auth:super_admin,organization_admin,branch_manager,receptionist']
);

$routes->get(
    'visitors/export',
    'VisitorController::exportCsv',
    ['filter' => 'auth:super_admin']
);

$routes->get(
    'visitors/checkin',
    'VisitorController::checkIn',
    ['filter' => 'auth:super_admin,organization_admin,branch_manager,receptionist']
);

$routes->post(
    'visitors/checkin',
    'VisitorController::storeCheckIn',
    ['filter' => 'auth:super_admin,organization_admin,branch_manager,receptionist']
);

$routes->post(
    'visitors/(:num)/checkout',
    'VisitorController::checkOut/$1',
    ['filter' => 'auth:super_admin,organization_admin,branch_manager,receptionist']
);

$routes->get(
    'visits',
    'VisitController::index',
    ['filter' => 'auth:super_admin,organization_admin,branch_manager,receptionist']
);

$routes->get(
    'visits/export',
    'VisitController::exportCsv',
    ['filter' => 'auth:super_admin']
);

$routes->post(
    'visits/(:num)/checkout',
    'VisitController::checkOut/$1',
    ['filter' => 'auth:super_admin,organization_admin,branch_manager,receptionist']
);

$routes->get(
    'visitors/(:num)',
    'VisitorController::show/$1',
    ['filter' => 'auth:super_admin,organization_admin,branch_manager,receptionist']
);

$routes->get(
    'visitors/(:num)/edit',
    'VisitorController::edit/$1',
    ['filter' => 'auth:super_admin']
);

$routes->post(
    'visitors/(:num)/update',
    'VisitorController::update/$1',
    ['filter' => 'auth:super_admin']
);