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
        $session = session();
        $userModel = new UserModel();

        $fullname = trim($this->request->getPost('fullname'));
        $email = trim($this->request->getPost('email'));
        $password = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('password_confirmation');

        $errors = [];

        if (empty($fullname)) {
            $errors[] = 'Full name is required.';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }

        if (empty($password)) {
            $errors[] = 'Password is required.';
        } else {
            if (strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters long.';
            }
            if (!preg_match('/[A-Z]/', $password)) {
                $errors[] = 'Password must include at least one uppercase letter.';
            }
            if (!preg_match('/[a-z]/', $password)) {
                $errors[] = 'Password must include at least one lowercase letter.';
            }
            if (!preg_match('/[0-9]/', $password)) {
                $errors[] = 'Password must include at least one number.';
            }
            if (!preg_match('/[\W_]/', $password)) {
                $errors[] = 'Password must include at least one symbol or special character.';
            }
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if ($email && $userModel->where('email', $email)->first()) {
            $errors[] = 'The email address is already registered.';
        }

        if (!empty($errors)) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data = [
            'role_id'  => 2,
            'fullname' => $fullname,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];

        $userModel->insert($data);

        return redirect()->to('/login')->with('success', 'Your account has been created. Please sign in.');
    }

    public function login()
    {
        return view('auth/login');
    }

    public function loginStore()
    {
        $email = trim($this->request->getPost('email'));
        $password = $this->request->getPost('password');
        $userModel = new UserModel();

        if (empty($email) || empty($password)) {
            return redirect()->back()->withInput()->with('error', 'Email and password are required.');
        }

        $user = $userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        session()->set([
            'user_id'   => $user['id'],
            'fullname'  => $user['fullname'],
            'email'     => $user['email'],
            'role_id'   => (int) $user['role_id'],
            'role'      => $user['role_id'] == 1 ? 'admin' : ($user['role_id'] == 3 ? 'finance' : 'customer'),
            'logged_in' => true,
        ]);

        if ((int) $user['role_id'] === 1) {
            return redirect()->to('/admin/dashboard');
        }

        if ((int) $user['role_id'] === 3) {
            return redirect()->to('/finance/dashboard');
        }

        return redirect()->to('/shop');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}