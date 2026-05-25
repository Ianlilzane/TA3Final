<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction History - Finance Hub</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: sans-serif; }
        body { display: flex; background: #f5f5f5; min-height: 100vh; }
        .sidebar { width: 260px; background: #0F6E56; color: white; padding: 1.5rem; display: flex; flex-direction: column; gap: 2rem; position: fixed; height: 100vh; }
        .sidebar h2 { font-size: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .nav-links { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .nav-links a { color: #e1f5ee; text-decoration: none; padding: 12px; display: flex; align-items: center; gap: 10px; border-radius: 6px; font-size: 14px; }
        .nav-links a.active, .nav-links a:hover { background: #085041; color: white; }
        .main-content { margin-left: 260px; flex: 1; padding: 2rem; display: flex; flex-direction: column; gap: 2rem; }
        .panel { background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid #e5e5e5; }
        .panel h4 { font-size: 16px; color: #111; margin-bottom: 1.25rem; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .finance-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
        .finance-table th, .finance-table td { padding: 12px 10px; border-bottom: 1px solid #eee; }
        .badge-success { background: #e1f5ee; color: #0f6e56; padding: 4px 8px; border-radius: 6px; font-weight: 500; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2><i class="ti ti-wallet"></i> Finance Hub</h2>
        <ul class="nav-links">
            <li><a href="<?= base_url('finance/dashboard') ?>"><i class="ti ti-chart-pie"></i> Sales Tracking</a></li>
            <li><a href="<?= base_url('finance/transactions') ?>" class="active"><i class="ti ti-receipt"></i> Transaction History</a></li>
            <li><a href="<?= base_url('logout') ?>" style="margin-top: 4rem; color: #ffd6d6;"><i class="ti ti-logout"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="panel">
            <h4>Archived Settlement History</h4>
            <table class="finance-table">
                <thead>
                    <tr>
                        <th>Date Processed</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Verification</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($paidOrders)): ?>
                        <?php foreach($paidOrders as $order): ?>
                            <tr>
                                <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                                <td><strong>#ORD-<?= $order['id'] ?></strong></td>
                                <td><?= esc($order['fullname']) ?></td>
                                <td><strong style="color: #0F6E56;">₱<?= number_format($order['total_amount'], 2) ?></strong></td>
                                <td><span class="badge-success">Cleared</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; color: #999; padding: 2rem;">No transactions found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>