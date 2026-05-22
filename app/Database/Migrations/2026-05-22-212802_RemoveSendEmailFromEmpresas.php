<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveSendEmailFromEmpresas extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('empresas', 'send_email');
    }

    public function down()
    {
        $this->forge->addColumn('empresas', [
            'send_email' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
            ]
        ]);
    }
}
