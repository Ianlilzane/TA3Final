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
        if (!session()->get('logged_in') || session()->get('role_id') != 1) {
            return redirect()->to(base_url('login'));
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

    public function createProduct()
    {
        if (!session()->get('logged_in') || session()->get('role_id') != 1) {
            return redirect()->to(base_url('login'));
        }

        $name = trim($this->request->getPost('name'));
        $category = trim($this->request->getPost('category'));
        $price = $this->request->getPost('price');
        $stock = $this->request->getPost('stock');
        $imageUrl = trim($this->request->getPost('image_url')) ?: null;

        if ($name === '' || $category === '' || $price === '' || $stock === '') {
            return redirect()->back()->with('error', 'Name, category, price, and stock are required to create a product.');
        }

        if (!is_numeric($price) || !is_numeric($stock) || $price < 0 || $stock < 0) {
            return redirect()->back()->with('error', 'Price and stock must be valid non-negative numbers.');
        }

        $hasImageColumn = (bool) $this->db->query("SHOW COLUMNS FROM products LIKE 'image_url'")->getRowArray();

        if ($imageUrl !== null && $imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return redirect()->back()->with('error', 'Please provide a valid image URL if you add one.');
        }

        if ($hasImageColumn) {
            $this->db->query("INSERT INTO products (name, category, price, stock, image_url) VALUES (?, ?, ?, ?, ?)", [$name, $category, $price, $stock, $imageUrl]);
        } else {
            $this->db->query("INSERT INTO products (name, category, price, stock) VALUES (?, ?, ?, ?)", [$name, $category, $price, $stock]);
        }

        return redirect()->back()->with('success', 'New product has been added to inventory.');
    }

    public function updateProduct()
    {
        if (!session()->get('logged_in') || session()->get('role_id') != 1) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized.']);
        }

        $productId = $this->request->getPost('product_id');
        $name = trim($this->request->getPost('name'));
        $category = trim($this->request->getPost('category'));
        $price = $this->request->getPost('price');
        $stock = $this->request->getPost('stock');
        $imageUrl = trim($this->request->getPost('image_url')) ?: null;

        if (!$productId || $name === '' || $category === '' || $price === '' || $stock === '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'All fields are required when updating a product.']);
        }

        if (!is_numeric($price) || !is_numeric($stock) || $price < 0 || $stock < 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Price and stock must be valid non-negative numbers.']);
        }

        if ($imageUrl !== null && $imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Please provide a valid image URL if you add one.']);
        }

        $hasImageColumn = (bool) $this->db->query("SHOW COLUMNS FROM products LIKE 'image_url'")->getRowArray();

        if ($hasImageColumn) {
            $this->db->query("UPDATE products SET name = ?, category = ?, price = ?, stock = ?, image_url = ? WHERE id = ?", [$name, $category, $price, $stock, $imageUrl, $productId]);
        } else {
            $this->db->query("UPDATE products SET name = ?, category = ?, price = ?, stock = ? WHERE id = ?", [$name, $category, $price, $stock, $productId]);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Product updated successfully.']);
    }

    public function deleteProduct()
    {
        if (!session()->get('logged_in') || session()->get('role_id') != 1) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized.']);
        }

        $productId = $this->request->getPost('product_id');

        if (!$productId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product ID is required for deletion.']);
        }

        $this->db->query("DELETE FROM products WHERE id = ?", [$productId]);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Product removed successfully from inventory.']);
    }

    public function updateProductImage()
    {
        if (!session()->get('logged_in') || session()->get('role_id') != 1) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized.']);
        }

        $productId = $this->request->getPost('product_id');
        $imageUrl = trim($this->request->getPost('image_url'));

        if (!$productId || $imageUrl === '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product ID and image URL are required.']);
        }

        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Please provide a valid image URL.']);
        }

        try {
            $hasColumn = $this->db->query("SHOW COLUMNS FROM products LIKE 'image_url'")->getRowArray();

            if (!$hasColumn) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Product image column is not defined in the database.']);
            }

            $this->db->query("UPDATE products SET image_url = ? WHERE id = ?", [$imageUrl, $productId]);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Product image updated successfully.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unable to update product image.']);
        }
    }
}