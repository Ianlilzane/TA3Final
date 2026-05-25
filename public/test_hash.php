<?php
// Test password verification
$password = 'admin123';
$hash = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/tv.';

echo "Password: " . $password . "\n";
echo "Hash: " . $hash . "\n";
echo "Verification Result: " . (password_verify($password, $hash) ? "✓ PASS" : "✗ FAIL") . "\n";

// Also test with the other hash
$hash2 = '$2y$10$mC3Bv8w7iM1wH5mRj6m6Ou6rOfeK69MvNqXzFfG6uYkC2LpE0E5aG';
echo "\nTesting second hash...\n";
echo "Hash 2: " . $hash2 . "\n";
echo "Verification Result: " . (password_verify($password, $hash2) ? "✓ PASS" : "✗ FAIL") . "\n";
?>
