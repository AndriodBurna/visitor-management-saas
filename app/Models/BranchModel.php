<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchModel extends Model
{
    protected $table      = 'branches';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'organization_id',
        'name',
        'code',
        'address',
        'phone',
        'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'organization_id' => 'required|integer',
        'name'            => 'required|min_length[2]|max_length[150]',
        'code'            => 'required|min_length[2]|max_length[50]|alpha_dash',
        'phone'           => 'permit_empty|max_length[30]',
        'status'          => 'required|in_list[active,inactive,suspended]',
    ];

    public function getByOrganization(int $organizationId): array
    {
        return $this->where('organization_id', $organizationId)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }
}