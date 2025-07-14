<?php

/**
 * Receive Notification Example
 * 
 * This example demonstrates how to poll for notifications
 * instead of using webhooks. Useful for testing or when
 * webhooks aren't available.
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

    echo "Polling for notifications...\n";

    // Poll for notifications
    while (true) {
        $notification = $client->receiveNotification();
        
        if ($notification) {
            echo "Received notification:\n";
            echo json_encode($notification, JSON_PRETTY_PRINT) . "\n";
            
            // Process based on notification type
            $type = $notification['typeWebhook'] ?? 'unknown';
            
            switch ($type) {
                case 'incomingMessageReceived':
                    $sender = $notification['senderData']['sender'];
                    $message = $notification['messageData']['textMessageData']['textMessage'] ?? 'Non-text message';
                    echo "Text message from {$sender}: {$message}\n";
                    break;
                    
                case 'outgoingMessageStatus':
                    $messageId = $notification['idMessage'];
                    $status = $notification['status'];
                    echo "Message {$messageId} status: {$status}\n";
                    break;
                    
                case 'stateInstanceChanged':
                    $state = $notification['stateInstance'];
                    echo "Instance state changed: {$state}\n";
                    break;
                    
                default:
                    echo "Unknown notification type: {$type}\n";
            }
            
            // Delete the notification after processing
            $client->deleteNotification($notification['receiptId']);
            echo "Notification processed and deleted.\n\n";
        } else {
            echo "No new notifications.\n";
        }
        
        // Wait before next poll
        sleep(2);
    }

} catch (WhatsAppApiException $e) {
    echo "WhatsApp API Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
