# OTP Authentication - Code Examples & Advanced Usage

## Using OtpService in Your Code

### Example 1: Manual OTP Generation

```php
<?php
namespace App\Controllers;

use App\Libraries\OtpService;

class MyController extends BaseController
{
    public function sendOtp()
    {
        $otpService = new OtpService();
        
        // Generate and send OTP
        $result = $otpService->generateAndSendOtp('user@example.com');
        
        if ($result['success']) {
            // OTP sent successfully
            return redirect()->to('/verify-otp?email=user@example.com')
                           ->with('success', $result['message']);
        } else {
            // Failed to send
            return redirect()->back()
                           ->with('error', $result['message']);
        }
    }
}
```

### Example 2: Verify OTP in Custom Logic

```php
$otpService = new OtpService();

// Verify OTP entered by user
$email = 'user@example.com';
$otpEntered = '123456';

$result = $otpService->verifyOtp($email, $otpEntered);

if ($result['success']) {
    // OTP is valid
    log_message('info', "Email {$email} verified successfully");
    // Proceed with next step
} else {
    // OTP is invalid
    log_message('warning', $result['message']);
    // Show error to user
}
```

### Example 3: Check User Verification Status

```php
$otpService = new OtpService();

// Check if user's email is verified
$email = 'user@example.com';
$isVerified = $otpService->isUserVerified($email);

if ($isVerified) {
    // User email is verified
    // Safe to proceed with sensitive operations
} else {
    // User email is not verified
    // Require OTP verification first
    return redirect()->to('/verify-otp?email=' . urlencode($email));
}
```

### Example 4: Resend OTP

```php
$otpService = new OtpService();

// Resend OTP to user
$email = 'user@example.com';
$result = $otpService->resendOtp($email);

if ($result['success']) {
    return response()->setJSON([
        'success' => true,
        'message' => 'New OTP sent to your email'
    ]);
} else {
    return response()->setJSON([
        'success' => false,
        'message' => 'Failed to resend OTP'
    ]);
}
```

## Extending the System

### Add SMS OTP Support

```php
<?php
namespace App\Libraries;

use Twilio\Rest\Client;

class SmsOtpService extends OtpService
{
    protected $twilioClient;

    public function __construct()
    {
        parent::__construct();
        
        // Initialize Twilio
        $this->twilioClient = new Client(
            getenv('twilio.account_sid'),
            getenv('twilio.auth_token')
        );
    }

    public function generateAndSendSmsOtp($phone)
    {
        // Generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Send via SMS
        $this->sendSmsOtp($phone, $otp);
        
        // Store OTP in database
        // ... update user with OTP ...
        
        return [
            'success' => true,
            'message' => 'OTP sent via SMS'
        ];
    }

    private function sendSmsOtp($phone, $otp)
    {
        $this->twilioClient->messages->create(
            $phone,
            [
                'from' => getenv('twilio.from_number'),
                'body' => "Your OTP is: {$otp}. It expires in 5 minutes."
            ]
        );
    }
}
```

### Add OTP to Existing Login

```php
public function twoFactorLogin()
{
    $otpService = new OtpService();
    
    // After password verification
    $user = $this->userModel->where('email', $email)->first();
    
    if ($user && password_verify($password, $user['password'])) {
        // Send OTP for 2FA
        $otpService->generateAndSendOtp($email);
        
        // Temporary session
        session()->set([
            'temp_user_id' => $user['id'],
            '2fa_pending' => true
        ]);
        
        return redirect()->to('/verify-2fa');
    }
}
```

### Custom Email Template

