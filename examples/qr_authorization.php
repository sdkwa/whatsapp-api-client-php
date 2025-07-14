<?php

require_once '../vendor/autoload.php';

use SDKWA\WhatsAppApiClient;
use SDKWA\Exceptions\WhatsAppApiException;

// Load environment variables
$apiHost = $_ENV['API_HOST'] ?? 'https://api.sdkwa.pro';
$idInstance = $_ENV['ID_INSTANCE'] ?? 'YOUR_INSTANCE_ID';
$apiTokenInstance = $_ENV['API_TOKEN_INSTANCE'] ?? 'YOUR_API_TOKEN';

try {
    // Initialize the client
    $client = new WhatsAppApiClient([
        'apiHost' => $apiHost,
        'idInstance' => $idInstance,
        'apiTokenInstance' => $apiTokenInstance
    ]);

    // Check current account state
    $state = $client->getStateInstance();
    echo "Current account state: " . $state['stateInstance'] . PHP_EOL;

    if ($state['stateInstance'] === 'authorized') {
        echo "Account is already authorized!" . PHP_EOL;
    } else {
        echo "Account needs authorization. Getting QR code..." . PHP_EOL;

        // Get QR code
        $qrResponse = $client->getQr();
        
        if ($qrResponse['type'] === 'qrCode') {
            echo "QR Code (base64): " . substr($qrResponse['message'], 0, 50) . "..." . PHP_EOL;
            echo "Please scan this QR code with your WhatsApp app." . PHP_EOL;
            echo "You can decode the base64 string and display it as an image." . PHP_EOL;
            
            // You can save the QR code to a file
            $qrData = $qrResponse['message'];
            // Remove data:image/png;base64, prefix if present
            $qrData = preg_replace('/^data:image\/png;base64,/', '', $qrData);
            file_put_contents('qr_code.png', base64_decode($qrData));
            echo "QR code saved as qr_code.png" . PHP_EOL;
            
        } else {
            echo "QR Response: " . $qrResponse['message'] . PHP_EOL;
        }

        // Alternative: Authorization by phone number
        echo PHP_EOL . "Alternative: Authorization by phone number" . PHP_EOL;
        echo "You can also authorize using getAuthorizationCode method:" . PHP_EOL;
        echo "1. Use 'Link device' -> 'Link with phone number' in WhatsApp" . PHP_EOL;
        echo "2. Call getAuthorizationCode with your phone number" . PHP_EOL;
        
        /*
        $authResponse = $client->getAuthorizationCode([
            'phoneNumber' => 79999999999 // Your phone number
        ]);
        echo "Authorization code: " . $authResponse['code'] . PHP_EOL;
        */
    }

    // Monitor state changes
    echo PHP_EOL . "Monitoring state changes..." . PHP_EOL;
    for ($i = 0; $i < 10; $i++) {
        sleep(2);
        $state = $client->getStateInstance();
        echo "State check #" . ($i + 1) . ": " . $state['stateInstance'] . PHP_EOL;
        
        if ($state['stateInstance'] === 'authorized') {
            echo "✅ Account is now authorized!" . PHP_EOL;
            break;
        }
    }

    // If authorized, get account info
    if ($state['stateInstance'] === 'authorized') {
        $settings = $client->getSettings();
        echo PHP_EOL . "Account settings:" . PHP_EOL;
        echo "Webhook URL: " . ($settings['webhookUrl'] ?? 'Not set') . PHP_EOL;
        echo "Outgoing webhook: " . ($settings['outgoingWebhook'] ?? 'Not set') . PHP_EOL;
        echo "Incoming webhook: " . ($settings['incomingWebhook'] ?? 'Not set') . PHP_EOL;
        
        // Get contacts
        $contacts = $client->getContacts();
        echo "Total contacts: " . count($contacts) . PHP_EOL;
        
        // Get chats
        $chats = $client->getChats();
        echo "Total chats: " . count($chats) . PHP_EOL;
    }

} catch (WhatsAppApiException $e) {
    echo "WhatsApp API Error: " . $e->getMessage() . PHP_EOL;
    echo "Status Code: " . $e->getStatusCode() . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
