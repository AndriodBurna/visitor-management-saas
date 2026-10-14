<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'organization_id' => null,
            'branch_id'       => null,
            'name'            => 'System Administrator',
            'email'           => 'admin@vms.local',
            'password_hash'   => password_hash('Admin@12345', PASSWORD_DEFAULT),
            'role'            => 'super_admin',
            'status'          => 'active',
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $this->db->table('users')->insert($data);
    }
}