<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\BranchModel;
use App\Models\OrganizationModel;
use App\Models\UserModel;
use App\Models\VisitModel;
use App\Models\VisitorModel;

class SystemController extends BaseController
{
    public function health()
    {
        $database = ['status' => 'unknown', 'detail' => 'Not checked'];

        try {
            db_connect()->query('SELECT 1');
            $database = ['status' => 'healthy', 'detail' => 'Connection established'];
        } catch (\Throwable $exception) {
            $database = ['status' => 'critical', 'detail' => 'Connection failed'];
        }

        $storagePaths = [
            'Cache'   => WRITEPATH . 'cache',
            'Logs'    => WRITEPATH . 'logs',
            'Session' => WRITEPATH . 'session',
        ];
        $storage = [];
        foreach ($storagePaths as $label => $path) {
            $storage[] = [
                'label' => $label,
                'path' => $path,
                'status' => is_dir($path) && is_writable($path) ? 'healthy' : 'warning',
                'detail' => is_dir($path) && is_writable($path) ? 'Writable' : 'Check permissions',
            ];
        }

        $counts = [
            'organizations' => 0,
            'locations' => 0,
            'staff' => 0,
            'visitors' => 0,
            'visits' => 0,
            'audit' => 0,
        ];

        if ($database['status'] === 'healthy') {
            $counts = [
                'organizations' => (new OrganizationModel())->countAllResults(),
                'locations' => (new BranchModel())->countAllResults(),
                'staff' => (new UserModel())->where('role !=', 'super_admin')->countAllResults(),
                'visitors' => (new VisitorModel())->countAllResults(),
                'visits' => (new VisitModel())->countAllResults(),
                'audit' => (new AuditLogModel())->countAllResults(),
            ];
        }

        return view('system/health', [
            'title' => 'System Health',
            'activeMenu' => 'system-health',
            'orgName' => 'Visitor Management SaaS',
            'database' => $database,
            'storage' => $storage,
            'counts' => $counts,
            'runtime' => [
                'environment' => ENVIRONMENT,
                'php' => PHP_VERSION,
                'codeigniter' => defined('CI_VERSION') ? CI_VERSION : '4.x',
                'timezone' => date_default_timezone_get(),
            ],
        ]);
    }
}
