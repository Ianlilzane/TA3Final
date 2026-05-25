<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ordering System - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f5f5f5; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; }
        .hero { text-align: center; margin-bottom: 2rem; }
        .hero h1 { font-size: 28px; font-weight: 500; color: #111; }
        .hero p { font-size: 15px; color: #666; margin-top: 0.5rem; }
        
        /* Login Container Style */
        .login-card { background: #fff; border: 2px solid #185FA5; border-radius: 12px; padding: 2rem; width: 100%; max-width: 400px; display: flex; flex-direction: column; gap: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        
        /* Form groups */
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13px; font-weight: 500; color: #444; }
        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-wrapper i { position: absolute; left: 12px; color: #888; font-size: 18px; }
        .input-wrapper input { width: 100%; padding: 10px 12px 10px 38px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; outline: none; transition: border-color 0.2s; }
        .input-wrapper input:focus { border-color: #185FA5; }
        
        /* Alert Message */
        .alert-error { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 8px; font-size: 13px; text-align: center; border: 1px solid #fca5a5; }

        /* Buttons & Links */
        .btn { display: block; text-align: center; padding: 11px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; text-decoration: none; border: none; width: 100%; }
        .btn-blue { background: #185FA5; color: #fff; }
        .btn-blue:hover { background: #0C447C; }
        
        .divider { text-align: center; font-size: 13px; color: #666; margin-top: 0.5rem; }
        .divider a { color: #185FA5; font-weight: 500; text-decoration: none; }
        .divider a:hover { text-decoration: underline; }
        
        .footer { margin-top: 2rem; font-size: 12px; color: #aaa; }
    </style>
</head>
<body>

    <div class="hero">
        <h1>Welcome to the Ordering System</h1>
        <p>Please log in to your account to continue.</p>
    </div>

    <div class="login-card">
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert-error">
                <?= implode(' ', (array)session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('login') ?>" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
            <?= csrf_field() ?> <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <i class="ti ti-mail"></i>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <i class="ti ti-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-blue">
                Log in <i class="ti ti-arrow-right" style="font-size: 14px; margin-left: 4px; vertical-align: middle;"></i>
            </button>
        </form>

        <div class="divider">
            New customer? <a href="<?= base_url('register') ?>">Register & order now</a>
        </div>
    </div>

    <div class="footer">Ordering System &copy; <?= date('Y') ?></div>

</body>
</html>