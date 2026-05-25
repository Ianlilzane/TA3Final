<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // 🚀 TESTING BYPASS OVERRIDE: 
        // Baguhin mo itong gawing 'true' kung gusto mong pumasok DRETSO nang walang harang ang login form.
        $bypassLoginForTesting = true; 

        if (!$bypassLoginForTesting) {
            if (!session()->get('is_logged_in') || session()->get('role_id') != 1) {
                return redirect()->to(base_url('login'));
            }
        }

        // 1. Kuhanin ang Kabuuang Benta (Kasama ang mga Approved at Completed Orders)
        $revenueRow = $this->db->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'Order Approved / Out for Delivery' OR status = 'Delivered & Completed'")->getRowArray();
        $totalRevenue = $revenueRow['total'] ?? 0;

        // 2. Kuhanin ang Listahan ng Pending Orders para sa Queue
        $pendingOrders = $this->db->query("SELECT * FROM orders WHERE status = 'Pending Approval' ORDER BY id DESC")->getResultArray();

        // 3. Kuhanin ang Lahat ng Orders para sa Sales Ledger
        $allOrders = $this->db->query("SELECT * FROM orders ORDER BY id DESC")->getResultArray();

        // 4. Kuhanin ang Mga Produkto at Bilangin ang may Low Stock (kunwari low stock kapag <= 5)
        $inventoryProducts = $this->db->query("SELECT * FROM products ORDER BY id ASC")->getResultArray();
        $lowStockCount = 0;
        foreach ($inventoryProducts as $p) {
            if ($p['stock'] <= 5) $lowStockCount++;
        }

        // 5. Kuhanin ang listahan ng Users para sa User Management Tab
        $allUsers = $this->db->query("SELECT * FROM users ORDER BY role_id ASC")->getResultArray();

        /**
         * 📊 DYNAMIC BEST SELLING MOTOR PARTS ENGINE
         * Babasahin natin ang mga items mula sa mga aprubado o nakumpletong orders para i-render sa chart
         */
        $completedOrders = $this->db->query("SELECT items FROM orders WHERE status = 'Order Approved / Out for Delivery' OR status = 'Delivered & Completed'")->getResultArray();
        
        $salesCount = [];

        foreach ($completedOrders as $order) {
            $itemsText = $order['items'];
            // Text parser para sa format na: Pangalan ng Item (xDami)
            preg_match_all('/([^,]+)\s\(x(\d+)\)/', $itemsText, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $itemName = trim($match[1]);
                $quantity = intval($match[2]);
                
                if (isset($salesCount[$itemName])) {
                    $salesCount[$itemName] += $quantity;
                } else {
                    $salesCount[$itemName] = $quantity;
                }
            }
        }

        // I-sort pababa ang mga benta para makuha ang pinakamabentang item sa itaas
        arsort($salesCount);

        // Kumuha lang ng Top 5 items para hindi sumasabog ang visuals ng bar chart mo
        $topSales = array_slice($salesCount, 0, 5, true);

        return view('admin/dashboard', [
            'totalRevenue'      => $totalRevenue,
            'pendingOrders'     => $pendingOrders,
            'allOrders'         => $allOrders,
            'inventoryProducts' => $inventoryProducts,
            'lowStockCount'     => $lowStockCount,
            'allUsers'          => $allUsers,
            'bestSellers'       => $topSales // Ipinasa natin ang array ng totoong benta
        ]);
    }

    /**
     * TRANSACTION CONTROL ENGINE: Nag-aapruba at awtomatikong nagbabawas sa stock list
     */
    public function updateStatus()
    {
        $orderId = $this->request->getPost('order_id');
        $action  = $this->request->getPost('action');

        if (!$orderId || !$action) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Missing order reference variables.']);
        }

        if ($action === 'approve') {
            // 1. Kuhanin muna ang detalye ng text string ng order items (Hal: "RCB E-Series Caliper Set (x1)")
            $order = $this->db->query("SELECT items FROM orders WHERE id = ?", [$orderId])->getRowArray();
            
            if ($order) {
                $itemsText = $order['items'];
                
                // Gagamitan natin ng text parser para malaman kung anong piyesa ang binili at ilan ang dami nito
                preg_match_all('/([^,]+)\s\(x(\d+)\)/', $itemsText, $matches, PREG_SET_ORDER);
                
                foreach ($matches as $match) {
                    $itemName = trim($match[1]);
                    $quantityBought = intval($match[2]);
                    
                    // 2. I-deduct o ibawas na sa live SQL column row gamit ang column na 'name'
                    $this->db->query("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE name = ?", [$quantityBought, $itemName]);
                }
            }

            $newStatus = 'Order Approved / Out for Delivery';
            $msg = 'Order #' . $orderId . ' has been approved! Stocks successfully deducted from warehouse.';
        } else {
            $newStatus = 'Order Rejected / Cancelled';
            $msg = 'Order #' . $orderId . ' has been marked as rejected.';
        }

        // I-save ang bagong status ng transaction order file
        $this->db->query("UPDATE orders SET status = ? WHERE id = ?", [$newStatus, $orderId]);

        return $this->response->setJSON(['status' => 'success', 'message' => $msg]);
    }
}