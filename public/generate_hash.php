<?php
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Plaintext Password: " . $password . "\n";
echo "Bcrypt Hash: " . $hash . "\n";
echo "\nVerification Test: " . (password_verify($password, $hash) ? "✓ PASS" : "✗ FAIL") . "\n";
?>
