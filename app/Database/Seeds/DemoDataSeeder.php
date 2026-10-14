<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // 1. Organization
        $this->db->table('organizations')->insert([
            'name'       => 'CHINT Uganda',
            'slug'       => 'chint-uganda',
            'email'      => 'info@chint.ug',
            'phone'      => '+256700000000',
            'address'    => 'Kampala, Uganda',
            'status'     => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $organizationId = $this->db->insertID();

        // 2. Branch
        $this->db->table('branches')->insert([
            'organization_id' => $organizationId,
            'name'            => 'Jinja Road Main Branch',
            'code'            => 'JINJA-MAIN',
            'address'         => 'Jinja Road, Kampala',
            'phone'           => '+256700000001',
            'status'          => 'active',
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        $branchId = $this->db->insertID();

        // 3. Host user
        $this->db->table('users')->insert([
            'organization_id' => $organizationId,
            'branch_id'       => $branchId,
            'name'            => 'Demo Host',
            'email'           => 'host@demo.local',
            'password_hash'   => password_hash('Host@12345', PASSWORD_DEFAULT),
            'role'            => 'branch_manager',
            'status'          => 'active',
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        $hostId = $this->db->insertID();

        // 4. Visitor
        $this->db->table('visitors')->insert([
            'organization_id'       => $organizationId,
            'full_name'             => 'John Visitor',
            'phone'                 => '+256700000002',
            'email'                 => 'visitor@example.com',
            'company'               => 'Demo Company Ltd',
            'identification_type'   => 'National ID',
            'identification_number' => 'TEST-001',
            'created_at'            => $now,
            'updated_at'            => $now,
        ]);

        $visitorId = $this->db->insertID();

        // 5. Active visit
        $this->db->table('visits')->insert([
            'organization_id' => $organizationId,
            'branch_id'       => $branchId,
            'visitor_id'      => $visitorId,
            'host_id'         => $hostId,
            'purpose'         => 'Product meeting',
            'check_in_at'     => $now,
            'check_out_at'    => null,
            'status'          => 'checked_in',
            'notes'           => 'Development demo visit',
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);
    }
}