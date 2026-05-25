<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Dashboard - Ordering System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', 'Segoe UI', sans-serif; }
        body { display: flex; background: #f5f5f5; min-height: 100vh; }
        
        .sidebar { width: 260px; background: #0F6E56; color: white; padding: 1.5rem; display: flex; flex-direction: column; gap: 2rem; position: fixed; height: 100vh; }
        .sidebar h2 { font-size: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .nav-links { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .nav-links a { color: #e1f5ee; text-decoration: none; padding: 12px; display: flex; align-items: center; gap: 10px; border-radius: 6px; font-size: 14px; }
        .nav-links a.active, .nav-links a:hover { background: #085041; color: white; }
        
        .main-content { margin-left: 260px; flex: 1; padding: 2rem; display: flex; flex-direction: column; gap: 2rem; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 24px; font-weight: 500; color: #111; }
        
        .btn-export { background: #0F6E56; color: white; border: none; padding: 10px 16px; border-radius: 6px; font-size: 13px; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 6px; }
        .btn-export:hover { background: #085041; }

        .widgets { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
        .widget-card { background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid #e5e5e5; display: flex; align-items: center; gap: 1rem; }
        .widget-icon { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; background: #e1f5ee; color: #0F6E56; }
        .widget-info h3 { font-size: 13px; color: #666; font-weight: 500; }
        .widget-info p { font-size: 22px; font-weight: 600; color: #111; }

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
            <li>
                <a href="<?= base_url('finance/dashboard') ?>" class="active">
                    <i class="ti ti-chart-pie"></i> Sales Tracking
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('finance/transactions') ?>">
                    <i class="ti ti-receipt"></i> Transaction History
                </a>
            </li>
            
            <li>
                <a href="<?= base_url('logout') ?>" style="margin-top: 4rem; color: #ffd6d6;">
                    <i class="ti ti-logout"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <div>
                <h1>Financial Dashboard</h1>
                <p style="color: #666; font-size: 14px; margin-top: 4px;">Welcome back, <?= esc($fullname ?? 'Finance Officer') ?>! Monitor payments and real-time revenue streams.</p>
            </div>
            <button class="btn-export" onclick="exportTableToExcel('finance-ledger-table', 'Realtime_Sales_Report')">
                <i class="ti ti-download"></i> Export Excel Report
            </button>
        </div>

        <div class="widgets">
            <div class="widget-card">
                <div class="widget-icon"><i class="ti ti-report-money"></i></div>
                <div class="widget-info">
                    <h3>Gross Revenue (Approved Orders)</h3>
                    <p>₱<?= number_format($totalRevenue ?? 0, 2) ?></p>
                </div>
            </div>
            <div class="widget-card">
                <div class="widget-icon"><i class="ti ti-cash"></i></div>
                <div class="widget-info">
                    <h3>Total Received Payments</h3>
                    <p><?= $totalOrdersCount ?? 0 ?> Transactions</p>
                </div>
            </div>
        </div>

        <div class="panel">
            <h4>Recent Paid Orders Ledger</h4>
            <table class="finance-table" id="finance-ledger-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Items Summary</th>
                        <th>Amount Paid</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($paidOrders)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #888; padding: 2rem;">No settled transactions found in the database.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($paidOrders as $order): ?>
                            <tr>
                                <td><strong>#ORD-<?= $order['id'] ?></strong></td>
                                <td><?= esc($order['fullname']) ?></td>
                                <td style="color: #555;"><?= esc($order['items']) ?></td>
                                <td><strong style="color: #0F6E56;">₱<?= number_format($order['total_amount'], 2) ?></strong></td>
                                <td><span class="badge-success">Paid & Dispatched</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function exportTableToExcel(tableID, filename = ''){
        var downloadLink;
        var dataType = 'application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);
        var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
        
        filename = filename ? filename + '.xls' : 'excel_data.xls';
        downloadLink = document.createElement("a");
        document.body.appendChild(downloadLink);
        
        if(navigator.msSaveOrOpenBlob){
            var blob = new Blob(['\ufeff' + tableHTML], {
                type: dataType
            });
            navigator.msSaveOrOpenBlob(blob, filename);
        } else {
            downloadLink.href = 'data:' + dataType + ', ' + '\ufeff' + tableHTML;
            downloadLink.download = filename;
            downloadLink.click();
        }
    }
    </script>

</body>
</html>