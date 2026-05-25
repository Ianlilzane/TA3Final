<?php

namespace App\Libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class OtpService
{
    protected $db;
    protected $userModel;
    protected $mailtrap;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->userModel = new \App\Models\UserModel();
        
        // Initialize PHPMailer for Mailtrap
        $this->mailtrap = new PHPMailer(true);
    }

    /**
     * Generate and send OTP to user email
     * 
     * @param string $email
     * @return array
     */
    public function generateAndSendOtp($email)
    {
        try {
            // Check if user exists
            $user = $this->userModel->where('email', $email)->first();
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Email not found in our system.'
                ];
            }

            // Generate 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Calculate expiry time (5 minutes from now)
            $expiryMinutes = getenv('otp.expiry_minutes') ?: 5;
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiryMinutes} minutes"));

            // Update user with OTP
            $this->userModel->update($user['id'], [
                'otp' => $otp,
                'otp_expires_at' => $expiresAt,
                'otp_attempts' => 0,
            ]);

            // Send OTP via email
            $this->sendOtpEmail($email, $otp, $user['fullname']);

            return [
                'success' => true,
                'message' => 'OTP sent to your email address.'
            ];
        } catch (\Exception $e) {
            log_message('error', 'OTP generation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to generate OTP. Please try again.'
            ];
        }
    }

    /**
     * Send OTP email using Mailtrap SMTP
     * 
     * @param string $email
     * @param string $otp
     * @param string $fullname
     * @return bool
     * @throws Exception
     */
    public function sendOtpEmail($email, $otp, $fullname)
    {
        try {
            $mail = new PHPMailer(true);

            // SMTP configuration from .env (MAIL_*)
            $mail->isSMTP();
            $mail->Host       = getenv('MAIL_HOST') ?: 'smtp.mailtrap.io';
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('MAIL_USERNAME') ?: getenv('mailtrap.username');
            $mail->Password   = getenv('MAIL_PASSWORD') ?: getenv('mailtrap.password');

            // Determine encryption and port
            $encryption = strtolower(getenv('MAIL_ENCRYPTION') ?: getenv('mailtrap.encryption') ?: 'tls');
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->Port = (int)(getenv('MAIL_PORT') ?: getenv('mailtrap.port') ?: 587);

            // Email content
            $mail->setFrom(
                getenv('MAIL_FROM_ADDRESS') ?: getenv('mailtrap.from_email') ?: 'noreply@example.com',
                getenv('MAIL_FROM_NAME') ?: getenv('mailtrap.from_name') ?: 'Application'
            );
            $mail->addAddress($email, $fullname);
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code';

            // Email body
            $emailBody = $this->getEmailTemplate($otp, $fullname);
            $mail->Body = $emailBody;
            $mail->AltBody = "Your OTP code is: {$otp}";

            $mail->send();
            
            log_message('info', "OTP sent to {$email}");
            return true;
        } catch (Exception $e) {
            log_message('error', "Email send failed: {$mail->ErrorInfo}");
            throw $e;
        }
    }

    /**
     * Verify OTP
     * 
     * @param string $email
     * @param string $otp
     * @return array
     */
    public function verifyOtp($email, $otp)
    {
        try {
            $user = $this->userModel->where('email', $email)->first();
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found.'
                ];
            }

            // Check if OTP has expired
            if ($user['otp_expires_at'] && strtotime($user['otp_expires_at']) < time()) {
                return [
                    'success' => false,
                    'message' => 'OTP has expired. Please request a new one.'
                ];
            }

            // Check attempt limit
            if ($user['otp_attempts'] >= 5) {
                return [
                    'success' => false,
                    'message' => 'Too many failed attempts. Please request a new OTP.'
                ];
            }

            // Verify OTP
            if ($user['otp'] !== $otp) {
                // Increment attempts
                $this->userModel->update($user['id'], [
                    'otp_attempts' => $user['otp_attempts'] + 1,
                ]);

                return [
                    'success' => false,
                    'message' => 'Invalid OTP. Please try again.'
                ];
            }

            // OTP is valid - mark user as verified
            $this->userModel->update($user['id'], [
                'is_verified' => true,
                'otp' => null,
                'otp_expires_at' => null,
                'otp_attempts' => 0,
            ]);

            return [
                'success' => true,
                'message' => 'OTP verified successfully!'
            ];
        } catch (\Exception $e) {
            log_message('error', 'OTP verification error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error verifying OTP.'
            ];
        }
    }

    /**
     * Get HTML email template
     * 
     * @param string $otp
     * @param string $fullname
     * @return string
     */
    private function getEmailTemplate($otp, $fullname)
    {
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                    .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
                    .otp-box { background-color: #fff; border: 2px solid #007bff; padding: 20px; text-align: center; margin: 20px 0; border-radius: 5px; }
                    .otp-code { font-size: 32px; font-weight: bold; color: #007bff; letter-spacing: 5px; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Verify Your Email</h1>
                    </div>
                    <div class='content'>
                        <p>Hello <strong>{$fullname}</strong>,</p>
                        <p>Thank you for registering with us. Please use the following OTP code to verify your email address:</p>
                        <div class='otp-box'>
                            <div class='otp-code'>{$otp}</div>
                        </div>
                        <p>This OTP is valid for 5 minutes. If you didn't request this, please ignore this email.</p>
                        <p>Best regards,<br>The Application Team</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; 2026 Your Application. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    /**
     * Check if user is verified
     * 
     * @param string $email
     * @return bool
     */
    public function isUserVerified($email)
    {
        $user = $this->userModel->where('email', $email)->first();
        return $user && $user['is_verified'];
    }

    /**
     * Resend OTP
     * 
     * @param string $email
     * @return array
     */
    public function resendOtp($email)
    {
        return $this->generateAndSendOtp($email);
    }
}
