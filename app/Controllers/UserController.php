<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\OrganizationModel;
use App\Models\BranchModel;
use App\Models\AuditLogModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class UserController extends BaseController
{
    protected UserModel $userModel;
    protected OrganizationModel $organizationModel;
    protected BranchModel $branchModel;

    public function __construct()
    {
        $this->userModel         = new UserModel();
        $this->organizationModel = new OrganizationModel();
        $this->branchModel       = new BranchModel();
    }

    /**
     * Display users/staff belonging to an organization.
     */
    public function index($organizationId)
    {
        $organization = $this->organizationModel->find($organizationId);

        if (!$organization) {
            throw PageNotFoundException::forPageNotFound(
                'Organization not found.'
            );
        }

        $q = trim((string) $this->request->getGet('q'));
        $role = trim((string) $this->request->getGet('role'));
        $status = trim((string) $this->request->getGet('status'));

        if (!in_array($role, ['organization_admin', 'branch_manager', 'receptionist'], true)) {
            $role = '';
        }
        if (!in_array($status, ['active', 'inactive', 'suspended'], true)) {
            $status = '';
        }

        $builder = $this->userModel
            ->select('users.*, branches.name AS branch_name')
            ->join('branches', 'branches.id = users.branch_id', 'left')
            ->where('users.organization_id', $organizationId)
            ->where('users.role !=', 'super_admin');

        if ($q !== '') {
            $builder->groupStart()
                ->like('users.name', $q)
                ->orLike('users.email', $q)
                ->orLike('users.phone', $q)
                ->groupEnd();
        }
        if ($role !== '') {
            $builder->where('users.role', $role);
        }
        if ($status !== '') {
            $builder->where('users.status', $status);
        }

        $users = $builder
            ->orderBy('users.created_at', 'DESC')
            ->paginate(20);

        return view('users/index', [
            'title'        => 'Staff & Users',
            'activeMenu'   => 'organizations',
            'organization' => $organization,
            'users'        => $users,
            'pager'        => $this->userModel->pager,
            'filters'      => ['q' => $q, 'role' => $role, 'status' => $status],
        ]);
    }

    public function platform()
    {
        $q = trim((string) $this->request->getGet('q'));
        $role = trim((string) $this->request->getGet('role'));
        $status = trim((string) $this->request->getGet('status'));

        if (!in_array($role, ['organization_admin', 'branch_manager', 'receptionist'], true)) {
            $role = '';
        }
        if (!in_array($status, ['active', 'inactive', 'suspended'], true)) {
            $status = '';
        }

        $builder = $this->userModel
            ->select('users.*, organizations.name AS organization_name, branches.name AS branch_name')
            ->join('organizations', 'organizations.id = users.organization_id', 'left')
            ->join('branches', 'branches.id = users.branch_id', 'left')
            ->where('users.role !=', 'super_admin');

        if ($q !== '') {
            $builder->groupStart()
                ->like('users.name', $q)
                ->orLike('users.email', $q)
                ->orLike('organizations.name', $q)
                ->groupEnd();
        }
        if ($role !== '') {
            $builder->where('users.role', $role);
        }
        if ($status !== '') {
            $builder->where('users.status', $status);
        }

        return view('users/platform', [
            'title' => 'Platform Staff',
            'activeMenu' => 'platform-staff',
            'users' => $builder->orderBy('users.created_at', 'DESC')->paginate(20),
            'pager' => $this->userModel->pager,
            'filters' => ['q' => $q, 'role' => $role, 'status' => $status],
        ]);
    }

    public function platformShow($userId)
    {
        $user = $this->userModel
            ->select('users.*, organizations.name AS organization_name, branches.name AS branch_name')
            ->join('organizations', 'organizations.id = users.organization_id', 'left')
            ->join('branches', 'branches.id = users.branch_id', 'left')
            ->where('users.role !=', 'super_admin')
            ->find($userId);

        if (!$user) {
            throw PageNotFoundException::forPageNotFound('Staff member not found.');
        }

        return view('users/platform_show', [
            'title' => 'Staff Account Details',
            'activeMenu' => 'platform-staff',
            'user' => $user,
        ]);
    }

    public function platformStatus($userId)
    {
        $user = $this->userModel->where('role !=', 'super_admin')->find($userId);
        if (!$user) {
            throw PageNotFoundException::forPageNotFound('Staff member not found.');
        }

        $status = trim((string) $this->request->getPost('status'));
        if (!in_array($status, ['active', 'inactive', 'suspended'], true)) {
            return redirect()->back()->with('error', 'Select a valid account status.');
        }
        if (($user['status'] ?? '') === $status) {
            return redirect()->back()->with('info', 'This account is already ' . $status . '.');
        }

        $this->userModel->update($userId, ['status' => $status]);
        (new AuditLogModel())->record('update', 'user', 'Staff account status changed from ' . ($user['status'] ?? 'unknown') . ' to ' . $status . '.', (int) $userId, $user['organization_id'] ? (int) $user['organization_id'] : null, $user['branch_id'] ? (int) $user['branch_id'] : null);

        return redirect()->to('/admin/staff/' . $userId)->with('success', 'Staff account status changed to ' . $status . '.');
    }

    public function resetPassword($userId)
    {
        $user = $this->userModel->where('role !=', 'super_admin')->find($userId);
        if (!$user) {
            throw PageNotFoundException::forPageNotFound('Staff member not found.');
        }

        $temporaryPassword = bin2hex(random_bytes(6));
        $this->userModel->update($userId, ['password_hash' => password_hash($temporaryPassword, PASSWORD_DEFAULT)]);
        (new AuditLogModel())->record('update', 'user', 'Staff account password reset by platform administrator.', (int) $userId, $user['organization_id'] ? (int) $user['organization_id'] : null, $user['branch_id'] ? (int) $user['branch_id'] : null);

        return redirect()->to('/admin/staff/' . $userId)->with('temporary_password', $temporaryPassword)->with('success', 'Temporary password generated. Share it securely with the staff member.');
    }

    /**
     * Display the create staff account form.
     */
    public function create($organizationId)
    {
        $organization = $this->organizationModel->find($organizationId);

        if (!$organization) {
            throw PageNotFoundException::forPageNotFound(
                'Organization not found.'
            );
        }

        $branches = $this->branchModel
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('users/create', [
            'title'        => 'Add Staff Member',
            'activeMenu'   => 'organizations',
            'organization' => $organization,
            'branches'     => $branches,
        ]);
    }

    public function store($organizationId)
    {
        $organization = $this->organizationModel->find($organizationId);

        if (!$organization) {
            throw PageNotFoundException::forPageNotFound(
                'Organization not found.'
            );
        }

        $name     = trim((string) $this->request->getPost('name'));
        $email    = trim((string) $this->request->getPost('email'));
        $phone    = trim((string) $this->request->getPost('phone'));
        $password = (string) $this->request->getPost('password');
        $role     = trim((string) $this->request->getPost('role'));
        $branchId = $this->request->getPost('branch_id');

        $allowedRoles = [
            'organization_admin',
            'branch_manager',
            'receptionist',
        ];

        if (!in_array($role, $allowedRoles, true)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', [
                    'role' => 'Invalid staff role selected.'
                ]);
        }

        if ($this->userModel->where('email', $email)->first()) {
            return redirect()->back()
                ->withInput()
                ->with('errors', [
                    'email' => 'This email address is already in use.'
                ]);
        }

        if (strlen($password) < 8) {
            return redirect()->back()
                ->withInput()
                ->with('errors', [
                    'password' => 'Password must be at least 8 characters.'
                ]);
        }

        if (in_array($role, ['branch_manager', 'receptionist'], true)) {

            if (empty($branchId)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', [
                        'branch_id' => 'A branch/location is required for this role.'
                    ]);
            }

            $branch = $this->branchModel
                ->where('organization_id', $organizationId)
                ->find($branchId);

            if (!$branch) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', [
                        'branch_id' => 'Invalid branch/location selected.'
                    ]);
            }
        } else {
            $branchId = null;
        }

        $data = [
            'organization_id' => $organizationId,
            'branch_id'       => $branchId ?: null,
            'name'            => $name,
            'email'           => $email,
            'phone'           => $phone,
            'password_hash'   => password_hash($password, PASSWORD_DEFAULT),
            'role'            => $role,
            'status'          => 'active',
        ];

        if (!$this->userModel->insert($data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->userModel->errors());
        }

        (new AuditLogModel())->record('create', 'user', 'Staff account created: ' . $name, (int) $this->userModel->getInsertID(), (int) $organizationId, $branchId ? (int) $branchId : null);

        return redirect()
            ->to('/organizations/' . $organizationId . '/users')
            ->with('success', 'Staff account created successfully.');
    }

    public function show($organizationId, $userId)
{
    $organization = $this->organizationModel->find($organizationId);

    if (!$organization) {
        throw PageNotFoundException::forPageNotFound('Organization not found.');
    }

    $user = $this->userModel
        ->select('users.*, branches.name AS branch_name')
        ->join('branches', 'branches.id = users.branch_id', 'left')
        ->where('users.organization_id', $organizationId)
        ->where('users.role !=', 'super_admin')
        ->find($userId);

    if (!$user) {
        throw PageNotFoundException::forPageNotFound('Staff member not found.');
    }

    return view('users/show', [
        'title'        => 'Staff Details',
        'activeMenu'   => 'organizations',
        'organization' => $organization,
        'user'         => $user,
    ]);
}

