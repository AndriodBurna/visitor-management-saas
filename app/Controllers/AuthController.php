<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\OrganizationModel;
use App\Models\AuditLogModel;
use App\Models\LoginAttemptModel;
use App\Models\PlatformSettingModel;

class AuthController extends BaseController
{
    public function login()
    {
        return view('auth/login');
    }

    public function attemptLogin()
    {
        $session = session();

        $email = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');
        $ipAddress = $this->request->getIPAddress();
        $security = (new PlatformSettingModel())->values(['max_login_attempts' => '5', 'lockout_minutes' => '15']);
        $loginAttempts = new LoginAttemptModel();

        if ($loginAttempts->isLocked($email, $ipAddress)) {
            return redirect()->back()->withInput()->with('error', 'Too many unsuccessful attempts. Try again later.');
        }

        $userModel = new UserModel();

        $user = $userModel
            ->where('email', $email)
            ->first();

        // -------------------------------------------------
        // 1. Validate credentials
        // -------------------------------------------------
        if (
            !$user ||
            !password_verify($password, $user['password_hash'])
        ) {
            $loginAttempts->recordFailure($email, $ipAddress, (int) $security['max_login_attempts'], (int) $security['lockout_minutes']);
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Invalid email or password.');
        }

        // -------------------------------------------------
        // 2. Check individual user account status
        // -------------------------------------------------
        if ($user['status'] !== 'active') {
            return redirect()
                ->back()
                ->with('error', 'Your account is not active.');
        }

        // -------------------------------------------------
        // 3. Check organization status
        // -------------------------------------------------
        // Super Admin belongs to the platform itself and
        // therefore does not depend on an organization.
        if ($user['role'] !== 'super_admin') {

            if (empty($user['organization_id'])) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Your account is not assigned to an organization.'
                    );
            }

            $organizationModel = new OrganizationModel();

            $organization = $organizationModel->find(
                $user['organization_id']
            );

            if (!$organization) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'The organization assigned to your account could not be found.'
                    );
            }

            if ($organization['status'] !== 'active') {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Your organization is currently inactive. Please contact the system administrator.'
                    );
            }
        }

        // -------------------------------------------------
        // 4. Authentication successful
        // -------------------------------------------------
        $loginAttempts->clear($email, $ipAddress);
        // Prevent session fixation.
        $session->regenerate(true);

        $session->set([
            'user_id'         => $user['id'],
            'user_name'       => $user['name'],
            'user_avatar'     => $user['avatar'] ?? '',
            'user_role'       => $user['role'],
            'organization_id' => $user['organization_id'],
            'branch_id'       => $user['branch_id'],
            'is_logged_in'    => true,
            'auth_started_at' => time(),
        ]);

        // -------------------------------------------------
        // 5. Record last successful login
        // -------------------------------------------------
        $userModel->update($user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);
        (new AuditLogModel())->record('login', 'user', 'User signed in.', (int) $user['id'], $user['organization_id'] ? (int) $user['organization_id'] : null, $user['branch_id'] ? (int) $user['branch_id'] : null);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        (new AuditLogModel())->record('logout', 'user', 'User signed out.', (int) session('user_id'));
        session()->destroy();

        return redirect()->to('/login');
    }
}