<?php

namespace App\Models;

use CodeIgniter\Model;

class VisitorModel extends Model
{
    protected $table      = 'visitors';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'organization_id',
        'full_name',
        'phone',
        'email',
        'company',
        'identification_type',
        'identification_number',
        'photo',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get visitors with their organization information.
     */
    public function getAllWithOrganization(): array
    {
        return $this
            ->select('
                visitors.*,
                organizations.name AS organization_name
            ')
            ->join(
                'organizations',
                'organizations.id = visitors.organization_id',
                'left'
            )
            ->orderBy('visitors.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get one visitor with organization information.
     */
    public function getWithOrganization(int $visitorId): ?array
    {
        return $this
            ->select('
                visitors.*,
                organizations.name AS organization_name
            ')
            ->join(
                'organizations',
                'organizations.id = visitors.organization_id',
                'left'
            )
            ->where('visitors.id', $visitorId)
            ->first();
    }
}