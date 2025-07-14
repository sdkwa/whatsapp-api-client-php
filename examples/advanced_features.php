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

    // Send contact card
    echo "Sending contact card..." . PHP_EOL;
    $contactResponse = $client->sendContact([
        'chatId' => '79999999999@c.us',
        'contact' => [
            'phoneContact' => 79999999999,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'company' => 'SDKWA Corp'
        ]
    ]);

    echo "Contact sent! Message ID: " . $contactResponse['idMessage'] . PHP_EOL;

    // Send location
    echo "Sending location..." . PHP_EOL;
    $locationResponse = $client->sendLocation([
        'chatId' => '79999999999@c.us',
        'latitude' => 51.5074,
        'longitude' => -0.1278,
        'nameLocation' => 'London Eye',
        'address' => 'Westminster Bridge Rd, London, UK'
    ]);

    echo "Location sent! Message ID: " . $locationResponse['idMessage'] . PHP_EOL;

    // Check if a number has WhatsApp
    echo "Checking WhatsApp availability..." . PHP_EOL;
    $checkResponse = $client->checkWhatsapp(79999999999);
    
    if ($checkResponse['existsWhatsapp']) {
        echo "✅ Phone number has WhatsApp" . PHP_EOL;
    } else {
        echo "❌ Phone number does not have WhatsApp" . PHP_EOL;
    }

    // Get chat history
    echo "Getting chat history..." . PHP_EOL;
    $history = $client->getChatHistory([
        'chatId' => '79999999999@c.us',
        'count' => 10
    ]);

    echo "Retrieved " . count($history) . " messages from chat history" . PHP_EOL;

    // Get account avatar
    echo "Getting account avatar..." . PHP_EOL;
    $avatar = $client->getAvatar('79999999999@c.us');
    
    if (isset($avatar['existsAvatar']) && $avatar['existsAvatar']) {
        echo "Avatar URL: " . $avatar['urlAvatar'] . PHP_EOL;
    } else {
        echo "No avatar found" . PHP_EOL;
    }

    // Archive and unarchive chat
    echo "Archiving chat..." . PHP_EOL;
    $client->archiveChat('79999999999@c.us');
    echo "Chat archived!" . PHP_EOL;

    echo "Unarchiving chat..." . PHP_EOL;
    $client->unarchiveChat('79999999999@c.us');
    echo "Chat unarchived!" . PHP_EOL;

    // Mark chat as read
    echo "Marking chat as read..." . PHP_EOL;
    $readResponse = $client->readChat([
        'chatId' => '79999999999@c.us'
    ]);

    if (isset($readResponse['setRead']) && $readResponse['setRead']) {
        echo "Chat marked as read!" . PHP_EOL;
    }

    // Get messages queue
    echo "Getting messages queue..." . PHP_EOL;
    $queue = $client->showMessagesQueue();
    echo "Messages in queue: " . count($queue) . PHP_EOL;

    // Update profile
    echo "Updating profile..." . PHP_EOL;
    $client->setProfileName('SDKWA PHP Bot');
    $client->setProfileStatus('Online via PHP SDK');
    echo "Profile updated!" . PHP_EOL;

    // Get warming status
    echo "Getting warming status..." . PHP_EOL;
    $warmingStatus = $client->getWarmingPhoneStatus();
    echo "Warming status: " . json_encode($warmingStatus) . PHP_EOL;

} catch (WhatsAppApiException $e) {
    echo "WhatsApp API Error: " . $e->getMessage() . PHP_EOL;
    echo "Status Code: " . $e->getStatusCode() . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
