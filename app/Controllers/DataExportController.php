<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\OrganizationModel;
use App\Models\VisitModel;
use App\Models\VisitorModel;

class DataExportController extends BaseController
{
    public function index()
    {
        return view('exports/index', [
            'title' => 'Data & Exports',
            'activeMenu' => 'data-exports',
            'exports' => [
                [
                    'name' => 'Organizations',
                    'description' => 'Tenant profiles, contact details, status, and creation dates.',
                    'count' => (new OrganizationModel())->countAllResults(),
                    'url' => site_url('organizations/export'),
                    'format' => 'CSV',
                ],
                [
                    'name' => 'Visitor directory',
                    'description' => 'Visitor identity and company records stored across the platform.',
                    'count' => (new VisitorModel())->countAllResults(),
                    'url' => site_url('visitors/export'),
                    'format' => 'CSV',
                ],
                [
                    'name' => 'Visit activity',
                    'description' => 'Check-in, check-out, host, purpose, location, and visit status data.',
                    'count' => (new VisitModel())->countAllResults(),
                    'url' => site_url('visits/export'),
                    'format' => 'CSV',
                ],
                [
                    'name' => 'Audit log',
                    'description' => 'Platform security events, actors, timestamps, and originating addresses.',
                    'count' => (new AuditLogModel())->countAllResults(),
                    'url' => site_url('audit-log/export'),
                    'format' => 'CSV',
                ],
            ],
        ]);
    }
}
