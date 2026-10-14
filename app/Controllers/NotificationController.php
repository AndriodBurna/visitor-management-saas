<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\OrganizationModel;
use App\Models\UserModel;
use App\Models\VisitModel;

class NotificationController extends BaseController
{
    public function index()
    {
        $organizations = new OrganizationModel();
        $users = new UserModel();
        $visits = new VisitModel();

        return view('notifications/index', [
            'title' => 'Alerts Center',
            'activeMenu' => 'notifications',
            'suspendedOrganizations' => $organizations->where('status', 'suspended')->orderBy('name', 'ASC')->findAll(),
            'inactiveStaff' => $users->select('users.*, organizations.name AS organization_name')->join('organizations', 'organizations.id = users.organization_id', 'left')->where('users.role !=', 'super_admin')->whereIn('users.status', ['inactive', 'suspended'])->orderBy('users.updated_at', 'DESC')->findAll(8),
            'onSiteCount' => $visits->where('status', 'checked_in')->countAllResults(),
            'recentSecurity' => (new AuditLogModel())->whereIn('action', ['login', 'logout', 'update'])->orderBy('created_at', 'DESC')->findAll(8),
        ]);
    }
}
