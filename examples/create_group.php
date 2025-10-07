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

    // Create a new group
    $response = $client->createGroup('My PHP Group', [
        '79999999999@c.us',
        '79999999998@c.us',
        '79999999997@c.us'
    ]);

    if ($response['created']) {
        echo "Group created successfully!" . PHP_EOL;
        echo "Group ID: " . $response['chatId'] . PHP_EOL;
        echo "Invite Link: " . $response['groupInviteLink'] . PHP_EOL;

        $groupId = $response['chatId'];

        // Send a message to the group
        $messageResponse = $client->sendMessage([
            'chatId' => $groupId,
            'message' => 'Welcome to our new group created with PHP SDK! 🎉'
        ]);

        echo "Welcome message sent to group!" . PHP_EOL;
        echo "Message ID: " . $messageResponse['idMessage'] . PHP_EOL;

        // Get group data
        $groupData = $client->getGroupData($groupId);
        echo "Group Name: " . $groupData['groupName'] . PHP_EOL;
        echo "Group Owner: " . $groupData['owner'] . PHP_EOL;
        echo "Participants: " . count($groupData['participants']) . PHP_EOL;

        // Add a new participant
        $client->addGroupParticipant($groupId, '79999999996@c.us');
        echo "New participant added to group!" . PHP_EOL;

        // Set someone as admin
        $client->setGroupAdmin($groupId, '79999999999@c.us');
        echo "Admin rights granted!" . PHP_EOL;

        // Update group name
        $client->updateGroupName($groupId, 'Updated PHP Group Name');
        echo "Group name updated!" . PHP_EOL;

        // Set group picture
        // $client->setGroupPicture($groupId, '/path/to/group-image.jpg');
        // echo "Group picture updated!" . PHP_EOL;
    } else {
        echo "Failed to create group" . PHP_EOL;
    }
} catch (WhatsAppApiException $e) {
    echo "WhatsApp API Error: " . $e->getMessage() . PHP_EOL;
    echo "Status Code: " . $e->getStatusCode() . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
