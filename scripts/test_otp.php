<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/Paths.php';

try {
    $s = new App\Libraries\OtpService();
    echo "OtpService instantiated\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
