<?php

namespace App\Models;

use CodeIgniter\Model;

class PlatformSettingModel extends Model
{
    protected $table = 'platform_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['setting_key', 'setting_value', 'updated_by', 'updated_at'];
    protected $useTimestamps = false;

    public function values(array $defaults): array
    {
        $settings = $defaults;
        foreach ($this->findAll() as $setting) {
            if (array_key_exists($setting['setting_key'], $defaults)) {
                $settings[$setting['setting_key']] = $setting['setting_value'];
            }
        }

        return $settings;
    }

    public function saveValues(array $values, int $userId): void
    {
        foreach ($values as $key => $value) {
            $existing = $this->where('setting_key', $key)->first();
            $data = [
                'setting_key' => $key,
                'setting_value' => (string) $value,
                'updated_by' => $userId,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $this->update($existing['id'], $data);
            } else {
                $this->insert($data);
            }
        }
    }
}
