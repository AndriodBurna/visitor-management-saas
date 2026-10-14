<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\PlatformSettingModel;

class RolePermissionController extends BaseController
{
    private const ROLES = ['organization_admin', 'branch_manager', 'receptionist'];
    private const PERMISSIONS = [
        'view_visitors' => 'View visitor directory',
        'manage_checkins' => 'Manage check-ins and check-outs',
        'view_visits' => 'View visit activity',
        'export_data' => 'Export operational data',
    ];
    private const DEFAULTS = [
        'organization_admin' => ['view_visitors' => 1, 'manage_checkins' => 1, 'view_visits' => 1, 'export_data' => 0],
        'branch_manager' => ['view_visitors' => 1, 'manage_checkins' => 1, 'view_visits' => 1, 'export_data' => 0],
        'receptionist' => ['view_visitors' => 1, 'manage_checkins' => 1, 'view_visits' => 1, 'export_data' => 0],
    ];

    public function index()
    {
        return view('roles/permissions', [
            'title' => 'Roles & Permissions',
            'activeMenu' => 'roles-permissions',
            'roles' => self::ROLES,
            'permissions' => self::PERMISSIONS,
            'matrix' => $this->matrix(),
        ]);
    }

    public function update()
    {
        $matrix = [];
        foreach (self::ROLES as $role) {
            foreach (array_keys(self::PERMISSIONS) as $permission) {
                $matrix[$role][$permission] = $this->request->getPost($role . '_' . $permission) ? 1 : 0;
            }
        }

        (new PlatformSettingModel())->saveValues(['role_permissions' => json_encode($matrix)], (int) session('user_id'));
        (new AuditLogModel())->record('update', 'role_permissions', 'Role permissions updated.');

        return redirect()->to('/admin/roles-permissions')->with('success', 'Role permissions were saved successfully.');
    }

    public static function permissionEnabled(string $role, string $permission): bool
    {
        if ($role === 'super_admin') {
            return true;
        }

        $stored = (new PlatformSettingModel())->where('setting_key', 'role_permissions')->first();
        $matrix = $stored ? json_decode((string) $stored['setting_value'], true) : null;

        return (bool) ($matrix[$role][$permission] ?? self::DEFAULTS[$role][$permission] ?? false);
    }

    private function matrix(): array
    {
        $stored = (new PlatformSettingModel())->where('setting_key', 'role_permissions')->first();
        $matrix = $stored ? json_decode((string) $stored['setting_value'], true) : [];

        foreach (self::DEFAULTS as $role => $defaults) {
            $matrix[$role] = array_merge($defaults, is_array($matrix[$role] ?? null) ? $matrix[$role] : []);
        }

        return $matrix;
    }
}
