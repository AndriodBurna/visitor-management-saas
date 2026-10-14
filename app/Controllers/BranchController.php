<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\OrganizationModel;
use App\Models\AuditLogModel;

class BranchController extends BaseController
{
    public function platform()
    {
        $branchModel = new BranchModel();
        $q = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));

        if (!in_array($status, ['active', 'inactive', 'suspended'], true)) {
            $status = '';
        }

        $builder = $branchModel
            ->select('branches.*, organizations.name AS organization_name')
            ->select('COUNT(DISTINCT users.id) AS staff_count', false)
            ->select('COUNT(DISTINCT visits.id) AS visit_count', false)
            ->join('organizations', 'organizations.id = branches.organization_id', 'left')
            ->join('users', 'users.branch_id = branches.id AND users.role != \'super_admin\'', 'left')
            ->join('visits', 'visits.branch_id = branches.id', 'left')
            ->groupBy('branches.id');

        if ($q !== '') {
            $builder->groupStart()
                ->like('branches.name', $q)
                ->orLike('branches.code', $q)
                ->orLike('branches.address', $q)
                ->orLike('organizations.name', $q)
                ->groupEnd();
        }
        if ($status !== '') {
            $builder->where('branches.status', $status);
        }

        return view('branches/platform', [
            'title' => 'Platform Locations',
            'activeMenu' => 'platform-locations',
            'branches' => $builder->orderBy('branches.name', 'ASC')->paginate(20),
            'pager' => $branchModel->pager,
            'filters' => ['q' => $q, 'status' => $status],
        ]);
    }

    public function index($organizationId)
    {
        $organizationModel = new OrganizationModel();
        $branchModel = new BranchModel();

        $organization = $organizationModel->find($organizationId);

        if (!$organization) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $q = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));

        if (!in_array($status, ['active', 'inactive', 'suspended'], true)) {
            $status = '';
        }

        $builder = $branchModel
            ->where('organization_id', $organizationId);
        if ($q !== '') {
            $builder->groupStart()
                ->like('name', $q)
                ->orLike('code', $q)
                ->orLike('phone', $q)
                ->orLike('address', $q)
                ->groupEnd();
        }
        if ($status !== '') {
            $builder->where('status', $status);
        }

        return view('branches/index', [
            'title'        => 'Branches',
            'activeMenu'   => 'organizations',
            'organization' => $organization,
            'branches'     => $builder->orderBy('name', 'ASC')->paginate(20),
            'pager'        => $branchModel->pager,
            'filters'      => ['q' => $q, 'status' => $status],
        ]);
    }

    public function create($organizationId)
    {
        $organizationModel = new OrganizationModel();

        $organization = $organizationModel->find($organizationId);

        if (!$organization) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('branches/create', [
            'title'        => 'Add Branch',
            'activeMenu'   => 'organizations',
            'organization' => $organization,
        ]);
    }

    public function store($organizationId)
    {
        $organizationModel = new OrganizationModel();
        $branchModel = new BranchModel();

        $organization = $organizationModel->find($organizationId);

        if (!$organization) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'organization_id' => $organizationId,
            'name'            => trim((string) $this->request->getPost('name')),
            'code'            => trim((string) $this->request->getPost('code')),
            'address'         => trim((string) $this->request->getPost('address')),
            'phone'           => trim((string) $this->request->getPost('phone')),
            'status'          => 'active',
        ];

        if (!$branchModel->insert($data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $branchModel->errors());
        }

        (new AuditLogModel())->record('create', 'branch', 'Branch created: ' . $data['name'], (int) $branchModel->getInsertID(), (int) $organizationId);

        return redirect()
            ->to('/organizations/' . $organizationId . '/branches')
            ->with('success', 'Branch created successfully.');
    }

            public function show($organizationId, $branchId)
        {
            $organizationModel = new \App\Models\OrganizationModel();
            $branchModel = new \App\Models\BranchModel();

            $organization = $organizationModel->find($organizationId);

            if (!$organization) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            $branch = $branchModel
                ->where('organization_id', $organizationId)
                ->find($branchId);

            if (!$branch) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            return view('branches/show', [
                'title'        => 'Location Details',
                'activeMenu'   => 'organizations',
                'organization' => $organization,
                'branch'       => $branch,
            ]);
        }

        public function edit($organizationId, $branchId)
        {
            $organizationModel = new \App\Models\OrganizationModel();
            $branchModel = new \App\Models\BranchModel();

            $organization = $organizationModel->find($organizationId);

            if (!$organization) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            $branch = $branchModel
                ->where('organization_id', $organizationId)
                ->find($branchId);

            if (!$branch) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            return view('branches/edit', [
                'title'        => 'Edit Location',
                'activeMenu'   => 'organizations',
                'organization' => $organization,
                'branch'       => $branch,
            ]);
        }

        public function update($organizationId, $branchId)
        {
            $organizationModel = new \App\Models\OrganizationModel();
            $branchModel = new \App\Models\BranchModel();

            $organization = $organizationModel->find($organizationId);

            if (!$organization) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            $branch = $branchModel
                ->where('organization_id', $organizationId)
                ->find($branchId);

            if (!$branch) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            $data = [
                'name'    => trim((string) $this->request->getPost('name')),
                'code'    => trim((string) $this->request->getPost('code')),
                'phone'   => trim((string) $this->request->getPost('phone')),
                'address' => trim((string) $this->request->getPost('address')),
                'status'  => trim((string) $this->request->getPost('status')),
            ];

            if (!$branchModel->update($branchId, $data)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $branchModel->errors());
            }

            (new AuditLogModel())->record('update', 'branch', 'Branch updated: ' . $data['name'], (int) $branchId, (int) $organizationId);

            return redirect()
                ->to('/organizations/' . $organizationId . '/branches/' . $branchId)
                ->with('success', 'Location updated successfully.');
        }
}