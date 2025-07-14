<?php

$files = [
    'src/WhatsAppApiClient.php',
    'src/WebhookHandler.php'
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Fix return array annotations
    $content = preg_replace('/(@return\s+)array(\s*$)/m', '$1array<string, mixed>$2', $content);
    
    // Fix parameter array annotations
    $content = preg_replace('/(@param\s+)array(\s+\$[a-zA-Z_]+)/m', '$1array<string, mixed>$2', $content);
    
    // Fix special case for createGroup chatIds parameter
    $content = preg_replace('/(@param\s+)array<string, mixed>(\s+\$chatIds\s+Array)/m', '$1array<string>$2', $content);
    
    file_put_contents($file, $content);
    echo "Fixed $file\n";
}

echo "All type annotations fixed!\n";
