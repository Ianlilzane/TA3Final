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
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');

        $data['myOrders'] = $this->db->query(
            "SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC", 
            [$userId]
        )->getResultArray();

        $data['products'] = $this->db->query(
            "SELECT * FROM products ORDER BY id ASC"
        )->getResultArray();

        return view('shop/index', $data);
    }

    /**
     * CUSTOMER CHECKOUT PROCESS
     * Sine-save ang summary ng cart papunta sa database orders table
     */
    public function placeOrder()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Please login to place an order.']);
        }

        $userId      = session()->get('user_id');
        $fullname    = trim($this->request->getPost('fullname'));
        $contact     = trim($this->request->getPost('contact'));
        $address     = trim($this->request->getPost('address'));
        $items       = trim($this->request->getPost('items'));
        $totalAmount = $this->request->getPost('total_amount');

        if (!$fullname || !$contact || !$address || !$items || !$totalAmount) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Please fill in all required shipping information fields.'
            ]);
        }

        if (!preg_match('/^\+?\d{10,15}$/', preg_replace('/\s+/', '', $contact))) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Please enter a valid contact number.'
            ]);
        }

        $sql = "INSERT INTO orders (user_id, fullname, contact, address, items, total_amount, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'Pending Approval')";

        $inserted = $this->db->query($sql, [$userId, $fullname, $contact, $address, $items, $totalAmount]);

        if ($inserted) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Order has been successfully registered to the database workflow.'
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Failed to save your order. Please try again later.'
        ]);
    }

    /**
     * CUSTOMER ORDER RECEIVED CONFIRMATION
     * Gagana kapag pinindot ng customer ang "Confirm Order Received" kapag dumating na ang order
     */
    public function confirmDelivery()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Please login first.']);
        }

        $orderId = $this->request->getPost('order_id');

        if (!$orderId) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Invalid order specification reference ID.'
            ]);
        }

        $userId = session()->get('user_id');
        $order = $this->db->query("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, $userId])->getRowArray();

        if (!$order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found or not your order.']);
        }

        $this->db->query(
            "UPDATE orders SET status = 'Delivered & Completed' WHERE id = ?", 
            [$orderId]
        );

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Thank you for confirming! Order status marked as Delivered & Completed.'
        ]);
    }

    public function cancelOrder()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Please login first.']);
        }

        $orderId = $this->request->getPost('order_id');
        $userId = session()->get('user_id');

        if (!$orderId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid order reference.']);
        }

        $order = $this->db->query("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, $userId])->getRowArray();

        if (!$order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found or not yours.']);
        }

        if ($order['status'] !== 'Pending Approval') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Only pending orders can be canceled.']);
        }

        $this->db->query("UPDATE orders SET status = 'Order Rejected / Cancelled' WHERE id = ?", [$orderId]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Your order has been canceled successfully.']);
    }
}