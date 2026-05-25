<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ShopController extends BaseController
{
    protected $db;

    public function __construct()
    {
        // Pag-connect sa iyong Database setup
        $this->db = \Config\Database::connect();
    }

    /**
     * MAIN SHOP PORTAL VIEW
     * Hihilahin ang mga produkto at ang order history ng naka-login na customer
     */
    public function index()
    {
        // Kunin ang user_id mula sa session (May default na 7 para sa testing kung walang login session)
        $userId = session()->get('user_id') ?? 7; 

        // 1. Hilahin ang mga active orders ng customer para sa kanyang "My Purchase Records" modal
        $data['myOrders'] = $this->db->query(
            "SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC", 
            [$userId]
        )->getResultArray();

        // 2. DATABASE ALIGNMENT: Napatunayang 'name' ang gamit sa table kaya simpleng SELECT * na ang gamit natin
        $data['products'] = $this->db->query(
            "SELECT * FROM products ORDER BY id ASC"
        )->getResultArray();

        // 🚀 ROUTE FIX: Itinuro sa tamang view directory (shop folder -> index.php)
        return view('shop/index', $data);
    }

    /**
     * CUSTOMER CHECKOUT PROCESS
     * Sine-save ang summary ng cart papunta sa database orders table
     */
    public function placeOrder()
    {
        $userId     = session()->get('user_id') ?? 7;
        $fullname   = $this->request->getPost('fullname');
        $contact    = $this->request->getPost('contact');
        $address    = $this->request->getPost('address');
        $items      = $this->request->getPost('items');
        $totalAmount = $this->request->getPost('total_amount');

        // Validation checking kung walang laman ang fields
        if (!$fullname || !$contact || !$address || !$items) {
            return $this->response->setJSON([
                'status'  => 'error', 
                'message' => 'Please fill in all required shipping information fields.'
            ]);
        }

        // Isulat ang bagong record sa 'orders' table (Baseline Status: Pending Approval)
        $sql = "INSERT INTO orders (user_id, fullname, contact, address, items, total_amount, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'Pending Approval')";
        
        $inserted = $this->db->query($sql, [$userId, $fullname, $contact, $address, $items, $totalAmount]);

        if ($inserted) {
            return $this->response->setJSON([
                'status'  => 'success', 
                'message' => 'Order has been successfully registered to the database workflow.'
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error', 
                'message' => 'Database persistence layer failed to write the record.'
            ]);
        }
    }

    /**
     * CUSTOMER ORDER RECEIVED CONFIRMATION
     * Gagana kapag pinindot ng customer ang "Confirm Order Received" kapag dumating na ang order
     */
    public function confirmDelivery()
    {
        $orderId = $this->request->getPost('order_id');
        
        if (!$orderId) {
            return $this->response->setJSON([
                'status'  => 'error', 
                'message' => 'Invalid order specification reference ID.'
            ]);
        }

        // I-update ang status para maging pinal: 'Delivered & Completed'
        $this->db->query(
            "UPDATE orders SET status = 'Delivered & Completed' WHERE id = ?", 
            [$orderId]
        );

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Thank you for confirming! Order status marked as Delivered & Completed.'
        ]);
    }
}