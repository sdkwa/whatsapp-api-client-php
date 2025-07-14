#!/usr/bin/env php
<?php

/**
 * Setup Script for WhatsApp API Client PHP
 * 
 * This script helps you set up the WhatsApp API Client PHP library
 * and configure your environment.
 */

echo "🚀 WhatsApp API Client PHP Setup\n";
echo "=====================================\n\n";

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    echo "❌ PHP 7.4 or higher is required. Current version: " . PHP_VERSION . "\n";
    exit(1);
}

echo "✅ PHP version: " . PHP_VERSION . "\n";

// Check required extensions
$requiredExtensions = ['curl', 'json'];
$missingExtensions = [];

foreach ($requiredExtensions as $extension) {
    if (!extension_loaded($extension)) {
        $missingExtensions[] = $extension;
    }
}

if (!empty($missingExtensions)) {
    echo "❌ Missing required extensions: " . implode(', ', $missingExtensions) . "\n";
    exit(1);
}

echo "✅ Required extensions are installed\n";

// Check if Composer is installed
if (!file_exists('vendor/autoload.php')) {
    echo "❌ Composer dependencies not installed. Please run: composer install\n";
    exit(1);
}

echo "✅ Composer dependencies are installed\n\n";

// Create .env file if it doesn't exist
if (!file_exists('.env')) {
    if (file_exists('.env.example')) {
        copy('.env.example', '.env');
        echo "✅ Created .env file from .env.example\n";
    } else {
        $envContent = "# SDKWA WhatsApp API Configuration\n";
        $envContent .= "API_HOST=https://api.sdkwa.pro\n";
        $envContent .= "ID_INSTANCE=YOUR_INSTANCE_ID\n";
        $envContent .= "API_TOKEN_INSTANCE=YOUR_API_TOKEN_INSTANCE\n";
        $envContent .= "USER_ID=YOUR_USER_ID\n";
        $envContent .= "USER_TOKEN=YOUR_USER_TOKEN\n";
        
        file_put_contents('.env', $envContent);
        echo "✅ Created .env file\n";
    }
} else {
    echo "✅ .env file already exists\n";
}

// Interactive configuration
echo "\n📝 Configuration Setup\n";
echo "======================\n\n";

echo "Would you like to configure your API credentials now? (y/n): ";
$handle = fopen("php://stdin", "r");
$configure = trim(fgets($handle));

if (strtolower($configure) === 'y' || strtolower($configure) === 'yes') {
    
    // Load existing .env
    $envVars = [];
    if (file_exists('.env')) {
        $envContent = file_get_contents('.env');
        $lines = explode("\n", $envContent);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                [$key, $value] = explode('=', $line, 2);
                $envVars[trim($key)] = trim($value);
            }
        }
    }

    // Get API Host
    $currentHost = $envVars['API_HOST'] ?? 'https://api.sdkwa.pro';
    echo "API Host [{$currentHost}]: ";
    $apiHost = trim(fgets($handle));
    if (empty($apiHost)) {
        $apiHost = $currentHost;
    }

    // Get Instance ID
    $currentInstance = $envVars['ID_INSTANCE'] ?? 'YOUR_INSTANCE_ID';
    echo "Instance ID [{$currentInstance}]: ";
    $instanceId = trim(fgets($handle));
    if (empty($instanceId)) {
        $instanceId = $currentInstance;
    }

    // Get API Token
    $currentToken = $envVars['API_TOKEN_INSTANCE'] ?? 'YOUR_API_TOKEN_INSTANCE';
    echo "API Token [{$currentToken}]: ";
    $apiToken = trim(fgets($handle));
    if (empty($apiToken)) {
        $apiToken = $currentToken;
    }

    // Get User ID (optional)
    $currentUserId = $envVars['USER_ID'] ?? 'YOUR_USER_ID';
    echo "User ID (optional, for instance management) [{$currentUserId}]: ";
    $userId = trim(fgets($handle));
    if (empty($userId)) {
        $userId = $currentUserId;
    }

    // Get User Token (optional)
    $currentUserToken = $envVars['USER_TOKEN'] ?? 'YOUR_USER_TOKEN';
    echo "User Token (optional, for instance management) [{$currentUserToken}]: ";
    $userToken = trim(fgets($handle));
    if (empty($userToken)) {
        $userToken = $currentUserToken;
    }

    // Update .env file
    $newEnvContent = "# SDKWA WhatsApp API Configuration\n";
    $newEnvContent .= "API_HOST={$apiHost}\n";
    $newEnvContent .= "ID_INSTANCE={$instanceId}\n";
    $newEnvContent .= "API_TOKEN_INSTANCE={$apiToken}\n";
    $newEnvContent .= "USER_ID={$userId}\n";
    $newEnvContent .= "USER_TOKEN={$userToken}\n";
    $newEnvContent .= "\n# Test Configuration\n";
    $newEnvContent .= "TEST_CHAT_ID=79999999999@c.us\n";
    $newEnvContent .= "TEST_GROUP_ID=GROUP_ID@g.us\n";
    $newEnvContent .= "TEST_PHONE_NUMBER=79999999999\n";

    file_put_contents('.env', $newEnvContent);
    echo "\n✅ Configuration saved to .env file\n";
}

