<?php

namespace App\Controllers;

use App\Models\VisitModel;
use App\Models\AuditLogModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class VisitController extends BaseController
{
    private function activityFilters(): array
    {
        $status = trim((string) $this->request->getGet('status'));
        $date = trim((string) $this->request->getGet('date'));
        $q = trim((string) $this->request->getGet('q'));

        return [
            'status' => in_array($status, ['scheduled', 'checked_in', 'checked_out', 'cancelled', 'rejected'], true) ? $status : '',
            'date'   => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : '',
            'q'      => $q,
        ];
    }

    private function activityQuery(array $filters)
    {
        $model = new VisitModel();
        $builder = $model
            ->select('visits.*, visitors.full_name AS visitor_name, visitors.company, branches.name AS branch_name, users.name AS host_name')
            ->join('visitors', 'visitors.id = visits.visitor_id')
            ->join('branches', 'branches.id = visits.branch_id', 'left')
            ->join('users', 'users.id = visits.host_id', 'left');

        $role = (string) session('user_role');
        if ($role !== 'super_admin') {
            $builder->where('visits.organization_id', (int) session('organization_id'));
        }
        if (in_array($role, ['branch_manager', 'receptionist'], true)) {
            $builder->where('visits.branch_id', (int) session('branch_id'));
        }
        if ($filters['status'] !== '') {
            $builder->where('visits.status', $filters['status']);
        }
        if ($filters['date'] !== '') {
            $builder->groupStart()
                ->where('visits.check_in_at >=', $filters['date'] . ' 00:00:00')
                ->where('visits.check_in_at <=', $filters['date'] . ' 23:59:59')
                ->orGroupStart()
                    ->where('visits.check_in_at IS NULL')
                    ->where('visits.created_at >=', $filters['date'] . ' 00:00:00')
                    ->where('visits.created_at <=', $filters['date'] . ' 23:59:59')
                ->groupEnd()
            ->groupEnd();
        }
        if ($filters['q'] !== '') {
            $builder->groupStart()
                ->like('visitors.full_name', $filters['q'])
                ->orLike('visitors.company', $filters['q'])
                ->orLike('users.name', $filters['q'])
                ->orLike('visits.purpose', $filters['q'])
                ->groupEnd();
        }

        return [$model, $builder];
    }

    public function exportCsv()
    {
        [$model, $builder] = $this->activityQuery($this->activityFilters());
        $rows = $builder
            ->orderBy('COALESCE(visits.check_in_at, visits.created_at)', 'DESC', false)
            ->findAll();

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Visitor', 'Company', 'Host', 'Location', 'Purpose', 'Status', 'Check-in', 'Check-out']);
        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['visitor_name'] ?? '',
                $row['company'] ?? '',
                $row['host_name'] ?? '',
                $row['branch_name'] ?? '',
                $row['purpose'] ?? '',
                $row['status'] ?? '',
                $row['check_in_at'] ?? '',
                $row['check_out_at'] ?? '',
            ]);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="visit-activity-' . date('Y-m-d') . '.csv"')
            ->setBody($csv);
    }

    public function checkOut($visitId)
    {
        $visitId = (int) $visitId;
        $visitModel = new VisitModel();
        $visit = $visitModel->find($visitId);

        if (!$visit || ($visit['status'] ?? '') !== 'checked_in') {
            return redirect()->back()->with('error', 'This visit is no longer active.');
        }

        $role = (string) session('user_role');
        if ($role !== 'super_admin' && (int) $visit['organization_id'] !== (int) session('organization_id')) {
            throw PageNotFoundException::forPageNotFound('Visit not found.');
        }
        if (in_array($role, ['branch_manager', 'receptionist'], true)
            && (int) $visit['branch_id'] !== (int) session('branch_id')) {
            throw PageNotFoundException::forPageNotFound('Visit not found.');
        }

        if (!$visitModel->update($visitId, [
            'check_out_at' => date('Y-m-d H:i:s'),
            'status'       => 'checked_out',
        ])) {
            return redirect()->back()->with('error', 'The visit could not be checked out. Please try again.');
        }

        (new AuditLogModel())->record('checkout', 'visit', 'Visitor checked out from Visit Activity.', $visitId, (int) $visit['organization_id'], (int) $visit['branch_id']);

        return redirect()->back()->with('success', 'Visitor checked out successfully.');
    }

    public function index()
    {
        $filters = $this->activityFilters();
        [$model, $builder] = $this->activityQuery($filters);

        $visits = $builder
            ->orderBy('COALESCE(visits.check_in_at, visits.created_at)', 'DESC', false)
            ->paginate(20);

        return view('visits/index', [
            'title'      => 'Visit Activity',
            'activeMenu' => 'visits',
            'visits'     => $visits,
            'pager'      => $model->pager,
            'filters'    => $filters,
        ]);
    }
}