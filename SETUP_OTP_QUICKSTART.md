# Quick Start - OTP Authentication Setup

## Step 1: Get Mailtrap Credentials (5 minutes)

1. Visit [https://mailtrap.io](https://mailtrap.io)
2. Sign up or log in
3. Navigate to **Inbox > SMTP Settings**
4. Note your credentials:
   - **Username** (usually a number like `12345678`)
   - **Password** (long alphanumeric string)

## Step 2: Update .env File

Open `.env` file and find the Mailtrap section, update:

```env
mailtrap.host = smtp.mailtrap.io
mailtrap.port = 465
mailtrap.username = YOUR_MAILTRAP_USERNAME
mailtrap.password = YOUR_MAILTRAP_PASSWORD
mailtrap.from_email = noreply@yourdomain.com
mailtrap.from_name = Your App Name
```

Replace:
- `YOUR_MAILTRAP_USERNAME` - Your Mailtrap username (number)
- `YOUR_MAILTRAP_PASSWORD` - Your Mailtrap password

## Step 3: Run Database Migration

Open terminal in your project root and run:

```bash
php spark migrate
```

This adds OTP fields to the users table:
- `otp` - The 6-digit code
- `otp_expires_at` - When it expires
- `is_verified` - Email verification status
- `otp_attempts` - Failed attempt counter

## Step 4: Test the System

1. Start your application
2. Go to **Register** page
3. Fill out registration form
4. Submit
5. You should be redirected to **Verify OTP** page
6. Check your **Mailtrap Inbox** for the OTP email
7. Enter the 6-digit code
8. Click **Verify OTP**
9. You should see success message
10. Now you can **Log In** with your credentials

## Step 5 (Optional): Customize OTP Settings

In `.env`, you can customize:

```env
# OTP expires after this many minutes
otp.expiry_minutes = 5

# Length of OTP code (digits)
otp.length = 6
```

## How It Works

### Registration
```
1. User submits registration form
   ↓
2. Account is created
   ↓
3. OTP is generated (6 random digits)
   ↓
4. Email is sent to user via Mailtrap
   ↓
5. User is shown OTP verification page
   ↓
6. User enters OTP from email
   ↓
7. System verifies OTP
   ↓
8. Email marked as verified
   ↓
9. User can now log in
```

### Login
```
1. User enters email & password
   ↓
2. Credentials verified
   ↓
3. Check if email is verified
   ↓
4. If NOT verified → Show OTP verification
   ↓
5. If verified → Log in & redirect to dashboard
```

## Features Included

✅ **6-digit OTP generation**
✅ **5-minute expiration** (configurable)
✅ **Email sending via Mailtrap**
✅ **Attempt limiting** (5 failed attempts)
✅ **Resend OTP functionality**
✅ **Beautiful UI** (matches your app design)
✅ **Keyboard navigation** (arrow keys, backspace)
✅ **Copy-paste support** for OTP
✅ **Security features** (CSRF, password hashing)
✅ **Session management**

## Troubleshooting

### Issue: No email received
**Solution:**
- Check Mailtrap is working: log into Mailtrap, check inbox
- Verify credentials in `.env` are correct
- Check application logs: `writable/logs/`

### Issue: OTP expired immediately
**Solution:**
- Check server time is correct
- Verify migration ran successfully
- Increase `otp.expiry_minutes` in `.env`

### Issue: Can't log in after OTP verification
**Solution:**
- Check `is_verified` is true in database users table
- Clear browser cookies
- Try registering with different email

### Issue: SMTP connection error
**Solution:**
- Verify port is 465 (use STARTTLS not PLAIN)
- Check username/password are correct
- Temporarily whitelist your IP in Mailtrap settings

## Files Changed

| File | Changes |
|------|---------|
| `.env` | Added Mailtrap configuration |
| `app/Models/UserModel.php` | Added OTP fields |
| `app/Controllers/AuthController.php` | Added OTP methods |
| `app/Config/Routes.php` | Added OTP routes |
| `app/Libraries/OtpService.php` | NEW - OTP handling |
| `app/Database/Migrations/*_add_otp_to_users.php` | NEW - Database changes |
| `app/Views/auth/verify_otp.php` | NEW - OTP verification UI |

## Database Schema

**New columns in `users` table:**

```sql
otp VARCHAR(6) NULL
otp_expires_at DATETIME NULL
is_verified BOOLEAN DEFAULT FALSE
otp_attempts INT DEFAULT 0
```

## Next Steps

1. ✅ Configure Mailtrap credentials
2. ✅ Run migration
3. ✅ Test registration flow
4. 📝 Customize email template (optional)
5. 📝 Add SMS OTP option (advanced)
6. 📝 Implement 2FA (advanced)

## Support Resources

- **Mailtrap Docs:** https://help.mailtrap.io/
- **PHPMailer Docs:** https://github.com/PHPMailer/PHPMailer
- **CodeIgniter 4 Docs:** https://codeigniter.com/user_guide/

## Security Reminder

⚠️ **Never commit `.env` to git!**

Add to `.gitignore`:
```
.env
.env.local
```

Always use strong, unique Mailtrap passwords in production!

---

**Setup Complete! Your OTP authentication system is ready.** 🎉
