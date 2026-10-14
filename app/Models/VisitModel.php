<?php

namespace App\Models;

use CodeIgniter\Model;

class VisitModel extends Model
{
    protected $table      = 'visits';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'organization_id',
        'branch_id',
        'visitor_id',
        'host_id',
        'purpose',
        'check_in_at',
        'check_out_at',
        'status',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getOnSiteNow(): int
    {
        return $this->where('status', 'checked_in')->countAllResults();
    }

   public function getCheckedInToday(): int
{
    $start = date('Y-m-d 00:00:00');
    $end   = date('Y-m-d 23:59:59');

    return $this->where('check_in_at >=', $start)
                ->where('check_in_at <=', $end)
                ->countAllResults();
}

    public function getPreRegisteredToday(): int
    {
        $start = date('Y-m-d 00:00:00');
        $end   = date('Y-m-d 23:59:59');

        return $this->where('status', 'scheduled')
            ->where('created_at >=', $start)
            ->where('created_at <=', $end)
            ->countAllResults();
    }

    public function getAverageVisitMinutes(): int
    {
        $row = $this->select('AVG(TIMESTAMPDIFF(MINUTE, check_in_at, check_out_at)) AS average_minutes')
            ->where('status', 'checked_out')
            ->where('check_in_at IS NOT NULL')
            ->where('check_out_at IS NOT NULL')
            ->first();

        return (int) round((float) ($row['average_minutes'] ?? 0));
    }

    public function getRecentVisits(int $limit = 10): array
    {
        return $this->select('
                visits.*,
                visitors.full_name AS visitor_name,
                visitors.company,
                users.name AS host_name
            ')
            ->join('visitors', 'visitors.id = visits.visitor_id')
            ->join('users', 'users.id = visits.host_id', 'left')
            ->orderBy('visits.created_at', 'DESC')
            ->limit($limit)
            ->find();
    }

    public function getDailyVisitSummary(int $days = 7): array
    {
        $start = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));

        return $this->select("DATE(check_in_at) AS visit_date, COUNT(*) AS total", false)
            ->where('check_in_at >=', $start)
            ->where('check_in_at IS NOT NULL')
            ->groupBy('visit_date')
            ->orderBy('visit_date', 'ASC')
            ->find();
    }

    public function getUpcomingToday(int $limit = 8): array
    {
        $start = date('Y-m-d 00:00:00');
        $end = date('Y-m-d 23:59:59');

        return $this->select('
                visits.*,
                visitors.full_name AS visitor_name,
                users.name AS host_name
            ')
            ->join('visitors', 'visitors.id = visits.visitor_id')
            ->join('users', 'users.id = visits.host_id', 'left')
            ->where('visits.status', 'scheduled')
            ->where('visits.created_at >=', $start)
            ->where('visits.created_at <=', $end)
            ->orderBy('visits.created_at', 'ASC')
            ->limit($limit)
            ->find();
    }
}