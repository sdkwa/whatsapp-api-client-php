# WhatsApp API Client PHP

[![Latest Stable Version](https://poser.pugx.org/sdkwa/whatsapp-api-client-php/v/stable)](https://packagist.org/packages/sdkwa/whatsapp-api-client-php)
[![Total Downloads](https://poser.pugx.org/sdkwa/whatsapp-api-client-php/downloads)](https://packagist.org/packages/sdkwa/whatsapp-api-client-php)
[![License](https://poser.pugx.org/sdkwa/whatsapp-api-client-php/license)](https://packagist.org/packages/sdkwa/whatsapp-api-client-php)
[![CI/CD Pipeline](https://github.com/sdkwa/whatsapp-api-client-php/actions/workflows/ci.yml/badge.svg)](https://github.com/sdkwa/whatsapp-api-client-php/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-8892BF.svg)](https://php.net/)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)
[![PSR-12](https://img.shields.io/badge/code%20style-PSR--12-blue.svg)](https://www.php-fig.org/psr/psr-12/)

PHP SDK for SDKWA WhatsApp HTTP API

## Installation

Install the package via Composer:

```bash
composer require sdkwa/whatsapp-api-client-php
```

**📚 New to this library?** Check out the [Getting Started Guide](GETTING_STARTED.md) for a step-by-step tutorial including authorization setup!

## Quick Start

### Step 1: Initialize Client

```php
<?php
require_once 'vendor/autoload.php';

use SDKWA\WhatsAppApiClient;

$client = new WhatsAppApiClient([
    'apiHost' => 'https://api.sdkwa.pro', // optional
    'idInstance' => 'YOUR_INSTANCE_ID',
    'apiTokenInstance' => 'YOUR_API_TOKEN'
]);
```

### Step 2: Authorize (Scan QR Code)

**⚠️ IMPORTANT:** Before you can send or receive messages, you must authorize your instance by scanning the QR code.

```php
// Get QR code for WhatsApp authorization
$qr = $client->getQr('whatsapp');

echo "Scan this QR code with WhatsApp:\n";
echo $qr['message']; // Display QR code or URL

// Check authorization status
$state = $client->getStateInstance('whatsapp');
echo "State: " . $state['stateInstance']; // Should be 'authorized' after scanning
```

For Telegram authorization, use phone number confirmation:
```php
// Send confirmation code to phone
$client->sendConfirmationCode(712345678989, 'telegram');

// After receiving code, sign in
$client->signInWithConfirmationCode('YOUR_CODE', 'telegram');
```

### Step 3: Send Messages

Once authorized, you can send and receive messages:

```php
// Send a WhatsApp message
$response = $client->sendMessage([
    'chatId' => '79999999999@c.us',
    'message' => 'Hello, World!'
], 'whatsapp');

// Send a Telegram message
$response = $client->sendMessage([
    'chatId' => '@username',
    'message' => 'Hello from Telegram!'
], 'telegram');

echo "Message sent with ID: " . $response['idMessage'];
```

## Features

- ✅ **Multi-Messenger Support**: WhatsApp and Telegram
- ✅ Send text messages
- ✅ Send files (by upload or URL)
- ✅ Send contacts and locations
- ✅ Manage groups
- ✅ Handle webhooks
- ✅ Account management
- ✅ QR code authorization
- ✅ Instance management
- ✅ Telegram app creation
- ✅ Full API coverage

## Documentation

> **⚠️ Important:** Before you can send or receive messages, you must **authorize your instance** by:
> - **WhatsApp:** Scanning the QR code with your WhatsApp mobile app
> - **Telegram:** Confirming your phone number with a verification code
>
> See the [Authorization](#authorization-required-first) section below for details.

### Basic Usage

#### Initialize Client

```php
use SDKWA\WhatsAppApiClient;

$client = new WhatsAppApiClient([
    'apiHost' => 'https://api.sdkwa.pro', // optional, defaults to https://api.sdkwa.pro
    'idInstance' => 'YOUR_INSTANCE_ID',
    'apiTokenInstance' => 'YOUR_API_TOKEN',
    'userId' => 'YOUR_USER_ID', // optional, required for instance management
    'userToken' => 'YOUR_USER_TOKEN' // optional, required for instance management
]);
```

**Note:** The `messengerType` parameter is passed with each API method call, not during initialization. This allows you to use the same client instance for both WhatsApp and Telegram.

#### Authorization (Required First!)

**Before sending or receiving any messages, you must authorize your instance:**

##### WhatsApp Authorization (QR Code)

```php
// Step 1: Get QR code
$qr = $client->getQr('whatsapp');

// Display the QR code (can be shown as image or text)
echo "QR Code: " . $qr['message'] . "\n";

// Step 2: Check if authorized
$state = $client->getStateInstance('whatsapp');

if ($state['stateInstance'] === 'authorized') {
    echo "WhatsApp is authorized and ready!";
} else {
    echo "Please scan the QR code with WhatsApp";
}
```

##### Telegram Authorization (Phone Number)

```php
// Step 1: Send confirmation code to your phone
$response = $client->sendConfirmationCode(712345678989, 'telegram'); // Phone without +

// Step 2: Enter the code you received
$result = $client->signInWithConfirmationCode('12345', 'telegram');

// Step 3: Verify authorization
$state = $client->getStateInstance('telegram');
echo "Telegram state: " . $state['stateInstance'];
```

#### Send Messages

```php
// Send WhatsApp text message
$response = $client->sendMessage([
    'chatId' => '79999999999@c.us',
    'message' => 'Hello, World!',
    'quotedMessageId' => 'optional_quoted_message_id'
], 'whatsapp');

// Send Telegram text message
$response = $client->sendMessage([
    'chatId' => '@username',
    'message' => 'Hello from Telegram!'
], 'telegram');

// Send file by upload (WhatsApp)
$response = $client->sendFileByUpload([
    'chatId' => '79999999999@c.us',
    'file' => '/path/to/file.jpg',
    'fileName' => 'image.jpg',
    'caption' => 'Check out this image!'
], 'whatsapp');

// Send file by URL (Telegram)
$response = $client->sendFileByUrl([
    'chatId' => '@username',
    'urlFile' => 'https://example.com/file.pdf',
    'fileName' => 'document.pdf',
    'caption' => 'Important document'
], 'telegram');

// Send contact (WhatsApp - default)
$response = $client->sendContact([
    'chatId' => '79999999999@c.us',
    'contact' => [
        'phoneContact' => 79999999999,
        'firstName' => 'John',
        'lastName' => 'Doe'
    ]
]);  // Default messenger type is 'whatsapp' if not specified

// Send location
$response = $client->sendLocation([
    'chatId' => '79999999999@c.us',
    'latitude' => 51.5074,
    'longitude' => -0.1278,
    'nameLocation' => 'London',
    'address' => 'London, UK'
], 'whatsapp');
```

#### Account Management

```php
// Check account authorization status
$state = $client->getStateInstance('whatsapp');
echo "Status: " . $state['stateInstance']; // 'notAuthorized', 'authorized', 'blocked', etc.

// Get account settings (defaults to 'whatsapp')
$settings = $client->getSettings();

// Set account settings for Telegram
$client->setSettings([
    'webhookUrl' => 'https://yourserver.com/webhook',
    'outgoingWebhook' => 'yes'
], 'telegram');

// Reboot WhatsApp account
$client->reboot('whatsapp');

// Logout Telegram account
$client->logout('telegram');
```

#### Group Management

```php
// Create WhatsApp group
$response = $client->createGroup('My Group', [
    '79999999999@c.us',
    '79999999998@c.us'
], 'whatsapp');

// Get Telegram group data
$groupData = $client->getGroupData('@groupname', 'telegram');

// Add participant (defaults to whatsapp)
$client->addGroupParticipant('GROUP_ID@g.us', '79999999997@c.us');

// Remove participant from Telegram group
$client->removeGroupParticipant('@groupname', '@username', 'telegram');

// Set group admin
$client->setGroupAdmin('GROUP_ID@g.us', '79999999999@c.us', 'whatsapp');

// Update group name
$client->updateGroupName('GROUP_ID@g.us', 'New Group Name', 'whatsapp');

// Leave group
$client->leaveGroup('GROUP_ID@g.us', 'whatsapp');
```

#### Webhook Handling

```php
// Handle incoming webhook
$webhookHandler = $client->getWebhookHandler();

// Set up webhook handlers
$webhookHandler->onIncomingMessageText(function($data) {
    echo "Received text message: " . $data['messageData']['textMessageData']['textMessage'];
});

$webhookHandler->onIncomingMessageFile(function($data) {
    echo "Received file: " . $data['messageData']['fileMessageData']['downloadUrl'];
});

$webhookHandler->onOutgoingMessageStatus(function($data) {
    echo "Message status: " . $data['statusMessage'];
});

// Process webhook (call this in your webhook endpoint)
$webhookHandler->processWebhook($_POST);
```

#### Instance Management

```php
// Get all instances (requires userId and userToken)
$instances = $client->getInstances();

// Create new instance
$response = $client->createInstance('DEVELOPER', 'infinitely');

// Extend instance
$response = $client->extendInstance(123, 'DEVELOPER', 'month1');

// Delete instance
$response = $client->deleteInstance(123);
```

#### Receiving Messages

```php
// Receive WhatsApp notification
$notification = $client->receiveNotification('whatsapp');

// Receive Telegram notification
$notification = $client->receiveNotification('telegram');

// Delete processed notification (defaults to whatsapp)
$client->deleteNotification($notification['receiptId']);

// Get chat history from Telegram
$history = $client->getChatHistory([
    'chatId' => '@username',
    'count' => 50
], 'telegram');
```

### Telegram Support

The library fully supports Telegram API integration. Simply pass `'telegram'` as the `$messengerType` parameter to any API method.

#### Telegram Authorization (Required First)

**Before using Telegram, you must authorize with your phone number:**

```php
// Step 1: Send confirmation code to your phone
$response = $client->sendConfirmationCode(712345678989, 'telegram'); // Without +
echo "Code sent! Check your Telegram app.";

// Step 2: Sign in with the code you received
$result = $client->signInWithConfirmationCode('12345', 'telegram');
echo "Authorized successfully!";

// Step 3: Verify you're authorized
$state = $client->getStateInstance('telegram');
if ($state['stateInstance'] === 'authorized') {
    echo "Ready to send messages!";
}
```

#### Telegram-Specific Methods

```php
// Create Telegram app (optional)
$app = $client->createApp(
    'My App',                    // title
    'myapp',                     // short name
    'https://myapp.com',         // URL
    'App description',           // description
    'telegram'                   // messenger type
);

// Send message via Telegram
$message = $client->sendMessage([
    'chatId' => '@username',  // Telegram username or chat ID
    'message' => 'Hello from Telegram!'
], 'telegram');
```

#### Working with Both Messengers

```php
// Same client instance can be used for both messengers
$client = new WhatsAppApiClient([
    'idInstance' => 'YOUR_INSTANCE_ID',
    'apiTokenInstance' => 'YOUR_API_TOKEN'
]);

// Send to WhatsApp
$client->sendMessage([
    'chatId' => '79999999999@c.us',
    'message' => 'Hello WhatsApp!'
], 'whatsapp');

// Send to Telegram
$client->sendMessage([
    'chatId' => '@username',
    'message' => 'Hello Telegram! 🚀'
], 'telegram');

// Send file to Telegram
$client->sendFileByUrl([
    'chatId' => '@username',
    'urlFile' => 'https://example.com/file.pdf',
    'fileName' => 'document.pdf',
    'caption' => 'Check this out!'
], 'telegram');
```

## Examples

See the `examples/` directory for complete working examples:

**WhatsApp Examples:**
- [Send Message](examples/send_message.php)
- [Send File](examples/send_file.php)
- [Create Group](examples/create_group.php)
- [Webhook Handler](examples/webhook_handler.php)
- [QR Code Authorization](examples/qr_authorization.php)
- [Instance Management](examples/instance_management.php)
- [Receive Notification](examples/receive_notification.php)

**Telegram Examples:**
- [Telegram Send Message](examples/telegram_send_message.php)
- [Telegram Create App](examples/telegram_create_app.php)
- [Telegram Authorization](examples/telegram_authorization.php)

## Error Handling

The client throws exceptions for HTTP errors and invalid responses:

```php
use SDKWA\Exceptions\WhatsAppApiException;

try {
    // Send message (defaults to 'whatsapp' if not specified)
    $response = $client->sendMessage([
        'chatId' => '79999999999@c.us',
        'message' => 'Hello!'
    ], 'whatsapp');
    
    // Or send to Telegram
    $response = $client->sendMessage([
        'chatId' => '@username',
        'message' => 'Hello!'
    ], 'telegram');
} catch (WhatsAppApiException $e) {
    echo "API Error: " . $e->getMessage();
    echo "Status Code: " . $e->getStatusCode();
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage();
}
```

## Requirements

- PHP 7.4 or higher
- ext-json
- ext-curl
- Guzzle HTTP client

## License

MIT License. See [LICENSE](LICENSE) file for details.

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Support

- [Documentation](https://docs.sdkwa.pro)
- [GitHub Issues](https://github.com/sdkwa/whatsapp-api-client-php/issues)
- [SDKWA Community](https://sdkwa.pro)
