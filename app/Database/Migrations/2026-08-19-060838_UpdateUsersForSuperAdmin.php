<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateUsersForSuperAdmin extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('users', [
            'organization_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('users', [
            'organization_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);
    }
}