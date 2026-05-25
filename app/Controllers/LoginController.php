<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class LoginController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * ⚡ REAL-TIME DATABASE ADMIN BYPASS
     * Puwersahang pagpasok gamit ang totoong admin profile sa database table mo
     */
    public function quickAdminLogin()
    {
        $session = session();

        // Hanapin ang tunay na account ng admin na may id = 5 o role_id = 1
        $admin = $this->db->query("SELECT * FROM users WHERE email = 'admin@test.com' AND role_id = 1 LIMIT 1", [])->getRowArray();

        if (!$admin) {
            return "⚠️ System Alert: Ang account na admin@test.com na may role_id = 1 ay hindi mahanap sa iyong database.";
        }

        // I-inject ang Session tokens gamit ang tamang table row elements
        $session->set([
            'user_id'   => $admin['id'], // Magiging id na 5 base sa table mo
            'username'  => $admin['fullname'], // "System Admin"
            'role_id'   => $admin['role_id'], // Value na 1
            'role'      => 'admin', // Flag identifier para sa security gate pass
            'logged_in' => true
        ]);

        return redirect()->to(base_url('admin/dashboard'));
    }
    
    /**
     * MAIN POST LOGIN PROCESSING
     */
    public function processLogin()
    {
        $session = session();
        
        // Pwede nating tanggapin kung 'email' o 'email_address' ang gamit sa HTML view template code
        $emailInput    = $this->request->getPost('email') ?? $this->request->getPost('email_address');
        $passwordInput = $this->request->getPost('password');

        // Hanapin ang user profile sa database gamit ang email match field
        $user = $this->db->query("SELECT * FROM users WHERE email = ?", [trim($emailInput)])->getRowArray();

        if ($user) {
            // I-verify ang security encryption token
            if (password_verify($passwordInput, $user['password']) || $passwordInput === $user['password']) {
                
                // Kalkulahin ang text-based role counterpart base sa real role_id mapping mo
                $roleText = 'customer';
                if (intval($user['role_id']) === 1) {
                    $roleText = 'admin';
                }

                $session->set([
                    'user_id'   => $user['id'],
                    'username'  => $user['fullname'],
                    'role_id'   => $user['role_id'],
                    'role'      => $roleText,
                    'logged_in' => true
                ]);

                // SPLIT ROUTING ENGINE BASE SA ROLE ID
                if (intval($user['role_id']) === 1) {
                    return redirect()->to(base_url('admin/dashboard'));
                } else {
                    return redirect()->to(base_url('shop'));
                }
            }
        }

        // Kung hindi tugma, ibalik sa screen na may warning ribbon notification
        return redirect()->back()->with('error', 'Invalid email or password');
    }
}