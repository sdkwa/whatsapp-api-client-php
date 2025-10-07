<?php

/**
 * Telegram Send Message Example
 *
 * This example demonstrates how to use the library with Telegram
 * instead of WhatsApp.
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

    // Send a message via Telegram (pass 'telegram' as second parameter)
    $response = $client->sendMessage([
        'chatId' => '@username', // Telegram username or chat ID
        'message' => 'Hello from Telegram! 🚀'
    ], 'telegram');

    echo "Message sent successfully!\n";
    echo "Message ID: " . $response['idMessage'] . "\n";
} catch (WhatsAppApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
    echo "Status Code: " . $e->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
