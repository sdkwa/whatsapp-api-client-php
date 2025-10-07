<?php

/**
 * Telegram App Creation Example
 *
 * This example demonstrates how to create a Telegram app.
 */

require_once '../vendor/autoload.php';

use SDKWA\WhatsAppApiClient;
use SDKWA\Exceptions\WhatsAppApiException;

try {
    // Initialize the client
    $client = new WhatsAppApiClient([
        'apiHost' => $_ENV['API_HOST'] ?? 'https://api.sdkwa.pro',
        'idInstance' => $_ENV['ID_INSTANCE'] ?? 'YOUR_INSTANCE_ID',
        'apiTokenInstance' => $_ENV['API_TOKEN_INSTANCE'] ?? 'YOUR_API_TOKEN'
    ]);

    // Create Telegram app (pass 'telegram' as last parameter)
    $response = $client->createApp(
        'My Awesome App',           // title
        'myapp',                    // shortName
        'https://myapp.com',        // url
        'This is my awesome app',   // description
        'telegram'                  // messengerType
    );

    echo "App created successfully!\n";
    print_r($response);
} catch (WhatsAppApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
    echo "Status Code: " . $e->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
