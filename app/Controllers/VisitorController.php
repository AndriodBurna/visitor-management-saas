<?php

namespace App\Controllers;

use App\Models\VisitorModel;
use App\Models\VisitModel;
use App\Models\OrganizationModel;
use App\Models\BranchModel;
use App\Models\UserModel;
use App\Models\AuditLogModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class VisitorController extends BaseController
{
    protected VisitorModel $visitorModel;
    protected VisitModel $visitModel;
    protected OrganizationModel $organizationModel;
    protected BranchModel $branchModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->visitorModel      = new VisitorModel();
        $this->visitModel        = new VisitModel();
        $this->organizationModel = new OrganizationModel();
        $this->branchModel       = new BranchModel();
        $this->userModel         = new UserModel();
    }

    private function canAccessOrganization(int $organizationId): bool
    {
        $role = (string) session('user_role');

        return $role === 'super_admin'
            || (int) session('organization_id') === $organizationId;
    }

    private function canAccessBranch(int $organizationId, int $branchId): bool
    {
        $role = (string) session('user_role');

        if ($role === 'super_admin') {
            return true;
        }

        if ((int) session('organization_id') !== $organizationId) {
            return false;
        }

        if ($role === 'organization_admin') {
            return true;
        }

        return in_array($role, ['branch_manager', 'receptionist'], true)
            && (int) session('branch_id') === $branchId;
    }

    /**
     * Super Admin: platform-wide visitor directory.
     */
    public function index()
    {
        $q      = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $date   = trim((string) $this->request->getGet('date'));

        if (!in_array($status, ['on_site', 'not_on_site'], true)) {
            $status = '';
        }
        if ($date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = '';
        }

        $builder = $this->visitorModel
            ->select('
                visitors.*,
                organizations.name AS organization_name,
                MAX(visit_history.check_in_at) AS last_visit_at,
                MAX(CASE WHEN active_visits.status = "checked_in" THEN 1 ELSE 0 END) AS is_on_site
            ')
            ->join(
                'organizations',
                'organizations.id = visitors.organization_id',
                'left'
            )
            ->join(
                'visits AS visit_history',
                'visit_history.visitor_id = visitors.id',
                'left'
            )
            ->join(
                'visits AS active_visits',
                'active_visits.visitor_id = visitors.id AND active_visits.status = "checked_in"',
                'left'
            );

        if ($q !== '') {
            $builder->groupStart()
                ->like('visitors.full_name', $q)
                ->orLike('visitors.email', $q)
                ->orLike('visitors.phone', $q)
                ->orLike('visitors.company', $q)
                ->groupEnd();
        }

        $role = (string) session('user_role');
        if ($role !== 'super_admin') {
            $builder->where('visitors.organization_id', (int) session('organization_id'));
        }
        if (in_array($role, ['branch_manager', 'receptionist'], true)) {
            $builder->where('visit_history.branch_id', (int) session('branch_id'));
        }
        if ($date !== '') {
            $builder->where('visit_history.check_in_at >=', $date . ' 00:00:00')
                ->where('visit_history.check_in_at <=', $date . ' 23:59:59');
        }
        if ($status === 'on_site') {
            $builder->having('is_on_site', 1);
        } elseif ($status === 'not_on_site') {
            $builder->having('is_on_site', 0);
        }

        $builder
            ->groupBy('visitors.id')
            ->orderBy('last_visit_at', 'DESC');

        $visitors = $builder->paginate(20);

        return view('visitors/index', [
            'title'      => 'All Visitors',
            'activeMenu' => 'visitors',
            'visitors'   => $visitors,
            'pager'      => $this->visitorModel->pager,

            'filters' => [
                'q'      => $q,
                'status' => $status,
                'date'   => $date,
            ],
        ]);
    }

    public function exportCsv()
    {
        $q = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $date = trim((string) $this->request->getGet('date'));
        $builder = $this->visitorModel
            ->select('visitors.*, organizations.name AS organization_name, MAX(visit_history.check_in_at) AS last_visit_at, MAX(CASE WHEN active_visits.status = "checked_in" THEN 1 ELSE 0 END) AS is_on_site')
            ->join('organizations', 'organizations.id = visitors.organization_id', 'left')
            ->join('visits AS visit_history', 'visit_history.visitor_id = visitors.id', 'left')
            ->join('visits AS active_visits', 'active_visits.visitor_id = visitors.id AND active_visits.status = "checked_in"', 'left');

        if ($q !== '') {
            $builder->groupStart()->like('visitors.full_name', $q)->orLike('visitors.email', $q)->orLike('visitors.phone', $q)->orLike('visitors.company', $q)->groupEnd();
        }
        if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $builder->where('visit_history.check_in_at >=', $date . ' 00:00:00')->where('visit_history.check_in_at <=', $date . ' 23:59:59');
        }
        $builder->groupBy('visitors.id');
        if ($status === 'on_site') {
            $builder->having('is_on_site', 1);
        } elseif ($status === 'not_on_site') {
            $builder->having('is_on_site', 0);
        }

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Visitor', 'Organization', 'Company', 'Phone', 'Email', 'Identification', 'Status', 'Last Visit', 'Date Added']);
        foreach ($builder->orderBy('last_visit_at', 'DESC')->findAll() as $visitor) {
            fputcsv($handle, [$visitor['full_name'] ?? '', $visitor['organization_name'] ?? '', $visitor['company'] ?? '', $visitor['phone'] ?? '', $visitor['email'] ?? '', trim(($visitor['identification_type'] ?? '') . ' ' . ($visitor['identification_number'] ?? '')), !empty($visitor['is_on_site']) ? 'On site' : 'Not on site', $visitor['last_visit_at'] ?? '', $visitor['created_at'] ?? '']);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="visitors-' . date('Y-m-d') . '.csv"')
            ->setBody($csv);
    }

    /**
     * Super Admin: visitor profile + visit history.
     */
    public function show($visitorId)
    {
        $visitor = $this->visitorModel
            ->getWithOrganization((int) $visitorId);

        $visitorWasCreated = false;

        if (!$visitor) {
            throw PageNotFoundException::forPageNotFound(
                'Visitor not found.'
            );
        }

        if (!$this->canAccessOrganization((int) $visitor['organization_id'])) {
            throw PageNotFoundException::forPageNotFound('Visitor not found.');
        }

        if (in_array((string) session('user_role'), ['branch_manager', 'receptionist'], true)) {
            $hasAccessibleVisit = $this->visitModel
                ->where('visitor_id', $visitorId)
                ->where('branch_id', (int) session('branch_id'))
                ->first();

            if (!$hasAccessibleVisit) {
                throw PageNotFoundException::forPageNotFound('Visitor not found.');
            }
        }

        $visits = $this->visitModel
            ->select('
                visits.*,
                branches.name AS branch_name,
                users.name AS host_name
            ')
            ->join(
                'branches',
                'branches.id = visits.branch_id',
                'left'
            )
            ->join(
                'users',
                'users.id = visits.host_id',
                'left'
            )
            ->where('visits.visitor_id', $visitorId)
            ->orderBy('visits.created_at', 'DESC')
            ->findAll();

        if (in_array((string) session('user_role'), ['branch_manager', 'receptionist'], true)) {
            $visits = $this->visitModel
                ->select('
                    visits.*,
                    branches.name AS branch_name,
                    users.name AS host_name
                ')
                ->join('branches', 'branches.id = visits.branch_id', 'left')
                ->join('users', 'users.id = visits.host_id', 'left')
                ->where('visits.visitor_id', $visitorId)
                ->where('visits.branch_id', (int) session('branch_id'))
                ->orderBy('visits.created_at', 'DESC')
                ->findAll();
        }

        return view('visitors/show', [
            'title'      => 'Visitor Details',
            'activeMenu' => 'visitors',
            'visitor'    => $visitor,
            'visits'     => $visits,
        ]);
    }

    public function edit($visitorId)
    {
        $visitor = $this->visitorModel->getWithOrganization((int) $visitorId);

        if (!$visitor) {
            throw PageNotFoundException::forPageNotFound('Visitor not found.');
        }

        return view('visitors/edit', [
            'title'      => 'Edit Visitor',
            'activeMenu' => 'visitors',
            'visitor'    => $visitor,
        ]);
    }

    public function update($visitorId)
    {
        $visitorId = (int) $visitorId;
        $visitor = $this->visitorModel->find($visitorId);

        if (!$visitor) {
            throw PageNotFoundException::forPageNotFound('Visitor not found.');
        }

        $fullName = trim((string) $this->request->getPost('full_name'));
        $phone = trim((string) $this->request->getPost('phone'));
        $email = trim((string) $this->request->getPost('email'));
        $company = trim((string) $this->request->getPost('company'));
        $identificationType = trim((string) $this->request->getPost('identification_type'));
        $identificationNumber = trim((string) $this->request->getPost('identification_number'));
        $errors = [];

        if ($fullName === '') {
            $errors['full_name'] = 'Visitor name is required.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if ($email !== '' && $this->visitorModel
            ->where('organization_id', $visitor['organization_id'])
            ->where('email', $email)
            ->where('id !=', $visitorId)
            ->first()) {
            $errors['email'] = 'This email is already used by another visitor in this organization.';
        }

        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data = [
            'full_name' => $fullName,
            'phone' => $phone ?: null,
            'email' => $email ?: null,
            'company' => $company ?: null,
            'identification_type' => $identificationType ?: null,
            'identification_number' => $identificationNumber ?: null,
        ];

        if (!$this->visitorModel->update($visitorId, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->visitorModel->errors());
        }

        (new AuditLogModel())->record(
            'update',
            'visitor',
            'Visitor profile updated: ' . $fullName,
            $visitorId,
            (int) $visitor['organization_id']
        );

        return redirect()->to('/visitors/' . $visitorId)->with('success', 'Visitor profile updated successfully.');
    }

    /**
     * Super Admin: display New Check-In form.
     */
    public function checkIn()
    {
        $organizations = $this->organizationModel
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->findAll();

        $branches = $this->branchModel
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->findAll();

        $hosts = $this->userModel
            ->where('role !=', 'super_admin')
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->findAll();

        $role = (string) session('user_role');
        if ($role !== 'super_admin') {
            $organizations = $this->organizationModel
                ->where('id', (int) session('organization_id'))
                ->where('status', 'active')
                ->findAll();
            $branches = $this->branchModel
                ->where('organization_id', (int) session('organization_id'))
                ->where('status', 'active')
                ->orderBy('name', 'ASC')
                ->findAll();
            $hosts = $this->userModel
                ->where('organization_id', (int) session('organization_id'))
                ->where('role !=', 'super_admin')
                ->where('status', 'active')
                ->orderBy('name', 'ASC')
                ->findAll();
        }

        if (in_array($role, ['branch_manager', 'receptionist'], true)) {
            $branches = $this->branchModel
                ->where('id', (int) session('branch_id'))
                ->where('organization_id', (int) session('organization_id'))
                ->where('status', 'active')
                ->findAll();
            $hosts = $this->userModel
                ->where('organization_id', (int) session('organization_id'))
                ->groupStart()
                    ->where('branch_id', (int) session('branch_id'))
                    ->orWhere('branch_id', null)
                ->groupEnd()
                ->where('role !=', 'super_admin')
                ->where('status', 'active')
                ->orderBy('name', 'ASC')
                ->findAll();
        }

        return view('visitors/checkin', [
            'title'         => 'New Check-In',
            'activeMenu'    => 'checkin',
            'organizations' => $organizations,
            'branches'      => $branches,
            'hosts'         => $hosts,
        ]);
    }

    /**
     * Super Admin: close the visitor's active visit.
     */
    public function checkOut($visitorId)
    {
        $visitorId = (int) $visitorId;

        $activeVisit = $this->visitModel
            ->where('visitor_id', $visitorId)
            ->where('status', 'checked_in')
            ->orderBy('check_in_at', 'DESC')
            ->first();

        if (!$activeVisit) {
            return redirect()
                ->back()
                ->with('error', 'This visitor does not have an active visit.');
        }

        if (!$this->canAccessBranch(
            (int) $activeVisit['organization_id'],
            (int) $activeVisit['branch_id']
        )) {
            throw PageNotFoundException::forPageNotFound('Visitor not found.');
        }

        $updated = $this->visitModel->update($activeVisit['id'], [
            'check_out_at' => date('Y-m-d H:i:s'),
            'status'       => 'checked_out',
        ]);

        if (!$updated) {
            return redirect()
                ->back()
                ->with('error', 'The visit could not be checked out. Please try again.');
        }

        (new AuditLogModel())->record('checkout', 'visit', 'Visitor checked out.', (int) $activeVisit['id'], (int) $activeVisit['organization_id'], (int) $activeVisit['branch_id']);

        return redirect()
            ->to('/visitors/' . $visitorId)
            ->with('success', 'Visitor checked out successfully.');
    }

    /**
     * Super Admin: create/reuse visitor and create a visit.
     */
    public function storeCheckIn()
    {
        $organizationId = (int) $this->request->getPost('organization_id');
        $branchId       = (int) $this->request->getPost('branch_id');
        $hostId         = (int) $this->request->getPost('host_id');

        $fullName = trim((string) $this->request->getPost('full_name'));
        $phone    = trim((string) $this->request->getPost('phone'));
        $email    = trim((string) $this->request->getPost('email'));
        $company  = trim((string) $this->request->getPost('company'));

        $identificationType = trim(
            (string) $this->request->getPost('identification_type')
        );

        $identificationNumber = trim(
            (string) $this->request->getPost('identification_number')
        );

        $purpose = trim((string) $this->request->getPost('purpose'));
        $notes   = trim((string) $this->request->getPost('notes'));

        // -------------------------------------------------
        // 1. Basic validation
        // -------------------------------------------------

        $errors = [];

        if ($organizationId <= 0) {
            $errors['organization_id'] = 'Please select an organization.';
        }

        if ($branchId <= 0) {
            $errors['branch_id'] = 'Please select a location.';
        }

        if ($fullName === '') {
            $errors['full_name'] = 'Visitor name is required.';
        }

        if ($purpose === '') {
            $errors['purpose'] = 'Visit purpose is required.';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if (!empty($errors)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $errors);
        }

        if (!$this->canAccessOrganization($organizationId)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', [
                    'organization_id' => 'You cannot use this organization.',
                ]);
        }

        if (!$this->canAccessBranch($organizationId, $branchId)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', [
                    'branch_id' => 'You cannot use this location.',
                ]);
        }

        // -------------------------------------------------
        // 2. Validate organization
        // -------------------------------------------------

        $organization = $this->organizationModel
            ->where('status', 'active')
            ->find($organizationId);

        if (!$organization) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', [
                    'organization_id' =>
                        'The selected organization is unavailable.',
                ]);
        }

        // -------------------------------------------------
        // 3. Validate branch belongs to organization
        // -------------------------------------------------

        $branch = $this->branchModel
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->find($branchId);

        if (!$branch) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', [
                    'branch_id' =>
                        'The selected location does not belong to this organization or is inactive.',
                ]);
        }

        // -------------------------------------------------
        // 4. Validate host when supplied
        // -------------------------------------------------

        if ($hostId > 0) {
            $host = $this->userModel
                ->where('organization_id', $organizationId)
                ->where('role !=', 'super_admin')
                ->where('status', 'active')
                ->find($hostId);

            if (!$host) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('errors', [
                        'host_id' =>
                            'The selected host is not available for this organization.',
                    ]);
            }

            // A branch-scoped host must belong to this location.
            if (
                !empty($host['branch_id']) &&
                (int) $host['branch_id'] !== $branchId
            ) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('errors', [
                        'host_id' =>
                            'The selected host is assigned to a different location.',
                    ]);
            }
        } else {
            $hostId = null;
        }

        // -------------------------------------------------
        // 5. Find existing visitor inside this organization
        // -------------------------------------------------

        $visitor = null;

        // Strongest match: identification number.
        if ($identificationNumber !== '') {
            $visitor = $this->visitorModel
                ->where('organization_id', $organizationId)
                ->where(
                    'identification_number',
                    $identificationNumber
                )
                ->first();
        }

        // Next: email.
        if (!$visitor && $email !== '') {
            $visitor = $this->visitorModel
                ->where('organization_id', $organizationId)
                ->where('email', $email)
                ->first();
        }

        // Finally: phone.
        if (!$visitor && $phone !== '') {
            $visitor = $this->visitorModel
                ->where('organization_id', $organizationId)
                ->where('phone', $phone)
                ->first();
        }

        // -------------------------------------------------
        // 6. Create visitor if they do not already exist
        // -------------------------------------------------

        if (!$visitor) {
            $visitorData = [
                'organization_id'      => $organizationId,
                'full_name'            => $fullName,
                'phone'                => $phone ?: null,
                'email'                => $email ?: null,
                'company'              => $company ?: null,
                'identification_type'  => $identificationType ?: null,
                'identification_number'=> $identificationNumber ?: null,
            ];

            $visitorId = $this->visitorModel->insert($visitorData);

            if (!$visitorId) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with(
                        'errors',
                        $this->visitorModel->errors()
                    );
            }

            $visitorWasCreated = true;
        } else {
            $visitorId = (int) $visitor['id'];
        }

        // -------------------------------------------------
        // 7. Prevent duplicate active check-in
        // -------------------------------------------------

        $activeVisit = $this->visitModel
            ->where('visitor_id', $visitorId)
            ->where('organization_id', $organizationId)
            ->where('status', 'checked_in')
            ->first();

        if ($activeVisit) {
            if ($visitorWasCreated) {
                $this->visitorModel->delete($visitorId);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('errors', [
                    'full_name' =>
                        'This visitor is already checked in.',
                ]);
        }

        // -------------------------------------------------
        // 8. Create the visit
        // -------------------------------------------------

        $visitData = [
            'organization_id' => $organizationId,
            'branch_id'       => $branchId,
            'visitor_id'      => $visitorId,
            'host_id'         => $hostId,
            'purpose'         => $purpose,
            'check_in_at'     => date('Y-m-d H:i:s'),
            'check_out_at'    => null,
            'status'          => 'checked_in',
            'notes'           => $notes ?: null,
        ];

        $visitId = $this->visitModel->insert($visitData);

        if (!$visitId) {
            if ($visitorWasCreated) {
                $this->visitorModel->delete($visitorId);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'errors',
                    $this->visitModel->errors()
                );
        }

        if ($visitorWasCreated) {
            (new AuditLogModel())->record(
                'visitor_created',
                'visitor',
                'Visitor profile created: ' . $fullName,
                (int) $visitorId,
                $organizationId,
                $branchId
            );
        }

        $hostName = $hostId ? ($host['name'] ?? 'Assigned host') : 'No specific host';
        (new AuditLogModel())->record(
            'check_in',
            'visit',
            'Check-in created for ' . $fullName . ' · Host: ' . $hostName . ' · Purpose: ' . $purpose,
            (int) $visitId,
            $organizationId,
            $branchId
        );

        return redirect()
            ->to('/visitors/' . $visitorId)
            ->with(
                'success',
                'Visitor checked in successfully.'
            );
    }
}