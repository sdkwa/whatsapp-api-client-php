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

    // Get webhook handler
    $webhookHandler = $client->getWebhookHandler();

    // Register handlers for different webhook types
    $webhookHandler->onIncomingMessageText(function ($data) {
        echo "📝 Received text message:" . PHP_EOL;
        echo "From: " . $data['senderData']['sender'] . PHP_EOL;
        echo "Message: " . $data['messageData']['textMessageData']['textMessage'] . PHP_EOL;
        echo "Chat ID: " . $data['senderData']['chatId'] . PHP_EOL;
        echo "---" . PHP_EOL;

        // You can respond to the message here
        // Example: Auto-reply
        /*
        global $client;
        $client->sendMessage([
            'chatId' => $data['senderData']['chatId'],
            'message' => 'Thanks for your message!'
        ]);
        */
    });

    $webhookHandler->onIncomingMessageFile(function ($data) {
        echo "📎 Received file message:" . PHP_EOL;
        echo "From: " . $data['senderData']['sender'] . PHP_EOL;
        echo "File URL: " . $data['messageData']['fileMessageData']['downloadUrl'] . PHP_EOL;
        echo "File Name: " . $data['messageData']['fileMessageData']['fileName'] . PHP_EOL;
        echo "Caption: " . ($data['messageData']['fileMessageData']['caption'] ?? 'No caption') . PHP_EOL;
        echo "---" . PHP_EOL;
    });

    $webhookHandler->onIncomingMessageLocation(function ($data) {
        echo "📍 Received location message:" . PHP_EOL;
        echo "From: " . $data['senderData']['sender'] . PHP_EOL;
        echo "Location: " . $data['messageData']['locationMessageData']['nameLocation'] . PHP_EOL;
        echo "Address: " . $data['messageData']['locationMessageData']['address'] . PHP_EOL;
        echo "Coordinates: " . $data['messageData']['locationMessageData']['latitude'] . ", " . $data['messageData']['locationMessageData']['longitude'] . PHP_EOL;
        echo "---" . PHP_EOL;
    });

    $webhookHandler->onIncomingMessageContact(function ($data) {
        echo "👤 Received contact message:" . PHP_EOL;
        echo "From: " . $data['senderData']['sender'] . PHP_EOL;
        echo "Contact: " . $data['messageData']['contactMessageData']['displayName'] . PHP_EOL;
        echo "Phone: " . $data['messageData']['contactMessageData']['vcard'] . PHP_EOL;
        echo "---" . PHP_EOL;
    });

    $webhookHandler->onOutgoingMessageStatus(function ($data) {
        echo "📤 Outgoing message status:" . PHP_EOL;
        echo "Message ID: " . $data['idMessage'] . PHP_EOL;
        echo "Status: " . $data['status'] . PHP_EOL;
        echo "Timestamp: " . $data['timestamp'] . PHP_EOL;
        echo "---" . PHP_EOL;
    });

    $webhookHandler->onStateInstance(function ($data) {
        echo "🔄 Instance state changed:" . PHP_EOL;
        echo "State: " . $data['stateInstance'] . PHP_EOL;
        echo "---" . PHP_EOL;
    });

    // Simple webhook endpoint simulation
    // In a real application, you would set up a proper web server endpoint
    echo "Webhook handler configured!" . PHP_EOL;
    echo "To use this in a web application, create an endpoint like:" . PHP_EOL;
    echo "POST /webhook" . PHP_EOL;
    echo PHP_EOL;
    echo "Example webhook endpoint code:" . PHP_EOL;
    echo "<?php" . PHP_EOL;
    echo "// webhook.php" . PHP_EOL;
    echo "require_once 'vendor/autoload.php';" . PHP_EOL;
    echo "use SDKWA\\WhatsAppApiClient;" . PHP_EOL;
    echo PHP_EOL;
    echo "\$client = new WhatsAppApiClient([...]);" . PHP_EOL;
    echo "\$webhookHandler = \$client->getWebhookHandler();" . PHP_EOL;
    echo PHP_EOL;
    echo "// Set up your handlers..." . PHP_EOL;
    echo PHP_EOL;
    echo "\$input = file_get_contents('php://input');" . PHP_EOL;
    echo "\$data = json_decode(\$input, true);" . PHP_EOL;
    echo "\$webhookHandler->processWebhook(\$data);" . PHP_EOL;
    echo PHP_EOL;
    echo "http_response_code(200);" . PHP_EOL;
    echo "echo 'OK';" . PHP_EOL;

    // Test webhook processing with sample data
    echo PHP_EOL . "Testing webhook with sample data..." . PHP_EOL;

    $sampleWebhookData = [
        'typeWebhook' => 'incomingMessageReceived',
        'instanceData' => [
            'idInstance' => $idInstance,
            'wid' => '79999999999@c.us',
            'typeInstance' => 'whatsapp'
        ],
        'timestamp' => time(),
        'idMessage' => 'test_message_id',
        'senderData' => [
            'chatId' => '79999999999@c.us',
            'sender' => '79999999999@c.us',
            'senderName' => 'Test User'
        ],
        'messageData' => [
            'typeMessage' => 'textMessage',
            'textMessageData' => [
                'textMessage' => 'Hello from webhook test!'
            ]
        ]
    ];

    $webhookHandler->processWebhook($sampleWebhookData);
} catch (WhatsAppApiException $e) {
    echo "WhatsApp API Error: " . $e->getMessage() . PHP_EOL;
    echo "Status Code: " . $e->getStatusCode() . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
