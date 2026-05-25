<?php

namespace App\Controllers;

// 🚀 I-import ang OrderModel nang manu-mano para hindi mag-null
use App\Models\OrderModel;

class FinanceController extends BaseController
{
    public function index()
    {
        // Proteksyon para siguradong Finance Officer lang ang makakapasok
        if (!session()->get('logged_in') || session()->get('role_id') != 3) {
            return redirect()->to('/login');
        }

        // 1. Buhayin ang totoong connection sa Orders database table
        $orderModel = new OrderModel();

        // 2. Kuhanin lang ang mga orders na bayad na o aprubado na (Kargadong benta)
        // ⚠️ TANDAAN: Palitan mo ang 'Order Approved / Out for Delivery' base sa saktong spelling ng status sa DB mo
        $approvedOrders = $orderModel->where('status', 'Order Approved / Out for Delivery')
                                    ->orderBy('id', 'DESC')
                                    ->findAll();
        
        // 3. I-compute ang Real Gross Revenue base sa mga nakuhang records
        $totalRevenue = 0;
        if (!empty($approvedOrders)) {
            foreach ($approvedOrders as $order) {
                $totalRevenue += $order['total_amount']; // Isasama sa kabuuan ang bawat total_amount ng order
            }
        }

        // 4. I-pack ang totoong data para ipadala sa HTML page
        $data = [
            'totalRevenue'     => $totalRevenue,
            'totalOrdersCount' => count($approvedOrders),
            'paidOrders'       => $approvedOrders,
            'fullname'         => session()->get('fullname')
        ];

        return view('finance/dashboard', $data);
    }

    public function transactions()
    {
        if (!session()->get('logged_in') || session()->get('role_id') != 3) {
            return redirect()->to('/login');
        }

        $orderModel = new OrderModel();
        
        // Kuhanin ang lahat ng orders na cleared na para sa history tab
        $data['paidOrders'] = $orderModel->where('status', 'Order Approved / Out for Delivery')
                                         ->orderBy('id', 'DESC')
                                         ->findAll();
                                         
        $data['fullname'] = session()->get('fullname');

        return view('finance/transactions', $data);
    }
}