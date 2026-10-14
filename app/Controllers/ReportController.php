<?php

namespace App\Controllers;

use App\Models\OrganizationModel;
use App\Models\VisitModel;

class ReportController extends BaseController
{
    public function platform()
    {
        $from = $this->validDate($this->request->getGet('from')) ?? date('Y-m-d', strtotime('-29 days'));
        $to = $this->validDate($this->request->getGet('to')) ?? date('Y-m-d');

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $start = $from . ' 00:00:00';
        $end = $to . ' 23:59:59';
        $visitModel = new VisitModel();
        $range = static fn ($builder) => $builder
            ->where('check_in_at >=', $start)
            ->where('check_in_at <=', $end);

        $total = $range((new VisitModel()))->countAllResults();
        $checkedOut = $range((new VisitModel())->where('status', 'checked_out'))->countAllResults();
        $checkedIn = $range((new VisitModel())->where('status', 'checked_in'))->countAllResults();
        $scheduled = (new VisitModel())
            ->where('status', 'scheduled')
            ->where('created_at >=', $start)
            ->where('created_at <=', $end)
            ->countAllResults();

        $byOrganization = $visitModel
            ->select('organizations.name AS organization_name, COUNT(visits.id) AS total', false)
            ->select("SUM(CASE WHEN visits.status = 'checked_in' THEN 1 ELSE 0 END) AS on_site", false)
            ->select("SUM(CASE WHEN visits.status = 'checked_out' THEN 1 ELSE 0 END) AS completed", false)
            ->join('organizations', 'organizations.id = visits.organization_id', 'left')
            ->where('visits.check_in_at >=', $start)
            ->where('visits.check_in_at <=', $end)
            ->groupBy('visits.organization_id')
            ->orderBy('total', 'DESC', false)
            ->findAll();

        $daily = $visitModel
            ->select('DATE(check_in_at) AS visit_date, COUNT(*) AS total', false)
            ->where('check_in_at >=', $start)
            ->where('check_in_at <=', $end)
            ->groupBy('visit_date')
            ->orderBy('visit_date', 'ASC')
            ->findAll();

        return view('reports/platform', [
            'title' => 'Platform Reports',
            'activeMenu' => 'reports',
            'from' => $from,
            'to' => $to,
            'summary' => compact('total', 'checkedOut', 'checkedIn', 'scheduled'),
            'byOrganization' => $byOrganization,
            'daily' => $daily,
        ]);
    }

    private function validDate(?string $date): ?string
    {
        $date = trim((string) $date);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 ? $date : null;
    }
}
