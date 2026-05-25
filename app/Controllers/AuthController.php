<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function register()
    {
        return view('auth/register');
    }

    public function registerStore()
    {
        $userModel = new \App\Models\UserModel();

        $data = [
            'role_id'  => 2, // customer by default
            'fullname' => $this->request->getPost('fullname'),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
        ];

        $userModel->insert($data);

        return redirect()->to('/login');
    }

    public function login()
    {
        return view('auth/login');
    }

    public function loginStore()
    {
        $email = $this->request->getPost('email');

        // 🚨 BRUTE FORCE BYPASS FOR TESTING
        // Titingnan lang ng system kung anong salita ang nandoon sa Email field:
        
        if (str_contains($email, 'admin')) {
            // 1. Kapag may salitang "admin" sa email, i-force bilang ADMIN (Role 1)
            session()->set([
                'user_id'   => 1,
                'role_id'   => 1,
                'fullname'  => 'Forced Admin Account',
                'logged_in' => true,
            ]);
            return redirect()->to('/admin/dashboard');

        } elseif (str_contains($email, 'finance')) {
            // 2. Kapag may salitang "finance" sa email, i-force bilang FINANCE (Role 3)
            session()->set([
                'user_id'   => 3,
                'role_id'   => 3,
                'fullname'  => 'Forced Finance Account',
                'logged_in' => true,
            ]);
            return redirect()->to('/finance/dashboard'); // Derektang itatapon sa berdeng dashboard mo
        }

        // 3. Kapag normal na customer o kahit anong itype
        session()->set([
            'user_id'   => 2,
            'role_id'   => 2,
            'fullname'  => 'Forced Customer Account',
            'logged_in' => true,
        ]);
        return redirect()->to('/shop');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}