# OTP Authentication with Mailtrap - Implementation Guide

## Overview
This guide explains the OTP (One-Time Password) authentication system integrated with Mailtrap email service for your CodeIgniter 4 application.

## System Architecture

### Components
1. **OtpService** - Core library handling OTP generation, sending, and verification
2. **AuthController** - Updated controller with OTP workflow
3. **Database Migration** - Adds OTP fields to users table
4. **Views** - OTP verification interface
5. **Mailtrap Integration** - Email service for sending OTP codes

## Installation & Setup

### 1. Install Dependencies
Dependencies have already been installed via Composer:
```bash
composer require phpmailer/phpmailer
```

### 2. Configure Mailtrap in .env

Update your `.env` file with Mailtrap credentials:

```env
# MAILTRAP CONFIGURATION
mailtrap.host = smtp.mailtrap.io
mailtrap.port = 465
mailtrap.username = YOUR_MAILTRAP_USERNAME
mailtrap.password = YOUR_MAILTRAP_PASSWORD
mailtrap.from_email = noreply@yourdomain.com
mailtrap.from_name = Your App Name

# OTP SETTINGS
otp.expiry_minutes = 5
otp.length = 6
```

**To get Mailtrap credentials:**
1. Go to [https://mailtrap.io](https://mailtrap.io)
2. Sign up or log in
3. Create a new inbox
4. Click "Show Credentials" under SMTP Settings
5. Copy the Username and Password
6. Use `smtp.mailtrap.io` as host and port `465`

### 3. Run Database Migration

Execute the migration to add OTP columns to the users table:

```bash
php spark migrate
```

This adds the following columns to the `users` table:
- `otp` - Stores the 6-digit OTP
- `otp_expires_at` - Timestamp when OTP expires
- `is_verified` - Boolean flag for email verification
- `otp_attempts` - Counter for failed OTP attempts

## Authentication Flow

### Registration Flow
1. User fills registration form
2. System validates input and creates user account
3. **OTP is generated and sent to user's email**
4. User is redirected to OTP verification page
5. User enters OTP from email
6. Upon successful verification, user can log in

### Login Flow
1. User enters email and password
2. System validates credentials
3. **If email not verified**, user is prompted to verify
4. New OTP is sent to email
5. User enters OTP to complete login
6. Upon verification, user is logged in and redirected to dashboard

## API Endpoints

### Registration
- **POST** `/register`
- Creates user and initiates OTP flow
- Redirects to `/verify-otp?email=user@example.com`

### Verify OTP
- **GET** `/verify-otp?email=user@example.com`
- Displays OTP verification form
- **POST** `/verify-otp`
- Verifies OTP and marks user as verified

### Resend OTP
- **POST** `/resend-otp`
- Generates and sends new OTP
- Returns JSON response

### Login
- **GET** `/login`
- Shows login form
- **POST** `/login`
- Authenticates user (checks OTP verification)

## Class Documentation

### OtpService Library

**Location:** `app/Libraries/OtpService.php`

#### Methods

##### `generateAndSendOtp($email)`
Generates a 6-digit OTP and sends it to the user's email.

```php
$otpService = new OtpService();
$result = $otpService->generateAndSendOtp('user@example.com');
// Returns: ['success' => true/false, 'message' => '...']
```

##### `verifyOtp($email, $otp)`
Verifies the OTP entered by user.

```php
$result = $otpService->verifyOtp('user@example.com', '123456');
// Returns: ['success' => true/false, 'message' => '...']
```

##### `isUserVerified($email)`
Checks if user's email is verified.

```php
$isVerified = $otpService->isUserVerified('user@example.com');
// Returns: true/false
```

##### `resendOtp($email)`
Resends OTP to user's email.

```php
$result = $otpService->resendOtp('user@example.com');
```

## Security Features

1. **OTP Expiration** - OTPs expire after 5 minutes (configurable)
2. **Attempt Limiting** - Maximum 5 failed attempts before requiring new OTP
3. **Hashed Passwords** - Using PHP's `password_hash()` and `password_verify()`
4. **Session Management** - Secure session handling with encrypted data
5. **CSRF Protection** - CSRF tokens on all forms
6. **Email Validation** - Server-side email validation

## Configuration Options

Edit `.env` to customize:

```env
# OTP expiry time in minutes
otp.expiry_minutes = 5

# OTP length (digits)
otp.length = 6

# Mailtrap credentials
mailtrap.host = smtp.mailtrap.io
mailtrap.port = 465
mailtrap.username = your_username
mailtrap.password = your_password
```

## Troubleshooting

### OTP not being sent
1. Verify Mailtrap credentials in `.env`
2. Check logs in `writable/logs/`
3. Ensure PHPMailer is properly installed: `composer show phpmailer/phpmailer`
4. Test Mailtrap connection

### User can't log in after verification
1. Check if `is_verified` is set to `true` in database
2. Clear session data and try again
3. Check error logs

### OTP always expires immediately
1. Verify server time is correct
2. Check `otp_expires_at` format in database (should be DATETIME)
3. Verify `otp.expiry_minutes` setting in `.env`

## Testing OTP System

### Test in Development
1. Use Mailtrap's test inbox
2. Emails won't actually be sent, but appear in the inbox
3. Check email content and formatting

### Test with Real Email (Production)
1. Update Mailtrap credentials for production account
2. Use production-level security settings
3. Test with actual email addresses

## File Structure

```
app/
├── Controllers/
│   └── AuthController.php       (Updated with OTP methods)
├── Libraries/
│   └── OtpService.php          (OTP handling library)
├── Models/
│   └── UserModel.php           (Updated with OTP fields)
├── Database/
│   └── Migrations/
│       └── 2026-05-25-120000_add_otp_to_users.php
├── Config/
│   └── Routes.php              (Updated with OTP routes)
└── Views/
    └── auth/
        └── verify_otp.php      (OTP verification form)

.env                             (Mailtrap configuration)
composer.json                    (Updated with PHPMailer)
```

## Next Steps

1. **Migrate Database:**
   ```bash
   php spark migrate
   ```

2. **Configure Mailtrap:**
   - Update `.env` with your Mailtrap credentials
   - Test sending an email

3. **Test Registration:**
   - Fill out registration form
   - Check email for OTP
   - Enter OTP to verify
   - Log in with verified account

4. **Optional Enhancements:**
   - Add email templates
   - Implement SMS OTP option
   - Add OTP regeneration limits
   - Implement two-factor authentication

## Support

For issues or questions:
1. Check logs: `writable/logs/`
2. Verify `.env` configuration
3. Review error messages in browser
4. Check Mailtrap inbox for emails

## Security Reminder

**Never commit actual Mailtrap credentials to version control.**

Use `.gitignore` to exclude `.env`:
```
.env
*.env
```

Keep production credentials secure and rotate them regularly.
