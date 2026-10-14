<?php

namespace App\Models;

use CodeIgniter\Model;

class OrganizationModel extends Model
{
    protected $table      = 'organizations';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name'   => 'required|min_length[2]|max_length[150]',
        'slug'   => 'required|min_length[2]|max_length[150]|alpha_dash',
        'email'  => 'permit_empty|valid_email|max_length[150]',
        'phone'  => 'permit_empty|max_length[30]',
        'status' => 'required|in_list[active,inactive,suspended]',
    ];

    public function getPlatformHealth(int $limit = 8): array
    {
        return $this->select('organizations.id, organizations.name, organizations.status')
            ->select('COUNT(DISTINCT branches.id) AS locations', false)
            ->select('COUNT(DISTINCT users.id) AS staff', false)
            ->select('COUNT(DISTINCT visitors.id) AS visitors', false)
            ->select('COUNT(DISTINCT visits.id) AS visits', false)
            ->select("SUM(CASE WHEN visits.status = 'checked_in' THEN 1 ELSE 0 END) AS on_site", false)
            ->join('branches', 'branches.organization_id = organizations.id', 'left')
            ->join('users', 'users.organization_id = organizations.id AND users.role != \'super_admin\'', 'left')
            ->join('visitors', 'visitors.organization_id = organizations.id', 'left')
            ->join('visits', 'visits.organization_id = organizations.id', 'left')
            ->groupBy('organizations.id')
            ->orderBy('on_site', 'DESC', false)
            ->orderBy('visits', 'DESC', false)
            ->limit($limit)
            ->find();
    }
}