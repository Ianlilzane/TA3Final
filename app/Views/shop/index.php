<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MotoParts Express - Customer Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', 'Segoe UI', sans-serif; }
        body { background: #f5f5f5; min-height: 100vh; padding-bottom: 3rem; }
        
        /* Navbar Layout */
        .navbar { background: white; padding: 1rem 2rem; border-bottom: 1px solid #e5e5e5; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
        .navbar .brand { font-size: 20px; font-weight: 600; color: #185FA5; display: flex; align-items: center; gap: 8px; }
        .nav-actions { display: flex; align-items: center; gap: 1.5rem; }
        .nav-actions a, .nav-link { text-decoration: none; color: #333; font-size: 14px; display: flex; align-items: center; gap: 6px; cursor: pointer; background: none; border: none; }
        .cart-icon { position: relative; font-size: 20px; color: #185FA5; cursor: pointer; }
        .cart-count { position: absolute; top: -6px; right: -8px; background: red; color: white; font-size: 10px; font-weight: bold; padding: 2px 5px; border-radius: 50%; }

        .container { max-width: 1100px; margin: 2rem auto; padding: 0 1rem; display: flex; flex-direction: column; gap: 2rem; }
        
        /* Weather Widget */
        .weather-card { background: #e0f2fe; border: 1px solid #7dd3fc; padding: 1rem; border-radius: 12px; display: flex; align-items: center; gap: 1rem; color: #0369a1; font-size: 14px; }
        .weather-card i { font-size: 24px; }

        /* Category System */
        .categories { display: flex; gap: 10px; list-style: none; overflow-x: auto; padding-bottom: 5px; }
        .cat-btn { background: white; border: 1px solid #ccc; padding: 8px 16px; border-radius: 20px; font-size: 13px; cursor: pointer; font-weight: 500; transition: 0.2s; white-space: nowrap; }
        .cat-btn.active, .cat-btn:hover { background: #185FA5; color: white; border-color: #185FA5; }

        /* Dynamic Grid Layout changes when Cart is active */
        .shop-layout { display: grid; grid-template-columns: 1fr; gap: 1.5rem; transition: 0.3s ease; }
        .shop-layout.with-cart { grid-template-columns: 2.3fr 1fr; }

        /* Product Cards */
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 1.5rem; }
        .product-card { background: white; border: 1px solid #e5e5e5; border-radius: 12px; padding: 1.25rem; display: flex; flex-direction: column; gap: 10px; justify-content: space-between; transition: 0.2s; }
        .product-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .product-img-holder { width: 100%; height: 140px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #aaa; font-size: 32px; }
        .product-title { font-size: 15px; font-weight: 600; color: #111; }
        .product-price { font-size: 16px; font-weight: 600; color: #185FA5; }
        
        .btn-order { background: #185FA5; color: white; border: none; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; }
        .btn-order:hover { background: #0c447c; }

        /* Shopping Summary Box */
        .cart-panel { background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid #e5e5e5; height: fit-content; position: sticky; top: 90px; display: none; animation: fadeIn 0.3s ease; }
        .cart-panel h3 { font-size: 16px; margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 8px; }
        .cart-item { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px dashed #eee; }
        .cart-total { display: flex; justify-content: space-between; font-weight: bold; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #ddd; font-size: 15px; }
        .btn-checkout { background: #0F6E56; color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; font-weight: bold; margin-top: 1rem; cursor: pointer; font-size: 13px; }
        .btn-checkout:hover { background: #085041; }

        /* Modals */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: none; justify-content: center; align-items: center; z-index: 1000; padding: 1rem; }
        .modal-card { background: white; border-radius: 12px; width: 100%; max-width: 500px; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1); display: flex; flex-direction: column; gap: 1.25rem; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .modal-header h3 { font-size: 18px; color: #111; }
        .close-modal-btn { background: none; border: none; font-size: 20px; cursor: pointer; color: #888; }

        /* Form Inputs */
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13px; font-weight: 500; color: #444; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; outline: none; }

        /* Order Tracking Table Layout */
        .tracking-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        .tracking-table th, .tracking-table td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
        .tracking-table th { background: #f8fafc; font-weight: 600; }

        @keyframes fadeIn { from { opacity: 0; transform: scale(0.98); } to { opacity: 1; transform: scale(1); } }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="brand"><i class="ti ti-motorbike"></i> MotoParts Express</div>
        <div class="nav-actions">
            
            <?php if (!empty($myOrders)): ?>
                <button class="nav-link" onclick="openTrackingModal()" style="color: #185FA5; font-weight: 600;">
                    <i class="ti ti-history"></i> My Orders
                    <span class="cart-count" style="position:relative; top:0; right:0; background:#185FA5; margin-left:2px;">
                        <?= count($myOrders) ?>
                    </span>
                </button>
            <?php endif; ?>

            <div class="cart-icon" onclick="toggleCartDisplay()">
                <i class="ti ti-shopping-cart"></i>
                <span class="cart-count" id="global-cart-count">0</span>
            </div>
            <a href="<?= base_url('logout') ?>" style="color: #666; margin-left: 10px;"><i class="ti ti-logout"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        
        <div class="weather-card" id="weather-widget">
            <i class="ti ti-cloud-sun" id="weather-icon"></i>
            <div>
                <strong>Delivery Status Checking:</strong> 
                <span id="weather-text">Checking atmospheric data forecast...</span>
            </div>
        </div>

        <div>
            <h2>Available Motorcycle Parts</h2>
            <p style="color: #666; font-size: 14px; margin-top: 4px;">Explore best-selling premium items for your daily motorcycle upgrades.</p>
        </div>

        <ul class="categories">
            <li><button class="cat-btn active" onclick="filterCategory('all', this)">All Items</button></li>
            <li><button class="cat-btn" onclick="filterCategory('Engine Parts', this)">Engine Parts</button></li>
            <li><button class="cat-btn" onclick="filterCategory('Brake Set', this)">Brake Set</button></li>
            <li><button class="cat-btn" onclick="filterCategory('Tire and Wheels', this)">Tire and Wheels</button></li>
            <li><button class="cat-btn" onclick="filterCategory('Accessories', this)">Accessories</button></li>
        </ul>

        <div class="shop-layout" id="main-shop-wrapper">
            
            <div class="product-grid" id="product-catalog">
                <?php if(!empty($products)): ?>
                    <?php foreach($products as $p): ?>
                        <div class="product-card" data-category="<?= esc($p['category']) ?>">
                            <div>
                                <div class="product-img-holder" style="background: #e0f2fe; color: #0284c7;">
                                <?php if (!empty($p['image_url'])): ?>
                                    <img src="<?= esc($p['image_url']) ?>" alt="<?= esc($p['name']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px;" />
                                <?php elseif($p['category'] === 'Engine Parts'): ?>
                                    <i class="ti ti-bolt"></i>
                                <?php elseif($p['category'] === 'Brake Set'): ?>
                                    <i class="ti ti-disc"></i>
                                <?php elseif($p['category'] === 'Tire and Wheels'): ?>
                                    <i class="ti ti-circle-dot"></i>
                                <?php else: ?>
                                    <i class="ti ti-settings"></i>
                                <?php endif; ?>
                            </div>
                                <h3 class="product-title" style="margin-top: 10px;"><?= esc($p['name']) ?></h3>
                                <p style="font-size: 11px; margin-top: 2px; font-weight: bold; color: <?= $p['stock'] <= 5 ? '#dc2626' : '#16a34a' ?>;">
                                    Stock: <?= $p['stock'] ?> pcs left
                                </p>
                            </div>
                            <div>
                                <div class="product-price">₱<?= number_format($p['price'], 2) ?></div>
                                <button class="btn-order" onclick="addToCart('<?= esc($p['name']) ?>', <?= $p['price'] ?>)"><i class="ti ti-plus"></i> Add to Cart</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center; color: #888; padding: 2rem;">No products available at the moment.</p>
                <?php endif; ?>
            </div>

            <div class="cart-panel" id="right-cart-panel">
                <h3><i class="ti ti-shopping-cart-discount"></i> Shopping Summary</h3>
                <div id="cart-items-list"></div>
                <div class="cart-total">
                    <span>Total Amount:</span>
                    <span id="cart-total-price">₱0.00</span>
                </div>
                <button class="btn-checkout" onclick="openCheckoutModal()">
                    Proceed to Checkout Order
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="checkout-modal">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="ti ti-list-check" style="color:#0F6E56;"></i> Shipping Information</h3>
                <button class="close-modal-btn" onclick="closeCheckoutModal()">&times;</button>
            </div>
            <form id="checkout-form-fields" onsubmit="submitOrderToDatabase(event)">
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div class="form-group">
                        <label>Recipient Full Name *</label>
                        <input type="text" id="cust-fullname" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Number *</label>
                        <input type="tel" id="cust-contact" placeholder="e.g., 09123456789" required>
                    </div>
                    <div class="form-group">
                        <label>Complete Delivery Address *</label>
                        <textarea id="cust-address" rows="3" placeholder="House/Unit No, Street, Barangay, City, Province" required></textarea>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top:1rem;">
                    <button type="button" class="btn-checkout" style="background: #9ca3af;" onclick="closeCheckoutModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn-checkout" style="margin-top:0;">
                        Confirm Order & Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="tracking-modal">
        <div class="modal-card" style="max-width: 650px;">
            <div class="modal-header">
                <h3><i class="ti ti-truck-delivery" style="color:#185FA5;"></i> My Purchase Records</h3>
                <button class="close-modal-btn" onclick="closeTrackingModal()">&times;</button>
            </div>
            <div style="max-height: 350px; overflow-y: auto;">
                <?php if (!empty($myOrders)): ?>
                    <table class="tracking-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Items Bought</th>
                                <th>Total</th>
                                <th>Status / Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myOrders as $order): ?>
                                <tr>
                                    <td><strong>#ORD-<?= $order['id'] ?></strong></td>
                                    <td style="font-size:12px; color:#555;"><?= esc($order['items']) ?></td>
                                    <td style="color:#185FA5; font-weight:600;">₱<?= number_format($order['total_amount'], 2) ?></td>
                                    <td>
                                        <?php if($order['status'] === 'Pending Approval'): ?>
                                        <div style="display: flex; flex-direction: column; gap: 8px; align-items: flex-start;">
                                            <span style="background: #fef3c7; color: #d97706; padding: 6px 10px; border-radius: 6px; font-size: 11px; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="ti ti-clock"></i> Pending Approval
                                            </span>
                                            <button type="button" onclick="cancelCustomerOrder(<?= $order['id'] ?>)" style="background: #dc2626; color: white; border: none; padding: 6px 10px; border-radius: 6px; font-size: 11px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="ti ti-x"></i> Cancel Order
                                            </button>
                                        </div>
                                    <?php elseif($order['status'] === 'Order Approved / Out for Delivery'): ?>
                                        <div style="display: flex; flex-direction: column; gap: 5px;">
                                            <span style="background: #e6f1fb; color: #185FA5; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; width: fit-content; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="ti ti-truck"></i> Delivering...
                                            </span>
                                            <button onclick="customerReceivedOrder(<?= $order['id'] ?>)" style="background: #16a34a; color: white; border: none; padding: 6px 10px; border-radius: 6px; font-size: 11px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 2px; justify-content: center;">
                                                Confirm Received ✔️
                                            </button>
                                        </div>
                                    <?php elseif($order['status'] === 'Delivered & Completed'): ?>
                                        <span style="background: #e1f5ee; color: #0f6e56; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="ti ti-circle-check"></i> Delivered & Completed
                                        </span>
                                    <?php else: ?>
                                        <span style="background: #f8d7da; color: #842029; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="ti ti-alert-circle"></i> <?= esc($order['status']) ?>
                                        </span>
                                    <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="padding: 15px; text-align: center; color: #888;">No active purchase history records.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        let currentBasket = [];
        let finalOverallTotal = 0;

        function openCheckoutModal() { document.getElementById('checkout-modal').style.display = 'flex'; }
        function closeCheckoutModal() { document.getElementById('checkout-modal').style.display = 'none'; }
        function openTrackingModal() { document.getElementById('tracking-modal').style.display = 'flex'; }
        function closeTrackingModal() { document.getElementById('tracking-modal').style.display = 'none'; }
        
        function toggleCartDisplay() {
            const panel = document.getElementById('right-cart-panel');
            const mainWrapper = document.getElementById('main-shop-wrapper');
            if (currentBasket.length > 0) {
                if (panel.style.display === 'block' || panel.style.display === '') {
                    panel.style.display = 'none';
                    mainWrapper.classList.remove("with-cart");
                } else {
                    panel.style.display = 'block';
                    mainWrapper.classList.add("with-cart");
                }
            }
        }

        function addToCart(name, price) {
            const existingItem = currentBasket.find(item => item.name === name);
            if (existingItem) { 
                existingItem.qty++; 
            } else { 
                currentBasket.push({ name: name, price: price, qty: 1 }); 
            }
            renderBasketUI();
        }

        function renderBasketUI() {
            const listContainer = document.getElementById('cart-items-list');
            const totalLabel = document.getElementById('cart-total-price');
            const counterBadge = document.getElementById('global-cart-count');
            
            const mainWrapper = document.getElementById('main-shop-wrapper');
            const cartPanel = document.getElementById('right-cart-panel');
            
            if (currentBasket.length === 0) {
                cartPanel.style.display = "none";
                mainWrapper.classList.remove("with-cart");
                counterBadge.innerText = "0";
                return;
            }

            cartPanel.style.display = "block";
            mainWrapper.classList.add("with-cart");

            let htmlString = "";
            finalOverallTotal = 0;
            let totalItemsCount = 0;

            currentBasket.forEach(item => {
                let currentSubtotal = item.price * item.qty;
                finalOverallTotal += currentSubtotal;
                totalItemsCount += item.qty;
                htmlString += `
                    <div class="cart-item">
                        <div>
                            <strong>${item.name}</strong><br>
                            <span style="color:#666;">₱${item.price.toLocaleString(undefined, {minimumFractionDigits: 2})} x ${item.qty}</span>
                        </div>
                        <span style="font-weight:600; color:#185FA5;">₱${currentSubtotal.toFixed(2)}</span>
                    </div>
                `;
            });

            listContainer.innerHTML = htmlString;
            totalLabel.innerText = `₱${finalOverallTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
            counterBadge.innerText = totalItemsCount;
        }

        function submitOrderToDatabase(event) {
            event.preventDefault();

            if (currentBasket.length === 0) {
                alert('Your shopping cart summary is empty.');
                return;
            }

            let textItemsSummary = currentBasket.map(i => `${i.name} (x${i.qty})`).join(', ');

            let formData = new FormData();
            formData.append('fullname', document.getElementById('cust-fullname').value);
            formData.append('contact', document.getElementById('cust-contact').value);
            formData.append('address', document.getElementById('cust-address').value);
            formData.append('items', textItemsSummary);
            formData.append('total_amount', finalOverallTotal);

            fetch('<?= base_url("shop/placeOrder") ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert('🎉 Order Saved! Your purchase records have been securely written to the database.');
                    window.location.reload(); 
                } else {
                    alert('⚠️ Error: ' + data.message);
                }
            })
            .catch(err => alert('Network system communication failure.'));
        }

        function customerReceivedOrder(orderId) {
            if(!confirm("Has your parcel been successfully delivered by the rider?")) return;

            let formData = new FormData();
            formData.append('order_id', orderId);

            fetch('<?= base_url("shop/confirmDelivery") ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert(data.message);
                    window.location.reload(); 
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => alert('Network system communication failure.'));
        }

        function cancelCustomerOrder(orderId) {
            if(!confirm("Do you want to cancel this pending order?")) return;

            let formData = new FormData();
            formData.append('order_id', orderId);

            fetch('<?= base_url("shop/cancelOrder") ?>', {
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
            .catch(err => alert('Network system communication failure.'));
        }

        function filterCategory(categorySelected, element) {
            document.querySelectorAll('.cat-btn').forEach(btn => btn.classList.remove('active'));
            element.classList.add('active');
            document.querySelectorAll('.product-card').forEach(card => {
                if (categorySelected === 'all' || card.getAttribute('data-category') === categorySelected) {
                    card.style.display = 'flex';
                } else { card.style.display = 'none'; }
            });
        }

        async function fetchWeatherStatus() {
            const textDisplay = document.getElementById('weather-text');
            const iconDisplay = document.getElementById('weather-icon');
            try {
                const res = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=14.5995&longitude=120.9842&current_weather=true`);
                const data = await res.json();
                const temp = data.current_weather.temperature;
                
                // Dynamic weather parsing logic
                if(temp > 33) {
                    iconDisplay.className = "ti ti-sun";
                } else if(temp < 26) {
                    iconDisplay.className = "ti ti-cloud-rain";
                } else {
                    iconDisplay.className = "ti ti-cloud-sun";
                }
                
                textDisplay.innerHTML = `Current Climate Forecast: <strong>${temp}°C</strong>. Motorcycle logistical pathways are running clear.`;
            } catch (e) {
                textDisplay.innerHTML = `Current Climate: <strong>31°C Standard Dry Track</strong>. Dispatch pathways fully functional.`;
            }
        }
        window.onload = fetchWeatherStatus;
    </script>
</body>
</html>