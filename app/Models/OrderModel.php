<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table            = 'orders'; // 👈 Siguraduhing tugma sa pangalan ng table mo sa DB
    protected $primaryKey       = 'id';
    protected $returnType       = 'array'; // 👈 Siguraduhing array ang balik para sa foreach loop
    protected $allowedFields    = ['fullname', 'items', 'total_amount', 'status', 'created_at']; 
}