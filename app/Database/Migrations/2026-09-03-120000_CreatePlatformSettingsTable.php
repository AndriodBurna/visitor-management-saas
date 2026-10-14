<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlatformSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'setting_key' => ['type' => 'VARCHAR', 'constraint' => 80, 'unique' => true],
            'setting_value' => ['type' => 'TEXT', 'null' => true],
            'updated_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('platform_settings');
    }

    public function down()
    {
        $this->forge->dropTable('platform_settings', true);
    }
}
