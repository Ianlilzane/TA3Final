<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Libraries\OtpService;

class AuthController extends BaseController
{
    protected $userModel;
    protected $otpService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->otpService = new OtpService();
    }

    public function register()
    {
        return view('auth/register');
    }

    public function registerStore()
    {
        $session = session();
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

        if ($email && $this->userModel->where('email', $email)->first()) {
            $errors[] = 'The email address is already registered.';
        }

        if (!empty($errors)) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        // Create user account
        $data = [
            'role_id'  => 2,
            'fullname' => $fullname,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'is_verified' => false,
        ];

        $this->userModel->insert($data);

        // Generate and send OTP
        $otpResult = $this->otpService->generateAndSendOtp($email);

        if (!$otpResult['success']) {
            return redirect()->back()->with('error', $otpResult['message']);
        }

        // Redirect to OTP verification page
        return redirect()->to('/verify-otp?email=' . urlencode($email))
                         ->with('success', 'Account created! Please check your email for the OTP code.');
    }

    public function verifyOtp()
    {
        $email = $this->request->getGet('email');
        if (!$email) {
            return redirect()->to('/login')->with('error', 'Invalid verification request.');
        }
        return view('auth/verify_otp', ['email' => $email]);
    }

    public function verifyOtpStore()
    {
        $email = trim($this->request->getPost('email'));
        $otp = trim($this->request->getPost('otp'));

        if (empty($email) || empty($otp)) {
            return redirect()->back()->withInput()->with('error', 'Email and OTP are required.');
        }

        $result = $this->otpService->verifyOtp($email, $otp);

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }

        return redirect()->to('/login')->with('success', 'Email verified successfully! You can now log in.');
    }

    public function resendOtp()
    {
        $email = trim($this->request->getPost('email'));

        if (empty($email)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Email is required.'
            ]);
        }

        $result = $this->otpService->resendOtp($email);

        return $this->response->setJSON($result);
    }

    public function login()
    {
        return view('auth/login');
    }

    public function loginStore()
    {
        $email = trim($this->request->getPost('email'));
        $password = $this->request->getPost('password');
        $selectedRole = $this->request->getPost('role') ?? 'customer';

        if (empty($email) || empty($password)) {
            return redirect()->back()->withInput()->with('error', 'Email and password are required.');
        }

        $user = $this->userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        // Check if user is verified (guard if column missing)
        if (!isset($user['is_verified']) || !$user['is_verified']) {
            $otpResult = $this->otpService->generateAndSendOtp($email);
            return redirect()->to('/verify-otp?email=' . urlencode($email))
                           ->with('warning', 'Your email is not verified. Please verify to continue.');
        }

        $expectedRoleId = $selectedRole === 'admin'
            ? 1
            : ($selectedRole === 'finance' ? 3 : 2);

        if ((int) $user['role_id'] !== $expectedRoleId) {
            return redirect()->back()->withInput()->with('error', 'The selected account type does not match the provided login credentials.');
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