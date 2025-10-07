<?php

/**
 * Webhook Endpoint Example
 *
 * This file demonstrates how to create a webhook endpoint
 * to receive WhatsApp messages and events in real-time.
 *
 * Usage:
 * 1. Deploy this file to a web server
 * 2. Configure your webhook URL in SDKWA settings
 * 3. The endpoint will receive and process webhook events
 */

require_once '../vendor/autoload.php';

use SDKWA\WhatsAppApiClient;
use SDKWA\Exceptions\WhatsAppApiException;

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Initialize the client
    $client = new WhatsAppApiClient([
        'apiHost' => $_ENV['API_HOST'] ?? 'https://api.sdkwa.pro',
        'idInstance' => $_ENV['ID_INSTANCE'] ?? 'YOUR_INSTANCE_ID',
        'apiTokenInstance' => $_ENV['API_TOKEN_INSTANCE'] ?? 'YOUR_API_TOKEN'
    ]);

    // Get the webhook handler
    $webhookHandler = $client->getWebhookHandler();

    // Set up handlers for different message types
    $webhookHandler->onIncomingMessageText(function ($data) use ($client) {
        $senderId = $data['senderData']['sender'];
        $chatId = $data['senderData']['chatId'];
        $message = $data['messageData']['textMessageData']['textMessage'];
        $timestamp = $data['timestamp'];

        // Log the message
        error_log("Received text message from {$senderId}: {$message}");

        // Auto-reply example
        if (strtolower($message) === 'hello' || strtolower($message) === 'hi') {
            $client->sendMessage([
                'chatId' => $chatId,
                'message' => 'Hello! Thanks for contacting us. How can I help you today?'
            ]);
        }

        // Command handling example
        if (strtolower($message) === '/help') {
            $client->sendMessage([
                'chatId' => $chatId,
                'message' => "Available commands:\n/help - Show this help\n/time - Get current time\n/status - Check bot status"
            ]);
        }

        if (strtolower($message) === '/time') {
            $client->sendMessage([
                'chatId' => $chatId,
                'message' => 'Current time: ' . date('Y-m-d H:i:s T')
            ]);
        }

        if (strtolower($message) === '/status') {
            $client->sendMessage([
                'chatId' => $chatId,
                'message' => '✅ Bot is online and working properly!'
            ]);
        }

        // Store message in database (example)
        // storeMessage($senderId, $chatId, $message, $timestamp);
    });

    $webhookHandler->onIncomingMessageFile(function ($data) use ($client) {
        $senderId = $data['senderData']['sender'];
        $chatId = $data['senderData']['chatId'];
        $fileName = $data['messageData']['fileMessageData']['fileName'];
        $fileUrl = $data['messageData']['fileMessageData']['downloadUrl'];
        $caption = $data['messageData']['fileMessageData']['caption'] ?? '';

        error_log("Received file from {$senderId}: {$fileName}");

        // Process the file (example)
        $client->sendMessage([
            'chatId' => $chatId,
            'message' => "Thanks for sending the file '{$fileName}'. I've received it!"
        ]);

        // Download and process file (example)
        // downloadAndProcessFile($fileUrl, $fileName, $senderId);
    });

    $webhookHandler->onIncomingMessageLocation(function ($data) use ($client) {
        $senderId = $data['senderData']['sender'];
        $chatId = $data['senderData']['chatId'];
        $locationName = $data['messageData']['locationMessageData']['nameLocation'];
        $address = $data['messageData']['locationMessageData']['address'];
        $latitude = $data['messageData']['locationMessageData']['latitude'];
        $longitude = $data['messageData']['locationMessageData']['longitude'];

        error_log("Received location from {$senderId}: {$locationName}");

        $client->sendMessage([
            'chatId' => $chatId,
            'message' => "Thanks for sharing your location: {$locationName}\nAddress: {$address}\nCoordinates: {$latitude}, {$longitude}"
        ]);
    });

    $webhookHandler->onIncomingMessageContact(function ($data) use ($client) {
        $senderId = $data['senderData']['sender'];
        $chatId = $data['senderData']['chatId'];
        $contactName = $data['messageData']['contactMessageData']['displayName'];

        error_log("Received contact from {$senderId}: {$contactName}");

        $client->sendMessage([
            'chatId' => $chatId,
            'message' => "Thanks for sharing the contact: {$contactName}"
        ]);
    });

    $webhookHandler->onOutgoingMessageStatus(function ($data) {
        $messageId = $data['idMessage'];
        $status = $data['status'];
        $timestamp = $data['timestamp'];

        error_log("Message {$messageId} status: {$status}");

        // Update message status in database
        // updateMessageStatus($messageId, $status, $timestamp);
    });

    $webhookHandler->onStateInstance(function ($data) {
        $state = $data['stateInstance'];

        error_log("Instance state changed: {$state}");

        // Handle state changes
        if ($state === 'authorized') {
            // Instance is now authorized
            // You might want to send a notification or update status
        } elseif ($state === 'blocked') {
            // Instance is blocked
            // Send alert to admin
        }
    });

    // Get the webhook data
    $input = file_get_contents('php://input');
    $webhookData = json_decode($input, true);

    // Validate webhook data
    if (!$webhookData) {
        throw new Exception('Invalid webhook data');
    }

    // Process the webhook
    $webhookHandler->processWebhook($webhookData);

    // Send success response
    http_response_code(200);
    echo json_encode(['status' => 'success']);
} catch (WhatsAppApiException $e) {
    error_log("WhatsApp API Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

// Example helper functions (implement as needed)

function storeMessage($senderId, $chatId, $message, $timestamp)
{
    // Store message in database
    // Example: INSERT INTO messages (sender_id, chat_id, message, timestamp) VALUES (?, ?, ?, ?)
}

function updateMessageStatus($messageId, $status, $timestamp)
{
    // Update message status in database
    // Example: UPDATE messages SET status = ?, status_timestamp = ? WHERE id = ?
}

function downloadAndProcessFile($fileUrl, $fileName, $senderId)
{
    // Download and process file
    // Example:
    // $fileContent = file_get_contents($fileUrl);
    // $localPath = "uploads/{$senderId}_{$fileName}";
    // file_put_contents($localPath, $fileContent);
    // processFile($localPath);
}
