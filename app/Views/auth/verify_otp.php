<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - OTP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; background: #f5f5f5; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; }
        
        .hero { text-align: center; margin-bottom: 2rem; }
        .hero h1 { font-size: 28px; font-weight: 500; color: #111; }
        .hero p { font-size: 15px; color: #666; margin-top: 0.5rem; }
        
        /* Verify Card */
        .verify-card { background: #fff; border: 2px solid #0F6E56; border-radius: 12px; padding: 2rem; width: 100%; max-width: 420px; display: flex; flex-direction: column; gap: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        
        .otp-icon { text-align: center; margin-bottom: 0.5rem; }
        .otp-icon i { font-size: 48px; color: #0F6E56; }
        
        .info-text { font-size: 14px; color: #666; text-align: center; }
        .info-text .email { font-weight: 600; color: #111; }
        
        /* Form groups */
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13px; font-weight: 500; color: #444; }
        
        .otp-input-group { display: flex; gap: 8px; justify-content: center; margin: 1.5rem 0; }
        .otp-input { width: 50px; height: 50px; border: 2px solid #ddd; border-radius: 8px; font-size: 24px; font-weight: 600; text-align: center; outline: none; transition: border-color 0.2s; }
        .otp-input:focus { border-color: #0F6E56; }
        .otp-input-group input[type="hidden"] { display: none; }
        
        .alert-success { background: #dcfce7; color: #166534; padding: 12px 14px; border-radius: 10px; border: 1px solid #bbf7d0; font-size: 13px; }
        .alert-warning { background: #fef3c7; color: #92400e; padding: 12px 14px; border-radius: 10px; border: 1px solid #fcd34d; font-size: 13px; }
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 14px; border-radius: 10px; border: 1px solid #f5c2c7; font-size: 13px; }
        
        .hidden-otp-input { display: none; }
        
        /* Buttons */
        .btn { display: block; text-align: center; padding: 11px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; text-decoration: none; border: none; width: 100%; transition: background 0.2s; }
        .btn-teal { background: #0F6E56; color: #fff; }
        .btn-teal:hover { background: #085041; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn-secondary:hover { background: #d1d5db; }
        
        /* Links and dividers */
        .divider { text-align: center; font-size: 13px; color: #666; margin-top: 1rem; }
        .divider a { color: #0F6E56; font-weight: 500; text-decoration: none; cursor: pointer; }
        .divider a:hover { text-decoration: underline; }
        
        .timer { text-align: center; font-size: 13px; color: #888; margin-top: 0.5rem; }
        
        .footer { margin-top: 2rem; font-size: 12px; color: #aaa; }
    </style>
</head>
<body>

    <div class="hero">
        <h1>Verify Your Email</h1>
        <p>We've sent a 6-digit code to your email address</p>
    </div>

    <div class="verify-card">
        <!-- Success Message -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert-success">
                <i class="ti ti-check" style="margin-right: 6px;"></i>
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <!-- Warning Message -->
        <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert-warning">
                <i class="ti ti-alert-circle" style="margin-right: 6px;"></i>
                <?= session()->getFlashdata('warning') ?>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert-error">
                <i class="ti ti-x" style="margin-right: 6px;"></i>
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <div class="otp-icon">
            <i class="ti ti-mail-check"></i>
        </div>

        <p class="info-text">
            Enter the OTP sent to <span class="email"><?= esc($email ?? '') ?></span>
        </p>

        <form action="<?= base_url('verify-otp') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="email" value="<?= esc($email ?? '') ?>">

            <div class="form-group">
                <label for="otp">One-Time Password (OTP)</label>
                <div class="otp-input-group">
                    <input type="text" id="otp" name="otp" class="hidden-otp-input" maxlength="6" pattern="[0-9]{6}" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="0">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="1">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="2">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="3">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="4">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="5">
                </div>
            </div>

            <button type="submit" class="btn btn-teal">
                Verify OTP <i class="ti ti-check" style="font-size: 14px; margin-left: 4px; vertical-align: middle;"></i>
            </button>
        </form>

        <div class="divider">
            Didn't receive the code?
            <a onclick="resendOtp()">Resend OTP</a>
        </div>

        <div style="text-align: center; margin-top: 1rem;">
            <a href="<?= base_url('login') ?>" style="color: #0F6E56; text-decoration: none; font-size: 13px;">
                <i class="ti ti-arrow-left" style="margin-right: 4px;"></i> Back to Login
            </a>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 Ordering System. All rights reserved.</p>
    </div>

    <script>
        // Handle OTP input
        const otpInputs = document.querySelectorAll('.otp-input');
        const hiddenOtpInput = document.getElementById('otp');

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                // Only allow numbers
                e.target.value = e.target.value.replace(/[^0-9]/g, '');

                // Move to next input if value is entered
                if (e.target.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }

                // Update hidden input with all OTP digits
                updateHiddenOtp();
            });

            input.addEventListener('keydown', (e) => {
                // Handle backspace
                if (e.key === 'Backspace' && input.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }

                // Handle arrow keys
                if (e.key === 'ArrowLeft' && index > 0) {
                    otpInputs[index - 1].focus();
                }
                if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').split('');
                otpInputs.forEach((inp, i) => {
                    inp.value = pastedData[i] || '';
                });
                updateHiddenOtp();
            });
        });

        function updateHiddenOtp() {
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            hiddenOtpInput.value = otp;
        }

        function resendOtp() {
            const email = '<?= esc($email ?? '') ?>';
            
            if (!email) {
                alert('Email not found. Please try again.');
                return;
            }

            const formData = new FormData();
            formData.append('email', email);

            fetch('<?= base_url('resend-otp') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Clear OTP inputs
                    otpInputs.forEach(input => input.value = '');
                    otpInputs[0].focus();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }

        // Focus on first input on page load
        window.addEventListener('load', () => {
            otpInputs[0].focus();
        });
    </script>

</body>
</html>
