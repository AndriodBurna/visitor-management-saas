<?php

namespace App\Controllers;

use App\Models\OrganizationModel;
use App\Models\AuditLogModel;
use App\Models\BranchModel;
use App\Models\UserModel;
use App\Models\VisitorModel;
use App\Models\VisitModel;

class OrganizationController extends BaseController
{
    public function lifecycle($id)
    {
        $organization = (new OrganizationModel())->find($id);

        if (!$organization) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('organizations/lifecycle', [
            'title' => 'Organization Lifecycle',
            'activeMenu' => 'organizations',
            'organization' => $organization,
            'stats' => [
                'branches' => (new BranchModel())->where('organization_id', $id)->countAllResults(),
                'staff' => (new UserModel())->where('organization_id', $id)->where('role !=', 'super_admin')->countAllResults(),
                'visitors' => (new VisitorModel())->where('organization_id', $id)->countAllResults(),
                'active_visits' => (new VisitModel())->where('organization_id', $id)->where('status', 'checked_in')->countAllResults(),
            ],
            'recentChanges' => (new AuditLogModel())->where('entity', 'organization')->where('entity_id', (int) $id)->orderBy('created_at', 'DESC')->findAll(6),
        ]);
    }

    public function changeStatus($id)
    {
        $organizationModel = new OrganizationModel();
        $organization = $organizationModel->find($id);

        if (!$organization) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $status = trim((string) $this->request->getPost('status'));
        if (!in_array($status, ['active', 'inactive', 'suspended'], true)) {
            return redirect()->back()->with('error', 'Select a valid organization lifecycle status.');
        }

        $activeVisits = (new VisitModel())->where('organization_id', $id)->where('status', 'checked_in')->countAllResults();
        if ($status !== 'active' && $activeVisits > 0) {
            return redirect()->back()->with('error', 'This organization has ' . $activeVisits . ' visitor(s) still on site. Check them out before changing access.');
        }

        if (($organization['status'] ?? '') === $status) {
            return redirect()->back()->with('info', 'The organization is already ' . $status . '.');
        }

        if (!$organizationModel->update($id, ['status' => $status])) {
            return redirect()->back()->with('error', 'The organization status could not be updated.');
        }

        (new AuditLogModel())->record('update', 'organization', 'Organization status changed from ' . ($organization['status'] ?? 'unknown') . ' to ' . $status . '.', (int) $id, (int) $id);

        return redirect()->to('/organizations/' . $id . '/lifecycle')->with('success', 'Organization status changed to ' . $status . '.');
    }

