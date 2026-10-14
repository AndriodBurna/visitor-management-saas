<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\PlatformSettingModel;

class PlatformSettingsController extends BaseController
{
    private const DEFAULTS = [
        'platform_name' => 'Visitor Management SaaS',
        'timezone' => 'Africa/Kampala',
        'visitor_retention_days' => '365',
        'require_host' => '1',
        'email_notifications' => '1',
        'support_email' => '',
        'visitor_id_prefix' => 'VIS',
        'require_purpose' => '1',
        'auto_checkout_hours' => '0',
    ];

    public function index()
    {
        return view('settings/platform', [
            'title' => 'Platform Settings',
            'activeMenu' => 'platform-settings',
            'settings' => (new PlatformSettingModel())->values(self::DEFAULTS),
        ]);
    }

    public function update()
    {
        $timezone = trim((string) $this->request->getPost('timezone'));
        $retention = (int) $this->request->getPost('visitor_retention_days');
        $platformName = trim((string) $this->request->getPost('platform_name'));
        $supportEmail = trim((string) $this->request->getPost('support_email'));
        $visitorIdPrefix = strtoupper(trim((string) $this->request->getPost('visitor_id_prefix')));
        $autoCheckoutHours = (int) $this->request->getPost('auto_checkout_hours');

        if ($platformName === '' || strlen($platformName) > 150) {
            return redirect()->back()->withInput()->with('errors', ['platform_name' => 'Enter a platform name up to 150 characters.']);
        }
        if (!in_array($timezone, timezone_identifiers_list(), true)) {
            return redirect()->back()->withInput()->with('errors', ['timezone' => 'Select a valid timezone.']);
        }
        if ($retention < 30 || $retention > 3650) {
            return redirect()->back()->withInput()->with('errors', ['visitor_retention_days' => 'Retention must be between 30 and 3,650 days.']);
        }
        if ($supportEmail !== '' && !filter_var($supportEmail, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('errors', ['support_email' => 'Enter a valid support email address.']);
        }
        if (!preg_match('/^[A-Z0-9]{2,10}$/', $visitorIdPrefix)) {
            return redirect()->back()->withInput()->with('errors', ['visitor_id_prefix' => 'Use 2 to 10 letters or numbers for the visitor prefix.']);
        }
        if ($autoCheckoutHours < 0 || $autoCheckoutHours > 24) {
            return redirect()->back()->withInput()->with('errors', ['auto_checkout_hours' => 'Automatic checkout must be between 0 and 24 hours.']);
        }

        $values = [
            'platform_name' => $platformName,
            'timezone' => $timezone,
            'visitor_retention_days' => (string) $retention,
            'require_host' => $this->request->getPost('require_host') ? '1' : '0',
            'email_notifications' => $this->request->getPost('email_notifications') ? '1' : '0',
            'support_email' => $supportEmail,
            'visitor_id_prefix' => $visitorIdPrefix,
            'require_purpose' => $this->request->getPost('require_purpose') ? '1' : '0',
            'auto_checkout_hours' => (string) $autoCheckoutHours,
        ];
        (new PlatformSettingModel())->saveValues($values, (int) session('user_id'));
        (new AuditLogModel())->record('update', 'platform_settings', 'Platform settings updated.');

        return redirect()->to('/admin/settings')->with('success', 'Platform settings were saved successfully.');
    }
}
