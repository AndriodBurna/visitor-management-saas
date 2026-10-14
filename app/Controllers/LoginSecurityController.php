<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\PlatformSettingModel;

class LoginSecurityController extends BaseController
{
    private const DEFAULTS = ['max_login_attempts' => '5', 'lockout_minutes' => '15', 'session_timeout_minutes' => '120', 'notify_failed_logins' => '1'];

    public function index()
    {
        return view('security/login', [
            'title' => 'Login Security',
            'activeMenu' => 'login-security',
            'settings' => (new PlatformSettingModel())->values(self::DEFAULTS),
        ]);
    }

    public function update()
    {
        $maxAttempts = (int) $this->request->getPost('max_login_attempts');
        $lockoutMinutes = (int) $this->request->getPost('lockout_minutes');
        $sessionTimeout = (int) $this->request->getPost('session_timeout_minutes');

        if ($maxAttempts < 3 || $maxAttempts > 10) {
            return redirect()->back()->withInput()->with('errors', ['max_login_attempts' => 'Use between 3 and 10 failed attempts.']);
        }
        if ($lockoutMinutes < 5 || $lockoutMinutes > 1440) {
            return redirect()->back()->withInput()->with('errors', ['lockout_minutes' => 'Lockout must be between 5 minutes and 24 hours.']);
        }
        if ($sessionTimeout < 15 || $sessionTimeout > 1440) {
            return redirect()->back()->withInput()->with('errors', ['session_timeout_minutes' => 'Session timeout must be between 15 minutes and 24 hours.']);
        }

        (new PlatformSettingModel())->saveValues([
            'max_login_attempts' => (string) $maxAttempts,
            'lockout_minutes' => (string) $lockoutMinutes,
            'session_timeout_minutes' => (string) $sessionTimeout,
            'notify_failed_logins' => $this->request->getPost('notify_failed_logins') ? '1' : '0',
        ], (int) session('user_id'));
        (new AuditLogModel())->record('update', 'login_security', 'Login security policy updated.');

        return redirect()->to('/admin/login-security')->with('success', 'Login security policy saved successfully.');
    }
}
