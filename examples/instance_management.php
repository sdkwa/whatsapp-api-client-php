<?php

require_once '../vendor/autoload.php';

use SDKWA\WhatsAppApiClient;
use SDKWA\Exceptions\WhatsAppApiException;

// Load environment variables
$apiHost = $_ENV['API_HOST'] ?? 'https://api.sdkwa.pro';
$idInstance = $_ENV['ID_INSTANCE'] ?? 'YOUR_INSTANCE_ID';
$apiTokenInstance = $_ENV['API_TOKEN_INSTANCE'] ?? 'YOUR_API_TOKEN';
$userId = $_ENV['USER_ID'] ?? 'YOUR_USER_ID';
$userToken = $_ENV['USER_TOKEN'] ?? 'YOUR_USER_TOKEN';

try {
    // Initialize the client with user credentials for instance management
    $client = new WhatsAppApiClient([
        'apiHost' => $apiHost,
        'idInstance' => $idInstance,
        'apiTokenInstance' => $apiTokenInstance,
        'userId' => $userId,
        'userToken' => $userToken
    ]);

    // Get all user instances
    echo "Getting all user instances..." . PHP_EOL;
    $instances = $client->getInstances();
    
    if (isset($instances['instances'])) {
        echo "Total instances: " . count($instances['instances']) . PHP_EOL;
        
        foreach ($instances['instances'] as $instance) {
            echo "Instance ID: " . $instance['idInstance'] . PHP_EOL;
            echo "State: " . $instance['stateInstance'] . PHP_EOL;
            echo "Webhook URL: " . ($instance['webhookUrl'] ?? 'Not set') . PHP_EOL;
            echo "---" . PHP_EOL;
        }
    } else {
        echo "No instances found or error: " . json_encode($instances) . PHP_EOL;
    }

    // Create a new instance
    echo PHP_EOL . "Creating new instance..." . PHP_EOL;
    $createResponse = $client->createInstance('DEVELOPER', 'infinitely');
    
    if (isset($createResponse['instance'])) {
        echo "Instance created successfully!" . PHP_EOL;
        echo "Instance ID: " . $createResponse['instance']['idInstance'] . PHP_EOL;
        echo "API Token: " . $createResponse['instance']['apiTokenInstance'] . PHP_EOL;
        
        $newInstanceId = $createResponse['instance']['idInstance'];
        
        // You can now use this new instance
        $newClient = new WhatsAppApiClient([
            'apiHost' => $apiHost,
            'idInstance' => $newInstanceId,
            'apiTokenInstance' => $createResponse['instance']['apiTokenInstance']
        ]);
        
        $newState = $newClient->getStateInstance();
        echo "New instance state: " . $newState['stateInstance'] . PHP_EOL;
        
        // Extend instance (example)
        echo PHP_EOL . "Extending instance for 1 month..." . PHP_EOL;
        $extendResponse = $client->extendInstance($newInstanceId, 'DEVELOPER', 'month1');
        
        if (isset($extendResponse['extend'])) {
            echo "Instance extended successfully!" . PHP_EOL;
            echo "Order ID: " . $extendResponse['order']['idOrder'] . PHP_EOL;
        } else {
            echo "Failed to extend instance: " . json_encode($extendResponse) . PHP_EOL;
        }
        
        // Delete instance (uncomment to test)
        /*
        echo PHP_EOL . "Deleting instance..." . PHP_EOL;
        $deleteResponse = $client->deleteInstance($newInstanceId);
        
        if (isset($deleteResponse['delete'])) {
            echo "Instance deleted successfully!" . PHP_EOL;
        } else {
            echo "Failed to delete instance: " . json_encode($deleteResponse) . PHP_EOL;
        }
        */
        
        // Restore instance (if deleted)
        /*
        echo PHP_EOL . "Restoring instance..." . PHP_EOL;
        $restoreResponse = $client->restoreInstance($newInstanceId);
        
        if (isset($restoreResponse['restore'])) {
            echo "Instance restored successfully!" . PHP_EOL;
        } else {
            echo "Failed to restore instance: " . json_encode($restoreResponse) . PHP_EOL;
        }
        */
        
    } else {
        echo "Failed to create instance: " . json_encode($createResponse) . PHP_EOL;
    }

} catch (WhatsAppApiException $e) {
    echo "WhatsApp API Error: " . $e->getMessage() . PHP_EOL;
    echo "Status Code: " . $e->getStatusCode() . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