    public function index()
    {
        $organizationModel = new OrganizationModel();

        $q = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));

        if (!in_array($status, ['active', 'inactive', 'suspended'], true)) {
            $status = '';
        }

        $builder = $organizationModel;
        if ($q !== '') {
            $builder->groupStart()
                ->like('name', $q)
                ->orLike('slug', $q)
                ->orLike('email', $q)
                ->orLike('phone', $q)
                ->groupEnd();
        }
        if ($status !== '') {
            $builder->where('status', $status);
        }

        return view('organizations/index', [
            'title'         => 'Organizations',
            'activeMenu'    => 'organizations',
            'organizations' => $builder
                ->orderBy('created_at', 'DESC')
                ->paginate(20),
            'pager'   => $organizationModel->pager,
            'filters' => ['q' => $q, 'status' => $status],
        ]);
    }

    public function exportCsv()
    {
        $q = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $model = new OrganizationModel();
        $builder = $model;

        if ($q !== '') {
            $builder->groupStart()
                ->like('name', $q)
                ->orLike('slug', $q)
                ->orLike('email', $q)
                ->orLike('phone', $q)
                ->groupEnd();
        }
        if (in_array($status, ['active', 'inactive', 'suspended'], true)) {
            $builder->where('status', $status);
        }

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Name', 'Slug', 'Email', 'Phone', 'Address', 'Status', 'Created']);
        foreach ($builder->orderBy('name', 'ASC')->findAll() as $organization) {
            fputcsv($handle, [
                $organization['name'] ?? '', $organization['slug'] ?? '',
                $organization['email'] ?? '', $organization['phone'] ?? '',
                $organization['address'] ?? '', $organization['status'] ?? '',
                $organization['created_at'] ?? '',
            ]);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="organizations-' . date('Y-m-d') . '.csv"')
            ->setBody($csv);
    }

    public function create()
    {
        return view('organizations/create', [
            'title'      => 'Create Organization',
            'activeMenu' => 'organizations',
        ]);
    }

   public function store()
{
    $organizationModel = new OrganizationModel();

    $data = [
        'name'    => trim((string) $this->request->getPost('name')),
        'slug'    => trim((string) $this->request->getPost('slug')),
        'email'   => trim((string) $this->request->getPost('email')),
        'phone'   => trim((string) $this->request->getPost('phone')),
        'address' => trim((string) $this->request->getPost('address')),
        'status'  => 'active',
    ];

    $duplicate = $organizationModel
        ->where('slug', $data['slug'])
        ->first();

    if ($duplicate) {
        return redirect()->back()
            ->withInput()
            ->with('errors', [
                'slug' => 'This slug is already being used by another organization.'
            ]);
    }

    if (!$organizationModel->insert($data)) {
        return redirect()->back()
            ->withInput()
            ->with('errors', $organizationModel->errors());
    }

    (new AuditLogModel())->record('create', 'organization', 'Organization created: ' . $data['name'], (int) $organizationModel->getInsertID(), (int) $organizationModel->getInsertID());

    return redirect()->to('/organizations')
        ->with('success', 'Organization created successfully.');
}

    public function show($id)
{
    $organizationModel = new \App\Models\OrganizationModel();
    $organization = $organizationModel->find($id);

    if (!$organization) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $stats = [
        'branches'  => (new BranchModel())->where('organization_id', $id)->countAllResults(),
        'staff'     => (new UserModel())->where('organization_id', $id)->where('role !=', 'super_admin')->countAllResults(),
        'visitors'  => (new VisitorModel())->where('organization_id', $id)->countAllResults(),
        'visits'    => (new VisitModel())->where('organization_id', $id)->countAllResults(),
        'on_site'   => (new VisitModel())->where('organization_id', $id)->where('status', 'checked_in')->countAllResults(),
    ];

    return view('organizations/show', [
        'title'        => 'Organization Details',
        'activeMenu'   => 'organizations',
        'organization' => $organization,
        'stats'        => $stats,
    ]);
}

public function edit($id)
{
    $organizationModel = new \App\Models\OrganizationModel();
    $organization = $organizationModel->find($id);

    if (!$organization) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    return view('organizations/edit', [
        'title'        => 'Edit Organization',
        'activeMenu'   => 'organizations',
        'organization' => $organization,
    ]);
}

public function update($id)
{
    $organizationModel = new \App\Models\OrganizationModel();
    $organization = $organizationModel->find($id);

    if (!$organization) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $data = [
        'name'    => trim((string) $this->request->getPost('name')),
        'slug'    => trim((string) $this->request->getPost('slug')),
        'email'   => trim((string) $this->request->getPost('email')),
        'phone'   => trim((string) $this->request->getPost('phone')),
        'address' => trim((string) $this->request->getPost('address')),
        'status'  => trim((string) $this->request->getPost('status')),
    ];

    $duplicate = $organizationModel
    ->where('slug', $data['slug'])
    ->where('id !=', $id)
    ->first();

    if ($duplicate) {
        return redirect()->back()
            ->withInput()
            ->with('errors', [
                'slug' => 'This slug is already being used by another organization.'
            ]);
    }

    if (!$organizationModel->update($id, $data)) {
        return redirect()->back()
            ->withInput()
            ->with('errors', $organizationModel->errors());
    }

    (new AuditLogModel())->record('update', 'organization', 'Organization updated: ' . $data['name'], (int) $id, (int) $id);

    return redirect()->to('/organizations/' . $id)
        ->with('success', 'Organization updated successfully.');
}
}