<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOtpToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'otp' => [
                'type'       => 'VARCHAR',
                'constraint' => '6',
                'null'       => true,
                'after'      => 'password',
            ],
            'otp_expires_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'otp',
            ],
            'is_verified' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'after'      => 'otp_expires_at',
            ],
            'otp_attempts' => [
                'type'       => 'INT',
                'default'    => 0,
                'after'      => 'is_verified',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', [
            'otp',
            'otp_expires_at',
            'is_verified',
            'otp_attempts',
        ]);
    }
}