```php
// Override in OtpService.php - modify getEmailTemplate()

private function getEmailTemplate($otp, $fullname)
{
    return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial; background: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; }
                .header { background: #0F6E56; color: white; padding: 20px; }
                .content { background: white; padding: 20px; }
                .otp-box { 
                    background: #f0f0f0; 
                    border-left: 4px solid #0F6E56;
                    padding: 20px;
                    margin: 20px 0;
                    font-size: 28px;
                    font-weight: bold;
                    color: #0F6E56;
                    text-align: center;
                    letter-spacing: 5px;
                }
                .footer { text-align: center; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Email Verification</h1>
                </div>
                <div class='content'>
                    <p>Hi {$fullname},</p>
                    <p>Your one-time password (OTP) is:</p>
                    <div class='otp-box'>{$otp}</div>
                    <p>This code is valid for 5 minutes.</p>
                    <p>If you didn't request this, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2026 Your Company. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
    ";
}
```

## Security Best Practices

### 1. Rate Limiting

```php
// Add to AuthController
private function isRateLimited($email)
{
    $cache = \Config\Services::cache();
    $key = "otp_attempts_{$email}";
    
    $attempts = $cache->get($key) ?? 0;
    
    if ($attempts >= 5) {
        return true;
    }
    
    $cache->save($key, $attempts + 1, 3600); // 1 hour
    return false;
}
```

### 2. Audit Logging

```php
private function logOtpActivity($email, $action, $result)
{
    log_message('info', "OTP Activity - Email: {$email}, Action: {$action}, Result: {$result}");
    
    // Optional: Store in database for audit trail
    $auditLog = [
        'email' => $email,
        'action' => $action,
        'result' => $result,
        'ip_address' => $this->request->getIPAddress(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    // $this->auditLogModel->insert($auditLog);
}
```

### 3. IP Whitelisting (Optional)

```php
private function isIpAllowed($email, $ip)
{
    $allowedIps = getenv('allowed_ips'); // CSV list
    
    if (empty($allowedIps)) {
        return true;
    }
    
    $allowedList = array_map('trim', explode(',', $allowedIps));
    
    return in_array($ip, $allowedList);
}
```

## Database Queries

### Find users with unverified emails

```sql
SELECT * FROM users WHERE is_verified = FALSE;
```

### Check expired OTPs

```sql
SELECT * FROM users 
WHERE otp IS NOT NULL 
AND otp_expires_at < NOW();
```

### Users with too many attempts

```sql
SELECT * FROM users 
WHERE otp_attempts >= 5;
```

## Testing

### Unit Test Example

```php
<?php
namespace Tests\Unit;

use App\Libraries\OtpService;
use CodeIgniter\Test\CIUnitTestCase;

class OtpServiceTest extends CIUnitTestCase
{
    public function testGenerateOtp()
    {
        $otpService = new OtpService();
        $result = $otpService->generateAndSendOtp('test@example.com');
        
        $this->assertTrue($result['success']);
    }

    public function testInvalidOtp()
    {
        $otpService = new OtpService();
        $result = $otpService->verifyOtp('test@example.com', '000000');
        
        $this->assertFalse($result['success']);
    }
}
```

## Environment Variables Reference

```env
# MAILTRAP - Required
mailtrap.host = smtp.mailtrap.io
mailtrap.port = 465
mailtrap.username = YOUR_USERNAME
mailtrap.password = YOUR_PASSWORD
mailtrap.from_email = noreply@example.com
mailtrap.from_name = App Name

# OTP Settings - Optional
otp.expiry_minutes = 5
otp.length = 6
otp.max_attempts = 5

# Optional Features
rate_limit.enabled = true
rate_limit.max_requests = 5
rate_limit.window = 3600

# Optional SMS (if using SMS OTP)
twilio.account_sid = YOUR_SID
twilio.auth_token = YOUR_TOKEN
twilio.from_number = +1234567890
```

## Common Issues & Solutions

### Issue: PHPMailer not found
```bash
# Solution: Reinstall composer dependencies
composer install
```

### Issue: SMTP timeout
```php
// Add timeout to OtpService.php
$mail->Timeout = 10; // seconds
```

### Issue: Special characters in password
```php
// Use URL encoding for password in connection string
$password = urlencode(getenv('mailtrap.password'));
```

---

For more advanced implementations, refer to the main `OTP_AUTHENTICATION_GUIDE.md`
