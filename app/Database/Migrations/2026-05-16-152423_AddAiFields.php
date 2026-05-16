<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAiFields extends Migration
{
    public function up()
    {
        // Columna para almacenar el resumen generado por IA
        $this->forge->addColumn('alertas', [
            'ai_summary' => ['type' => 'TEXT', 'null' => true, 'after' => 'status']
        ]);

        // Toggle por empresa para activar/desactivar resumen IA
        $this->forge->addColumn('empresas', [
            'ai_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => false, 'after' => 'send_email']
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('alertas', 'ai_summary');
        $this->forge->dropColumn('empresas', 'ai_enabled');
    }
}
