<?php

/**
 * Telegram Authorization Example
 *
 * This example demonstrates how to authorize a Telegram instance
 * using phone number and confirmation code.
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

    // Step 1: Send confirmation code to phone number (pass 'telegram' as second parameter)
    echo "Step 1: Sending confirmation code...\n";
    $phoneNumber = 712345678989; // Your phone number without plus sign

    $response = $client->sendConfirmationCode($phoneNumber, 'telegram');
    echo "Confirmation code sent!\n";
    print_r($response);

    // Step 2: Enter the confirmation code you received
    echo "\nStep 2: Please enter the confirmation code you received:\n";
    $code = trim(fgets(STDIN));

    // Step 3: Sign in with confirmation code (pass 'telegram' as second parameter)
    echo "Signing in...\n";
    $signInResponse = $client->signInWithConfirmationCode($code, 'telegram');

    echo "Successfully authorized!\n";
    print_r($signInResponse);
} catch (WhatsAppApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
    echo "Status Code: " . $e->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
