<?php

namespace App\Controllers;

use App\Models\AuditLogModel;

class AuditLogController extends BaseController
{
    private function filteredQuery()
    {
        $q = trim((string) $this->request->getGet('q'));
        $action = trim((string) $this->request->getGet('action'));
        $allowedActions = ['login', 'logout', 'create', 'visitor_created', 'update', 'check_in', 'checkout'];
        $model = new AuditLogModel();
        $builder = $model
            ->select('audit_logs.*, audit_logs.ip_address AS ipaddress, users.name AS actor_name')
            ->join('users', 'users.id = audit_logs.actor_id', 'left');

        if ($q !== '') {
            $builder->groupStart()->like('audit_logs.description', $q)->orLike('audit_logs.entity', $q)->orLike('users.name', $q)->groupEnd();
        }
        if (in_array($action, $allowedActions, true)) {
            $builder->where('audit_logs.action', $action);
        }

        return $builder;
    }

    public function exportCsv()
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['When', 'Actor', 'Action', 'Entity', 'Entity ID', 'Description', 'IP Address']);
        foreach ($this->filteredQuery()->orderBy('audit_logs.created_at', 'DESC')->findAll() as $log) {
            fputcsv($handle, [$log['created_at'] ?? '', $log['actor_name'] ?? 'System', $log['action'] ?? '', $log['entity'] ?? '', $log['entity_id'] ?? '', $log['description'] ?? '', $log['ip_address'] ?? '']);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="audit-log-' . date('Y-m-d') . '.csv"')
            ->setBody($csv);
    }

    public function index()
    {
        $q = trim((string) $this->request->getGet('q'));
        $action = trim((string) $this->request->getGet('action'));
        $allowedActions = ['login', 'logout', 'create', 'visitor_created', 'update', 'check_in', 'checkout'];
        if (!in_array($action, $allowedActions, true)) {
            $action = '';
        }
        $model = new AuditLogModel();
        $builder = $model
            ->select('audit_logs.*, audit_logs.ip_address AS ipaddress, users.name AS actor_name')
            ->join('users', 'users.id = audit_logs.actor_id', 'left');

        if ($q !== '') {
            $builder->groupStart()
                ->like('audit_logs.description', $q)
                ->orLike('audit_logs.entity', $q)
                ->orLike('users.name', $q)
                ->groupEnd();
        }
        if ($action !== '') {
            $builder->where('audit_logs.action', $action);
        }

        return view('audit_logs/index', [
            'title'      => 'Audit Log',
            'activeMenu' => 'audit',
            'logs'       => $builder->orderBy('audit_logs.created_at', 'DESC')->paginate(30),
            'pager'      => $model->pager,
            'filters'    => ['q' => $q, 'action' => $action],
            'actionOptions' => $allowedActions,
        ]);
    }
}