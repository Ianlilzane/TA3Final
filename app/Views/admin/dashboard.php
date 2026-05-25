<?php
if (! function_exists('safeEsc')) {
    /**
     * Normalize arrays and mixed values into a safe escaped string.
     *
     * @param mixed $value
     * @return string
     */
    function safeEsc($value): string
    {
        $out = [];
        $iter = function($v) use (&$iter, &$out) {
            if (is_array($v)) {
                foreach ($v as $item) {
                    $iter($item);
                }
            } elseif ($v instanceof \Stringable) {
                $out[] = (string) $v;
            } else {
                $out[] = (string) $v;
            }
        };

        $iter($value);
        return htmlspecialchars(trim(implode(' ', array_filter($out, fn($s) => $s !== ''))), ENT_QUOTES, 'UTF-8');
    }
}

$pendingOrders = $pendingOrders ?? [];
$inventoryProducts = $inventoryProducts ?? [];
$allOrders = $allOrders ?? [];
$allUsers = $allUsers ?? [];
$bestSellers = $bestSellers ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - MotoParts Express</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', 'Segoe UI', sans-serif; }
        body { display: flex; background: #f5f5f7; min-height: 100vh; color: #333; }
        
        /* Sidebar Navigation */
        .sidebar { width: 260px; background: #185FA5; color: white; padding: 1.5rem; display: flex; flex-direction: column; gap: 2rem; position: fixed; height: 100vh; }
        .sidebar h2 { font-size: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .nav-links { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .nav-links a { color: #e6f1fb; text-decoration: none; padding: 12px; display: flex; align-items: center; gap: 10px; border-radius: 6px; font-size: 14px; transition: 0.2s; cursor: pointer; }
        .nav-links a.active, .nav-links a:hover { background: #0c447c; color: white; }
        
        /* Main Content Wrapper */
        .main-content { margin-left: 260px; flex: 1; padding: 2rem; display: flex; flex-direction: column; gap: 2rem; }
        .header h1 { font-size: 24px; font-weight: 600; color: #111; }
        .header p { font-size: 14px; color: #666; margin-top: 4px; }
        
        /* Tab Views Control */
        .tab-content { display: none; flex-direction: column; gap: 2rem; }
        .tab-content.active { display: flex; }

        /* Widgets Row */
        .widgets { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; }
        .widget-card { background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid #e5e5e5; display: flex; align-items: center; gap: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .widget-icon { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .widget-info h3 { font-size: 13px; color: #666; font-weight: 500; }
        .widget-info p { font-size: 22px; font-weight: 600; color: #111; margin-top: 2px; }
        
        /* Layout Grid */
        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }
        .panel { background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid #e5e5e5; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .panel h4 { font-size: 16px; color: #111; margin-bottom: 1.25rem; border-bottom: 1px solid #eee; padding-bottom: 10px; display: flex; align-items: center; gap: 8px; }
        
        /* Global Table System */
        .custom-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
        .custom-table th, .custom-table td { padding: 14px 12px; border-bottom: 1px solid #eee; }
        .custom-table th { background: #f8fafc; font-weight: 600; color: #475569; }
        
        /* Badges & Buttons */
        .badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 500; display: inline-block; }
        .badge-success { background: #e1f5ee; color: #0f6e56; }
        .badge-danger { background: #fee2e2; color: #dc2626; }
        .badge-warning { background: #fef3c7; color: #d97706; }
        
        .btn-approve { background: #0F6E56; color: white; border: none; padding: 8px 12px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: 0.2s; }
        .btn-approve:hover { background: #085041; }
        .btn-reject { background: #dc2626; color: white; border: none; padding: 8px 12px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: 0.2s; }
        .btn-reject:hover { background: #991b1b; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2><i class="ti ti-settings"></i> Admin Panel</h2>
        <ul class="nav-links">
            <li><a onclick="switchTab('dashboard', this)" class="active"><i class="ti ti-dashboard"></i> Dashboard Overview</a></li>
            <li><a onclick="switchTab('products', this)"><i class="ti ti-package"></i> Products & Inventory</a></li>
            <li><a onclick="switchTab('sales', this)"><i class="ti ti-report-money"></i> Financial Sales</a></li>
            <li><a onclick="switchTab('users', this)"><i class="ti ti-users"></i> User Management</a></li>
            <li><a href="<?= base_url('logout') ?>" style="margin-top: 4rem; color: #ffb3b3;"><i class="ti ti-logout"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        
        <div id="tab-dashboard" class="tab-content active">
            <div class="header">
                <h1>System Overview</h1>
                <p>Real-time shop status, order approvals, and live statistics tracking.</p>
            </div>

            <div class="widgets">
                <div class="widget-card">
                    <div class="widget-icon" style="background: #e1f5ee; color: #0f6e56;"><i class="ti ti-currency-peso"></i></div>
                    <div class="widget-info">
                        <h3>Total Revenue</h3>
                        <p>₱<?= number_format(isset($totalRevenue) ? $totalRevenue : 0, 2) ?></p>
                    </div>
                </div>
                <div class="widget-card">
                    <div class="widget-icon" style="background: #e6f1fb; color: #185FA5;"><i class="ti ti-shopping-cart"></i></div>
                    <div class="widget-info">
                        <h3>Active Pending Queue</h3>
                        <p><?= count($pendingOrders) ?> Orders</p>
                    </div>
                </div>
                <div class="widget-card">
                    <div class="widget-icon" style="background: #fee2e2; color: #dc2626;"><i class="ti ti-alert-triangle"></i></div>
                    <div class="widget-info">
                        <h3>Low Stock Alerts</h3>
                        <p><?= isset($lowStockCount) ? $lowStockCount : 0 ?> Items</p>
                    </div>
                </div>
            </div>

            <div class="panel">
                <h4><i class="ti ti-bell-ringing" style="color:#d97706;"></i> Incoming Orders For Verification</h4>
                <?php if(empty($pendingOrders)): ?>
                    <p style="text-align: center; color: #888; padding: 2rem;">No pending orders in the validation pipeline.</p>
                <?php else: ?>
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer Info</th>
                                <th>Shipping Address</th>
                                <th>Items Summary</th>
                                <th>Total Price</th>
                                <th style="text-align: center;">Action Controls</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pendingOrders as $order): ?>
                                <tr id="order-row-<?= $order['id'] ?>">
                                    <td><strong>#ORD-<?= $order['id'] ?></strong></td>
                                    <td><strong><?= safeEsc($order['fullname']) ?></strong><br><span style="color:#666; font-size:11px;"> <?= safeEsc($order['contact']) ?></span></td>
                                    <td><?= safeEsc($order['address']) ?></td>
                                    <td style="color:#0c447c; font-weight:500;"><?= safeEsc($order['items']) ?></td>
                                    <td><strong>₱<?= number_format($order['total_amount'], 2) ?></strong></td>
                                    <td style="text-align: center; white-space: nowrap;">
                                        <button class="btn-approve" onclick="executeAction(<?= $order['id'] ?>, 'approve')"><i class="ti ti-check"></i> Approve</button>
                                        <button class="btn-reject" onclick="executeAction(<?= $order['id'] ?>, 'reject')"><i class="ti ti-x"></i> Reject</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="dashboard-grid">
                <div class="panel">
                    <h4>Best Selling Motor Parts (Statistics)</h4>
                    <div style="height: 280px; position: relative;"><canvas id="adminSalesChart"></canvas></div>
                </div>
                <div class="panel">
                    <h4>Inventory Critical Alerts</h4>
                    <table class="custom-table">
                        <thead><tr><th>Item Unit</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach($inventoryProducts as $p): ?>
                                <?php if($p['stock'] <= 5): ?>
                                    <tr>
                                        <td><?= safeEsc($p['name']) ?></td>
                                        <td><span class="<?= $p['stock'] == 0 ? 'badge badge-danger' : 'badge badge-warning' ?>"><?= $p['stock'] ?> Left</span></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="tab-products" class="tab-content">
            <div class="header">
                <h1>Products & Live Inventory</h1>
                <p>Monitor real-time warehouse stock deductions (Baseline: 100 pcs per item).</p>
            </div>
            <div class="panel">
                <h4><i class="ti ti-box"></i> Stock Control Deck</h4>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Image</th>
                            <th>Item Name</th>
                            <th>Category Group</th>
                            <th>Unit Retail Price</th>
                            <th>Available Stock (Pcs)</th>
                            <th>Status Badge</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($inventoryProducts as $p): ?>
                        <tr>
                            <td>#PRD-<?= $p['id'] ?></td>
                            <td style="width: 92px;">
                                <?php if (!empty($p['image_url'])): ?>
                                    <img src="<?= esc($p['image_url']) ?>" alt="<?= safeEsc($p['name']) ?>" style="width: 72px; height: 54px; object-fit: cover; border-radius: 8px; border:1px solid #e5e7eb;" />
                                <?php else: ?>
                                    <div style="width: 72px; height: 54px; display:flex; align-items:center; justify-content:center; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; color:#64748b; font-size:12px;">No image</div>
                                <?php endif; ?>
                            </td>
                            <?php
                                $flattenName = function($value) {
                                    $out = [];
                                    $iter = function($v) use (&$iter, &$out) {
                                        if (is_array($v)) {
                                            foreach ($v as $item) {
                                                $iter($item);
                                            }
                                        } elseif ($v instanceof \Stringable) {
                                            $out[] = (string) $v;
                                        } else {
                                            $out[] = (string) $v;
                                        }
                                    };
                                    $iter($value);
                                    return trim(implode(' ', array_filter($out, fn($s) => $s !== '')));
                                };
                                $productName = isset($p['name']) ? $flattenName($p['name']) : '';
                            ?>
                            <td><strong><?= safeEsc($productName) ?></strong></td>
                            <td><?= safeEsc($p['category']) ?></td>
                            <td style="color:#185FA5; font-weight:600;">₱<?= number_format($p['price'], 2) ?></td>
                            <td><strong><?= $p['stock'] ?> pcs</strong></td>
                            <td>
                                <?php if($p['stock'] == 0): ?>
                                    <span class="badge badge-danger">Out of Stock</span>
                                <?php elseif($p['stock'] <= 10): ?>
                                    <span class="badge badge-warning">Low Stock Warning</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Fully Supplied</span>
                                <?php endif; ?>
                                <button type="button" onclick="promptImageUpdate(<?= $p['id'] ?>)" style="margin-top: 8px; background:#185FA5; color:white; border:none; border-radius:6px; padding:6px 10px; font-size:11px; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
                                    <i class="ti ti-photo"></i> Set Image
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-sales" class="tab-content">
            <div class="header">
                <h1>Financial Sales Ledger</h1>
                <p>Audited ledger records of all orders approved and signed off for final delivery dispatch.</p>
            </div>
            <div class="panel">
                <h4><i class="ti ti-report-money"></i> Historical Revenue Stream</h4>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer Account</th>
                            <th>Items Dispatched</th>
                            <th>Gross Total Transacted</th>
                            <th>Status Flag</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($allOrders as $o): ?>
                            <?php if($o['status'] === 'Order Approved / Out for Delivery'): ?>
                                <tr>
                                    <td><strong>#INV-<?= $o['id'] ?></strong></td>
                                    <td><?= safeEsc($o['fullname']) ?></td>
                                    <td><?= safeEsc($o['items']) ?></td>
                                    <td style="color:#0f6e56; font-weight:600;">₱<?= number_format($o['total_amount'], 2) ?></td>
                                    <td><span class="badge badge-success">Paid & Dispatched</span></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-users" class="tab-content">
            <div class="header">
                <h1>User Management Accounts</h1>
                <p>System authorized personnel profiles and client roles catalog registry.</p>
            </div>
            <div class="panel">
                <h4><i class="ti ti-users"></i> System Security Profiles</h4>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Registered Email</th>
                            <th>Account Clearance Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($allUsers as $u): ?>
                            <tr>
                                <td>#USR-<?= $u['id'] ?></td>
                                <td><strong><?= safeEsc($u['fullname']) ?></strong></td>
                                <td><?= safeEsc($u['email']) ?></td>
                                <td>
                                    <span class="badge" style="background:#e6f1fb; color:#185FA5; font-weight:600;">
                                        <?= $u['role_id'] == 1 ? '🥇 System Admin' : ($u['role_id'] == 3 ? '💼 Finance Officer' : '👤 Standard Customer') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        // 1. Dynamic Live Side-Panel Tab Navigator
        function switchTab(tabId, element) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.nav-links a').forEach(link => link.classList.remove('active'));
            
            document.getElementById('tab-' + tabId).classList.add('active');
            element.classList.add('active');
        }

        // 2. Real-time AJAX Approval Engine with Inventory Deduction Trigger
        function executeAction(orderId, actionType) {
            if(!confirm(`Proceed with order code #${orderId} process verification?`)) return;

            let formData = new window.FormData();
            formData.append('order_id', orderId);
            formData.append('action', actionType);

            fetch('<?= base_url("admin/dashboard/updateStatus") ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert(data.message);
                    window.location.reload(); // Live reload para ma-calculate agad ang bawas-benta sa mga graphs
                } else {
                    alert('Error processing command: ' + data.message);
                }
            })
            .catch(() => alert('Network processing failure.'));
        }

        function promptImageUpdate(productId) {
            const imageUrl = prompt('Enter a public image URL for this product:');
            if (!imageUrl) {
                return;
            }

            const validUrl = /^https?:\/\/.+$/i;
            if (!validUrl.test(imageUrl)) {
                alert('Please provide a valid image URL that begins with http:// or https://');
                return;
            }

            let formData = new window.FormData();
            formData.append('product_id', productId);
            formData.append('image_url', imageUrl);

            fetch('<?= base_url("admin/dashboard/updateImage") ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(() => alert('Unable to update image.'));
        }

        // 3. Analytics Chart Generation (DYNAMIC CONNECTED VERSION)
        const dynamicLabels = <?php echo json_encode(array_keys($bestSellers)); ?>;
        const dynamicData = <?php echo json_encode(array_values($bestSellers)); ?>;
        
        const ctx = document.getElementById('adminSalesChart').getContext('2d');
        new window.Chart(ctx, {
            type: 'bar',
            data: {
                labels: dynamicLabels.length ? dynamicLabels : ['Walang Benta'],
                datasets: [{
                    label: 'Units Sold Across Portal',
                    data: dynamicData.length ? dynamicData : [0],
                    backgroundColor: '#185FA5',
                    borderRadius: 6
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>