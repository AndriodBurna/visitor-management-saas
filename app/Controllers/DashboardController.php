<?php

namespace App\Controllers;

use App\Models\VisitModel;
use App\Models\OrganizationModel;
use App\Models\BranchModel;
use App\Models\UserModel;
use App\Models\VisitorModel;
use App\Models\AuditLogModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $session = session();

        if (!$session->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        return match ($session->get('user_role')) {
            'super_admin'        => redirect()->to('/dashboard/super-admin'),
            'organization_admin' => redirect()->to('/dashboard/organization'),
            'branch_manager'     => redirect()->to('/dashboard/branch'),
            'receptionist'       => redirect()->to('/dashboard/reception'),
            default              => redirect()->to('/login'),
        };
    }

    public function superAdmin()
{
    $visitModel        = new VisitModel();
    $organizationModel = new OrganizationModel();
    $branchModel       = new BranchModel();
    $userModel         = new UserModel();
    $visitorModel      = new VisitorModel();

    // -------------------------------------------------
    // Platform statistics
    // -------------------------------------------------

    $totalOrganizations = $organizationModel->countAllResults();
    $totalLocations     = $branchModel->where('status', 'active')->countAllResults();

    $totalStaff = $userModel
        ->where('role !=', 'super_admin')
        ->countAllResults();
    $totalVisitors = $visitorModel->countAllResults();
    $totalVisits = (new VisitModel())->countAllResults();

    // -------------------------------------------------
    // Visitor statistics
    // -------------------------------------------------

    $onSiteNow      = $visitModel->getOnSiteNow();
    $checkedInToday = $visitModel->getCheckedInToday();
    $preRegistered = $visitModel->getPreRegisteredToday();
    $avgVisitMinutes = $visitModel->getAverageVisitMinutes();

    // -------------------------------------------------
    // Recent visitor activity
    // -------------------------------------------------

    $recentVisits = $visitModel->getRecentVisits(10);
    $recentAudit = (new AuditLogModel())->recent(6);
    $upcomingVisits = $visitModel->getUpcomingToday(8);
    $dailyVisits = $visitModel->getDailyVisitSummary(7);
    $organizationHealth = $organizationModel->getPlatformHealth(8);

    $recentVisitors = array_map(function ($visit) {
        return [
            'name'     => $visit['visitor_name'] ?? '',
            'company'  => $visit['company'] ?? '',
            'host'     => $visit['host_name'] ?? '',
            'status'   => $visit['status'] ?? 'pending',
            'time'     => $visit['check_in_at'] ?? '',
            'initials' => strtoupper(
                substr($visit['visitor_name'] ?? '?', 0, 1)
            ),
        ];
    }, $recentVisits);

    return view('dashboard/super_admin', [
        'title'      => 'Super Admin Dashboard',
        'activeMenu' => 'dashboard',
        'orgName'    => 'Visitor Management SaaS',

        // SaaS/platform-level statistics
        'platformStats' => [
            'organizations' => $totalOrganizations,
            'locations'     => $totalLocations,
            'staff'         => $totalStaff,
            'visitors'      => $totalVisitors,
            'visits'        => $totalVisits,
        ],

        // Visitor statistics
        'stats' => [
            'onSiteNow'       => $onSiteNow,
            'checkedInToday'  => $checkedInToday,
            'preRegistered'   => $preRegistered,
            'avgVisitMinutes' => $avgVisitMinutes,
        ],

        'recentVisitors' => $recentVisitors,
        'upcomingVisits' => array_map(static function (array $visit): array {
            return [
                'name'    => $visit['visitor_name'] ?? '',
                'host'    => $visit['host_name'] ?? 'No specific host',
                'time'    => !empty($visit['created_at']) ? date('H:i', strtotime($visit['created_at'])) : '—',
                'purpose' => $visit['purpose'] ?? '',
            ];
        }, $upcomingVisits),
        'recentAudit'   => $recentAudit,
        'dailyVisits'   => $dailyVisits,
        'organizationHealth' => $organizationHealth,
    ]);
}

    public function organization()
    {
        return $this->workspaceDashboard('Organization workspace', (int) session('organization_id'));
    }

    public function branch()
    {
        return $this->workspaceDashboard('Branch workspace', (int) session('organization_id'), (int) session('branch_id'));
    }

    public function reception()
    {
        return $this->workspaceDashboard('Reception workspace', (int) session('organization_id'), (int) session('branch_id'));
    }

    private function workspaceDashboard(string $title, int $organizationId, ?int $branchId = null)
    {
        $visitModel = new VisitModel();
        $start = date('Y-m-d 00:00:00');
        $end = date('Y-m-d 23:59:59');

        $scope = static function ($builder) use ($organizationId, $branchId) {
            $builder->where('organization_id', $organizationId);
            if ($branchId) {
                $builder->where('branch_id', $branchId);
            }
            return $builder;
        };

        $onSite = $scope($visitModel->where('status', 'checked_in'))->countAllResults();
        $checkedInToday = $scope((new VisitModel())->where('check_in_at >=', $start)->where('check_in_at <=', $end))->countAllResults();
        $checkedOutToday = $scope((new VisitModel())->where('check_out_at >=', $start)->where('check_out_at <=', $end))->countAllResults();

        return view('dashboard/' . match ($title) {
            'Organization workspace' => 'organization',
            'Branch workspace' => 'branch',
            default => 'reception',
        }, [
            'title'      => $title,
            'activeMenu' => 'dashboard',
            'roleTitle'  => $title,
            'stats'      => [
                'onSite'          => $onSite,
                'checkedInToday'  => $checkedInToday,
                'checkedOutToday' => $checkedOutToday,
            ],
        ]);
    }
}