fclose($handle);

// Test connection
echo "\n🔍 Testing Connection\n";
echo "====================\n\n";

require_once 'vendor/autoload.php';

use SDKWA\WhatsAppApiClient;
use SDKWA\Exceptions\WhatsAppApiException;

// Load environment variables
$envVars = [];
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    $lines = explode("\n", $envContent);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $envVars[trim($key)] = trim($value);
        }
    }
}

$config = [
    'apiHost' => $envVars['API_HOST'] ?? 'https://api.sdkwa.pro',
    'idInstance' => $envVars['ID_INSTANCE'] ?? 'YOUR_INSTANCE_ID',
    'apiTokenInstance' => $envVars['API_TOKEN_INSTANCE'] ?? 'YOUR_API_TOKEN_INSTANCE'
];

if (isset($envVars['USER_ID']) && $envVars['USER_ID'] !== 'YOUR_USER_ID') {
    $config['userId'] = $envVars['USER_ID'];
}

if (isset($envVars['USER_TOKEN']) && $envVars['USER_TOKEN'] !== 'YOUR_USER_TOKEN') {
    $config['userToken'] = $envVars['USER_TOKEN'];
}

try {
    $client = new WhatsAppApiClient($config);
    echo "✅ Client initialized successfully\n";
    
    // Test API connection
    if ($config['idInstance'] !== 'YOUR_INSTANCE_ID' && $config['apiTokenInstance'] !== 'YOUR_API_TOKEN_INSTANCE') {
        echo "🔄 Testing API connection...\n";
        
        $state = $client->getStateInstance();
        echo "✅ API connection successful\n";
        echo "📊 Instance state: " . $state['stateInstance'] . "\n";
        
        if ($state['stateInstance'] === 'authorized') {
            echo "🟢 Instance is authorized and ready to use!\n";
        } else {
            echo "🟡 Instance needs authorization. Use the QR code example to authorize.\n";
        }
    } else {
        echo "⚠️ Please configure your API credentials in .env file to test the connection\n";
    }
    
} catch (WhatsAppApiException $e) {
    echo "❌ API Error: " . $e->getMessage() . "\n";
    if ($e->getStatusCode() === 401) {
        echo "💡 This usually means your API token is invalid or expired.\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Show next steps
echo "\n🎉 Setup Complete!\n";
echo "==================\n\n";

echo "Next steps:\n";
echo "1. Configure your API credentials in .env file if you haven't already\n";
echo "2. Run the examples to test your setup:\n";
echo "   php examples/qr_authorization.php\n";
echo "   php examples/send_message.php\n";
echo "3. Check the examples/ directory for more usage examples\n";
echo "4. Read the README.md for full documentation\n\n";

echo "📚 Available examples:\n";
$examples = glob('examples/*.php');
foreach ($examples as $example) {
    $filename = basename($example);
    echo "   - {$filename}\n";
}

echo "\n🔗 Useful links:\n";
echo "   - Documentation: https://docs.sdkwa.pro\n";
echo "   - GitHub: https://github.com/sdkwa/whatsapp-api-client-php\n";
echo "   - Support: https://github.com/sdkwa/whatsapp-api-client-php/issues\n";

echo "\n✨ Happy coding with WhatsApp API Client PHP!\n";
