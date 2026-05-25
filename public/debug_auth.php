<?php
require 'vendor/autoload.php';

$db = \Config\Database::connect();

echo "=== Checking Database Connection ===\n";
echo "Database connected: ✓\n\n";

echo "=== Checking Users Table ===\n";
$users = $db->query("SELECT id, fullname, email, role_id, password FROM users")->getResultArray();

if (empty($users)) {
    echo "⚠️ No users found in database!\n";
} else {
    echo "Found " . count($users) . " user(s):\n\n";
    foreach ($users as $user) {
        echo "ID: " . $user['id'] . "\n";
        echo "Name: " . $user['fullname'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Role ID: " . $user['role_id'] . "\n";
        echo "Password Hash: " . substr($user['password'], 0, 30) . "...\n";
        
        $test = password_verify('admin123', $user['password']);
        echo "Verify admin123: " . ($test ? "✓ PASS" : "✗ FAIL") . "\n";
        echo "---\n";
    }
}

echo "\n=== Testing Login for admin@gmail.com ===\n";
$admin = $db->query("SELECT * FROM users WHERE email = 'admin@gmail.com'")->getRowArray();

if (!$admin) {
    echo "⚠️ Account admin@gmail.com NOT FOUND in database\n";
} else {
    echo "✓ Account found!\n";
    echo "Email: " . $admin['email'] . "\n";
    echo "Role ID: " . $admin['role_id'] . "\n";
    echo "Password hash: " . $admin['password'] . "\n";
    $verify = password_verify('admin123', $admin['password']);
    echo "Password verify: " . ($verify ? "✓ PASS" : "✗ FAIL") . "\n";
}
?>
