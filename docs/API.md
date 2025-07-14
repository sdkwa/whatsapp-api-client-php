# API Documentation

## Table of Contents

1. [Installation](#installation)
2. [Configuration](#configuration)
3. [Basic Usage](#basic-usage)
4. [Account Management](#account-management)
5. [Messaging](#messaging)
6. [File Handling](#file-handling)
7. [Group Management](#group-management)
8. [Webhook Handling](#webhook-handling)
9. [Instance Management](#instance-management)
10. [Error Handling](#error-handling)
11. [Advanced Usage](#advanced-usage)

## Installation

```bash
composer require sdkwa/whatsapp-api-client-php
```

## Configuration

### Basic Configuration

```php
use SDKWA\WhatsAppApiClient;

$client = new WhatsAppApiClient([
    'apiHost' => 'https://api.sdkwa.pro', // Optional
    'idInstance' => 'YOUR_INSTANCE_ID',
    'apiTokenInstance' => 'YOUR_API_TOKEN'
]);
```

### With User Credentials (for instance management)

```php
$client = new WhatsAppApiClient([
    'apiHost' => 'https://api.sdkwa.pro',
    'idInstance' => 'YOUR_INSTANCE_ID',
    'apiTokenInstance' => 'YOUR_API_TOKEN',
    'userId' => 'YOUR_USER_ID',
    'userToken' => 'YOUR_USER_TOKEN'
]);
```

## Basic Usage

### Check Account State

```php
$state = $client->getStateInstance();
echo $state['stateInstance']; // 'authorized', 'notAuthorized', 'blocked', 'starting'
```

### Send Simple Message

```php
$response = $client->sendMessage([
    'chatId' => '79999999999@c.us',
    'message' => 'Hello, World!'
]);

echo $response['idMessage']; // Message ID
```

## Account Management

### Get QR Code for Authorization

```php
$qr = $client->getQr();
if ($qr['type'] === 'qrCode') {
    // Save QR code image
    $qrData = preg_replace('/^data:image\/png;base64,/', '', $qr['message']);
    file_put_contents('qr.png', base64_decode($qrData));
}
```

### Get Account Settings

```php
$settings = $client->getSettings();
print_r($settings);
```

### Update Account Settings

```php
$client->setSettings([
    'webhookUrl' => 'https://yourserver.com/webhook',
    'outgoingWebhook' => 'yes',
    'incomingWebhook' => 'yes'
]);
```

### Account Control

```php
// Reboot account
$client->reboot();

// Logout account
$client->logout();

// Get warming status
$status = $client->getWarmingPhoneStatus();
```

## Messaging

### Send Text Message

```php
$response = $client->sendMessage([
    'chatId' => '79999999999@c.us',
    'message' => 'Hello!',
    'quotedMessageId' => 'optional_message_id', // Optional
    'archiveChat' => false, // Optional
    'linkPreview' => true // Optional
]);
```

### Send Contact

```php
$response = $client->sendContact([
    'chatId' => '79999999999@c.us',
    'contact' => [
        'phoneContact' => 79999999999,
        'firstName' => 'John',
        'lastName' => 'Doe',
        'company' => 'SDKWA'
    ]
]);
```

### Send Location

```php
$response = $client->sendLocation([
    'chatId' => '79999999999@c.us',
    'latitude' => 51.5074,
    'longitude' => -0.1278,
    'nameLocation' => 'London Eye',
    'address' => 'Westminster Bridge Rd, London, UK'
]);
```

## File Handling

### Send File by Upload

```php
$response = $client->sendFileByUpload([
    'chatId' => '79999999999@c.us',
    'file' => '/path/to/file.jpg', // File path or file content
    'fileName' => 'image.jpg',
    'caption' => 'Check this out!'
]);
```

### Send File by URL

```php
$response = $client->sendFileByUrl([
    'chatId' => '79999999999@c.us',
    'urlFile' => 'https://example.com/file.pdf',
    'fileName' => 'document.pdf',
    'caption' => 'Important document'
]);
```

### Upload File

```php
$response = $client->uploadFile('/path/to/file.jpg');
$fileUrl = $response['urlFile'];

// Now use the URL to send the file
$client->sendFileByUrl([
    'chatId' => '79999999999@c.us',
    'urlFile' => $fileUrl,
    'fileName' => 'uploaded.jpg'
]);
```

## Group Management

### Create Group

```php
$response = $client->createGroup('My Group', [
    '79999999999@c.us',
    '79999999998@c.us'
]);

$groupId = $response['chatId'];
$inviteLink = $response['groupInviteLink'];
```

### Group Information

```php
$groupData = $client->getGroupData('GROUP_ID@g.us');
print_r($groupData);
```

### Group Participants

```php
// Add participant
$client->addGroupParticipant('GROUP_ID@g.us', '79999999997@c.us');

// Remove participant
$client->removeGroupParticipant('GROUP_ID@g.us', '79999999997@c.us');

// Set as admin
$client->setGroupAdmin('GROUP_ID@g.us', '79999999999@c.us');

// Remove admin rights
$client->removeAdmin('GROUP_ID@g.us', '79999999999@c.us');
```

### Group Settings

```php
// Update group name
$client->updateGroupName('GROUP_ID@g.us', 'New Group Name');

// Set group picture
$client->setGroupPicture('GROUP_ID@g.us', '/path/to/image.jpg');

// Leave group
$client->leaveGroup('GROUP_ID@g.us');
```

## Webhook Handling

### Basic Webhook Setup

```php
$webhookHandler = $client->getWebhookHandler();

// Handle incoming text messages
$webhookHandler->onIncomingMessageText(function($data) {
    $sender = $data['senderData']['sender'];
    $message = $data['messageData']['textMessageData']['textMessage'];
    echo "Message from {$sender}: {$message}\n";
});

// Handle incoming files
$webhookHandler->onIncomingMessageFile(function($data) {
    $sender = $data['senderData']['sender'];
    $fileName = $data['messageData']['fileMessageData']['fileName'];
    $fileUrl = $data['messageData']['fileMessageData']['downloadUrl'];
    echo "File from {$sender}: {$fileName} ({$fileUrl})\n";
});

// Process webhook data
$webhookData = json_decode(file_get_contents('php://input'), true);
$webhookHandler->processWebhook($webhookData);
```

### All Webhook Types

```php
$webhookHandler
    ->onIncomingMessageText(function($data) { /* handle text */ })
    ->onIncomingMessageFile(function($data) { /* handle files */ })
    ->onIncomingMessageLocation(function($data) { /* handle location */ })
    ->onIncomingMessageContact(function($data) { /* handle contact */ })
    ->onIncomingMessageExtendedText(function($data) { /* handle extended text */ })
    ->onOutgoingMessageStatus(function($data) { /* handle status updates */ })
    ->onStateInstance(function($data) { /* handle state changes */ })
    ->onDeviceInfo(function($data) { /* handle device info */ });
```

## Instance Management

### Get User Instances

```php
$instances = $client->getInstances();
foreach ($instances['instances'] as $instance) {
    echo "Instance: {$instance['idInstance']}\n";
    echo "State: {$instance['stateInstance']}\n";
}
```

### Create Instance

```php
$response = $client->createInstance('DEVELOPER', 'infinitely');
$newInstanceId = $response['instance']['idInstance'];
$newApiToken = $response['instance']['apiTokenInstance'];
```

### Manage Instance

```php
// Extend instance
$client->extendInstance(123, 'DEVELOPER', 'month1');

// Delete instance
$client->deleteInstance(123);

// Restore instance
$client->restoreInstance(123);
```

## Error Handling

### Basic Error Handling

```php
use SDKWA\Exceptions\WhatsAppApiException;

try {
    $response = $client->sendMessage([
        'chatId' => '79999999999@c.us',
        'message' => 'Hello!'
    ]);
} catch (WhatsAppApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
    echo "Status Code: " . $e->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
```

### Common Error Codes

- `400`: Bad Request - Invalid parameters
- `401`: Unauthorized - Invalid API token
- `404`: Not Found - Instance not found
- `429`: Too Many Requests - Rate limit exceeded
- `500`: Internal Server Error

## Advanced Usage

### Receiving Messages

```php
// Poll for notifications
$notification = $client->receiveNotification();
if (!empty($notification)) {
    // Process notification
    $receiptId = $notification['receiptId'];
    
    // Delete processed notification
    $client->deleteNotification($receiptId);
}
```

### Chat Management

```php
// Get chat history
$history = $client->getChatHistory([
    'chatId' => '79999999999@c.us',
    'count' => 50
]);

// Mark chat as read
$client->readChat(['chatId' => '79999999999@c.us']);

// Archive/unarchive chat
$client->archiveChat('79999999999@c.us');
$client->unarchiveChat('79999999999@c.us');

// Delete message
$client->deleteMessage('79999999999@c.us', 'MESSAGE_ID');
```

### Queue Management

```php
// Show messages in queue
$queue = $client->showMessagesQueue();
echo "Messages in queue: " . count($queue) . "\n";

// Clear queue
$client->clearMessagesQueue();
```

### Profile Management

```php
// Set profile name
$client->setProfileName('My Bot');

// Set profile status
$client->setProfileStatus('Online');

// Set profile picture
$client->setProfilePicture('/path/to/avatar.jpg');

// Get avatar
$avatar = $client->getAvatar('79999999999@c.us');
if ($avatar['existsAvatar']) {
    echo "Avatar URL: " . $avatar['urlAvatar'] . "\n";
}
```

### Utility Functions

```php
// Check if number has WhatsApp
$check = $client->checkWhatsapp(79999999999);
if ($check['existsWhatsapp']) {
    echo "Number has WhatsApp\n";
}

// Get contacts
$contacts = $client->getContacts();

// Get chats
$chats = $client->getChats();

// Get contact info
$info = $client->getContactInfo('79999999999@c.us');
```

## Rate Limiting

The API has rate limits to prevent abuse:

- Message sending: Configurable delay between messages
- API calls: Standard rate limiting applies
- File uploads: Size limit of 100 MB per file

## Best Practices

1. **Always handle exceptions** - API calls can fail
2. **Use webhooks** for real-time message processing
3. **Store message IDs** for tracking and references
4. **Implement proper logging** for debugging
5. **Test with small groups** before scaling
6. **Monitor API usage** and respect rate limits
7. **Keep credentials secure** - never expose API tokens
8. **Use environment variables** for configuration

## Examples

See the `examples/` directory for complete working examples:

- `send_message.php` - Basic message sending
- `send_file.php` - File upload and sending
- `create_group.php` - Group creation and management
- `webhook_handler.php` - Webhook processing
- `qr_authorization.php` - QR code authorization
- `instance_management.php` - Instance operations
- `whatsapp_bot.php` - Complete bot implementation
- `webhook_endpoint.php` - Production webhook endpoint

Each example includes detailed comments and error handling.
