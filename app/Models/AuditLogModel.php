<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class AuditLogModel extends Model
{
    protected $table         = 'audit_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'actor_id',
        'organization_id',
        'branch_id',
        'action',
        'entity',
        'entity_id',
        'description',
        'ip_address',
        'created_at',
    ];
    protected $useTimestamps = false;

    public function record(string $action, string $entity, string $description, ?int $entityId = null, ?int $organizationId = null, ?int $branchId = null): bool
    {
        if (!$this->db->tableExists($this->table)) {
            return false;
        }

        return (bool) $this->insert([
            'actor_id'       => session('user_id') ?: null,
            'organization_id' => $organizationId ?? (session('organization_id') ?: null),
            'branch_id'      => $branchId ?? (session('branch_id') ?: null),
            'action'         => substr($action, 0, 80),
            'entity'         => $entity,
            'entity_id'      => $entityId,
            'description'    => function_exists('mb_substr')
                ? mb_substr($description, 0, 255)
                : substr($description, 0, 255),
            'ip_address'     => $this->getClientAddress(),
            'created_at'     => Time::now('UTC')->toDateTimeString(),
        ]);
    }

    private function getClientAddress(): string
    {
        $address = service('request')->getIPAddress();

        return $address === '::1' ? '127.0.0.1' : $address;
    }

    public function recent(int $limit = 6): array
    {
        return $this->select('audit_logs.*, users.name AS actor_name')
            ->join('users', 'users.id = audit_logs.actor_id', 'left')
            ->orderBy('audit_logs.created_at', 'DESC')
            ->limit($limit)
            ->find();
    }
}