public function edit($organizationId, $userId)
{
    $organization = $this->organizationModel->find($organizationId);

    if (!$organization) {
        throw PageNotFoundException::forPageNotFound('Organization not found.');
    }

    $user = $this->userModel
        ->where('organization_id', $organizationId)
        ->where('role !=', 'super_admin')
        ->find($userId);

    if (!$user) {
        throw PageNotFoundException::forPageNotFound('Staff member not found.');
    }

    $branches = $this->branchModel
        ->where('organization_id', $organizationId)
        ->where('status', 'active')
        ->orderBy('name', 'ASC')
        ->findAll();

    return view('users/edit', [
        'title'        => 'Edit Staff Member',
        'activeMenu'   => 'organizations',
        'organization' => $organization,
        'user'         => $user,
        'branches'     => $branches,
    ]);
}

    public function update($organizationId, $userId)
    {
        $organization = $this->organizationModel->find($organizationId);

        if (!$organization) {
            throw PageNotFoundException::forPageNotFound('Organization not found.');
        }

        $user = $this->userModel
            ->where('organization_id', $organizationId)
            ->where('role !=', 'super_admin')
            ->find($userId);

        if (!$user) {
            throw PageNotFoundException::forPageNotFound('Staff member not found.');
        }

        $name     = trim((string) $this->request->getPost('name'));
        $email    = trim((string) $this->request->getPost('email'));
        $phone    = trim((string) $this->request->getPost('phone'));
        $role     = trim((string) $this->request->getPost('role'));
        $status   = trim((string) $this->request->getPost('status'));
        $branchId = $this->request->getPost('branch_id');

        $allowedRoles = [
            'organization_admin',
            'branch_manager',
            'receptionist',
        ];

        $allowedStatuses = [
            'active',
            'inactive',
            'suspended',
        ];

        if (!in_array($role, $allowedRoles, true)) {
            return redirect()->back()->withInput()->with('errors', [
                'role' => 'Invalid staff role selected.',
            ]);
        }

        if (!in_array($status, $allowedStatuses, true)) {
            return redirect()->back()->withInput()->with('errors', [
                'status' => 'Invalid account status.',
            ]);
        }

        $duplicateEmail = $this->userModel
            ->where('email', $email)
            ->where('id !=', $userId)
            ->first();

        if ($duplicateEmail) {
            return redirect()->back()->withInput()->with('errors', [
                'email' => 'This email address is already in use.',
            ]);
        }

        if (in_array($role, ['branch_manager', 'receptionist'], true)) {

            if (empty($branchId)) {
                return redirect()->back()->withInput()->with('errors', [
                    'branch_id' => 'A branch/location is required for this role.',
                ]);
            }

            $branch = $this->branchModel
                ->where('organization_id', $organizationId)
                ->find($branchId);

            if (!$branch) {
                return redirect()->back()->withInput()->with('errors', [
                    'branch_id' => 'Invalid branch/location selected.',
                ]);
            }

        } else {
            $branchId = null;
        }

        $data = [
            'name'      => $name,
            'email'     => $email,
            'phone'     => $phone,
            'role'      => $role,
            'branch_id' => $branchId ?: null,
            'status'    => $status,
        ];

        if (!$this->userModel->update($userId, $data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->userModel->errors());
        }

        (new AuditLogModel())->record('update', 'user', 'Staff account updated: ' . $name, (int) $userId, (int) $organizationId, $branchId ? (int) $branchId : null);

        return redirect()
            ->to('/organizations/' . $organizationId . '/users/' . $userId)
            ->with('success', 'Staff account updated successfully.');
    }
}