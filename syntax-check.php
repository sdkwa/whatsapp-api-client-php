<?php

// Simple syntax check script
echo "Checking syntax...\n";

$files = [
    'src/WhatsAppApiClient.php',
    'src/WebhookHandler.php',
    'src/Exceptions/WhatsAppApiException.php'
];

foreach ($files as $file) {
    $result = shell_exec("php -l $file 2>&1");
    if (strpos($result, 'No syntax errors') !== false) {
        echo "✓ $file: OK\n";
    } else {
        echo "✗ $file: $result\n";
    }
}

echo "Syntax check complete!\n";
