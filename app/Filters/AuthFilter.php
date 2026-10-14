<?php

namespace App\Filters;

use App\Models\UserModel;
use App\Models\OrganizationModel;
use App\Models\BranchModel;
use App\Controllers\RolePermissionController;
use App\Models\PlatformSettingModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // -------------------------------------------------
        // 1. User must already be authenticated
        // -------------------------------------------------
        if (!$session->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        $userId = $session->get('user_id');

        $timeout = (int) (new PlatformSettingModel())->values(['session_timeout_minutes' => '120'])['session_timeout_minutes'];
        if ($session->get('auth_started_at') && time() - (int) $session->get('auth_started_at') > $timeout * 60) {
            $session->destroy();
            return redirect()->to('/login')->with('error', 'Your session expired. Please sign in again.');
        }

        if (!$userId) {
            $session->destroy();

            return redirect()
                ->to('/login')
                ->with('error', 'Your session is no longer valid. Please sign in again.');
        }

        // -------------------------------------------------
        // 2. Re-check the real user record
        // -------------------------------------------------
        $userModel = new UserModel();

        $user = $userModel->find($userId);

        if (!$user) {
            $session->destroy();

            return redirect()
                ->to('/login')
                ->with('error', 'Your account could not be found.');
        }

        // Account may have been suspended after login.
        if (($user['status'] ?? '') !== 'active') {
            $session->destroy();

            return redirect()
                ->to('/login')
                ->with('error', 'Your account is no longer active.');
        }

        // -------------------------------------------------
        // 3. Re-check organization status
        // -------------------------------------------------
        // Super Admin belongs to the platform and does not
        // require an organization.
        if (($user['role'] ?? '') !== 'super_admin') {

            $organizationId = $user['organization_id'] ?? null;

            if (empty($organizationId)) {
                $session->destroy();

                return redirect()
                    ->to('/login')
                    ->with(
                        'error',
                        'Your account is not assigned to an organization.'
                    );
            }

            $organizationModel = new OrganizationModel();

            $organization = $organizationModel->find($organizationId);

            if (
                !$organization ||
                ($organization['status'] ?? '') !== 'active'
            ) {
                $session->destroy();

                return redirect()
                    ->to('/login')
                    ->with(
                        'error',
                        'Your organization is currently inactive. Please contact the system administrator.'
                    );
            }
        }

        // -------------------------------------------------
        // 4. Branch-scoped users must have a valid,
        //    active branch
        // -------------------------------------------------
        if (
            in_array(
                $user['role'] ?? '',
                ['branch_manager', 'receptionist'],
                true
            )
        ) {
            $branchId = $user['branch_id'] ?? null;

            if (empty($branchId)) {
                $session->destroy();

                return redirect()
                    ->to('/login')
                    ->with(
                        'error',
                        'Your account is not assigned to a location.'
                    );
            }

            $branchModel = new BranchModel();

            $branch = $branchModel
                ->where('organization_id', $user['organization_id'])
                ->find($branchId);

            if (
                !$branch ||
                ($branch['status'] ?? '') !== 'active'
            ) {
                $session->destroy();

                return redirect()
                    ->to('/login')
                    ->with(
                        'error',
                        'Your assigned location is currently unavailable.'
                    );
            }
        }

        // -------------------------------------------------
        // 5. Enforce requested role
        // -------------------------------------------------
        if (!empty($arguments)) {
            $userRole = $user['role'];

            if (!in_array($userRole, $arguments, true)) {
                return redirect()->to('/dashboard');
            }
        }

        $permission = $this->permissionForRequest($request, $user['role']);
        if ($permission !== null && !RolePermissionController::permissionEnabled($user['role'], $permission)) {
            return redirect()->to('/dashboard')->with('error', 'Your role does not have access to that workflow.');
        }
    }

    private function permissionForRequest(RequestInterface $request, string $role): ?string
    {
        if ($role === 'super_admin') {
            return null;
        }

        $path = trim($request->getUri()->getPath(), '/');
        $path = preg_replace('#^index\.php/?#', '', $path);

        if ($path === 'visitors' || preg_match('#^visitors/\d+$#', $path)) {
            return 'view_visitors';
        }
        if ($path === 'visitors/checkin' || preg_match('#^visitors/\d+/checkout$#', $path)) {
            return 'manage_checkins';
        }
        if ($path === 'visits') {
            return 'view_visits';
        }
        if (preg_match('#^visits/\d+/checkout$#', $path)) {
            return 'manage_checkins';
        }

        return null;
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
        //
    }
}