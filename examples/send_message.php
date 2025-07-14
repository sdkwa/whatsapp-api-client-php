<?php

require_once '../vendor/autoload.php';

use SDKWA\WhatsAppApiClient;
use SDKWA\Exceptions\WhatsAppApiException;

// Load environment variables (you can use vlucas/phpdotenv or set them manually)
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

    // Send a simple text message
    $response = $client->sendMessage([
        'chatId' => '79999999999@c.us', // Replace with actual chat ID
        'message' => 'Hello from PHP SDK! 👋'
    ]);

    echo "Message sent successfully!" . PHP_EOL;
    echo "Message ID: " . $response['idMessage'] . PHP_EOL;

    // Send a message with quote
    $response = $client->sendMessage([
        'chatId' => '79999999999@c.us',
        'message' => 'This is a quoted message',
        'quotedMessageId' => 'some_message_id' // Optional
    ]);

    echo "Quoted message sent!" . PHP_EOL;
    echo "Message ID: " . $response['idMessage'] . PHP_EOL;

} catch (WhatsAppApiException $e) {
    echo "WhatsApp API Error: " . $e->getMessage() . PHP_EOL;
    echo "Status Code: " . $e->getStatusCode() . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
