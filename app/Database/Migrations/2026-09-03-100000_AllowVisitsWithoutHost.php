<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AllowVisitsWithoutHost extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('visits', [
            'host_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('visits', [
            'host_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);
    }
}
