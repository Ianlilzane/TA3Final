<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CreateTestAccounts extends Seeder
{
    public function run()
    {
        $data = [
            [
                'fullname' => 'System Administrator',
                'email'    => 'admin@test.com',
                'password' => '$2y$10$mC3Bv8w7iM1wH5mRj6m6Ou6rOfeK69MvNqXzFfG6uYkC2LpE0E5aG',
                'role_id'  => 1,
            ],
            [
                'fullname' => 'Finance Officer',
                'email'    => 'finance@test.com',
                'password' => '$2y$10$mC3Bv8w7iM1wH5mRj6m6Ou6rOfeK69MvNqXzFfG6uYkC2LpE0E5aG',
                'role_id'  => 3,
            ],
            [
                'fullname' => 'Test Customer',
                'email'    => 'customer@test.com',
                'password' => '$2y$10$mC3Bv8w7iM1wH5mRj6m6Ou6rOfeK69MvNqXzFfG6uYkC2LpE0E5aG',
                'role_id'  => 2,
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
