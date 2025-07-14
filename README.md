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

## Quick Start

```php
<?php
require_once 'vendor/autoload.php';

use SDKWA\WhatsAppApiClient;

$client = new WhatsAppApiClient([
    'apiHost' => 'https://api.sdkwa.pro', // optional
    'idInstance' => 'YOUR_INSTANCE_ID',
    'apiTokenInstance' => 'YOUR_API_TOKEN'
]);

// Send a message
$response = $client->sendMessage([
    'chatId' => '79999999999@c.us',
    'message' => 'Hello, World!'
]);

echo "Message sent with ID: " . $response['idMessage'];
```

## Features

- ✅ Send text messages
- ✅ Send files (by upload or URL)
- ✅ Send contacts and locations
- ✅ Manage groups
- ✅ Handle webhooks
- ✅ Account management
- ✅ QR code authorization
- ✅ Instance management
- ✅ Full API coverage

## Documentation

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

#### Send Messages

```php
// Send text message
$response = $client->sendMessage([
    'chatId' => '79999999999@c.us',
    'message' => 'Hello, World!',
    'quotedMessageId' => 'optional_quoted_message_id'
]);

// Send file by upload
$response = $client->sendFileByUpload([
    'chatId' => '79999999999@c.us',
    'file' => '/path/to/file.jpg',
    'fileName' => 'image.jpg',
    'caption' => 'Check out this image!'
]);

// Send file by URL
$response = $client->sendFileByUrl([
    'chatId' => '79999999999@c.us',
    'urlFile' => 'https://example.com/file.pdf',
    'fileName' => 'document.pdf',
    'caption' => 'Important document'
]);

// Send contact
$response = $client->sendContact([
    'chatId' => '79999999999@c.us',
    'contact' => [
        'phoneContact' => 79999999999,
        'firstName' => 'John',
        'lastName' => 'Doe'
    ]
]);

// Send location
$response = $client->sendLocation([
    'chatId' => '79999999999@c.us',
    'latitude' => 51.5074,
    'longitude' => -0.1278,
    'nameLocation' => 'London',
    'address' => 'London, UK'
]);
```

#### Account Management

```php
// Get account state
$state = $client->getStateInstance();

// Get QR code for authorization
$qr = $client->getQr();

// Get account settings
$settings = $client->getSettings();

// Set account settings
$client->setSettings([
    'webhookUrl' => 'https://yourserver.com/webhook',
    'outgoingWebhook' => 'yes'
]);

// Reboot account
$client->reboot();

// Logout account
$client->logout();
```

#### Group Management

```php
// Create group
$response = $client->createGroup('My Group', [
    '79999999999@c.us',
    '79999999998@c.us'
]);

// Get group data
$groupData = $client->getGroupData('GROUP_ID@g.us');

// Add participant
$client->addGroupParticipant('GROUP_ID@g.us', '79999999997@c.us');

// Remove participant
$client->removeGroupParticipant('GROUP_ID@g.us', '79999999997@c.us');

// Set group admin
$client->setGroupAdmin('GROUP_ID@g.us', '79999999999@c.us');

// Update group name
$client->updateGroupName('GROUP_ID@g.us', 'New Group Name');

// Leave group
$client->leaveGroup('GROUP_ID@g.us');
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
// Receive notification
$notification = $client->receiveNotification();

// Delete processed notification
$client->deleteNotification($notification['receiptId']);

// Get chat history
$history = $client->getChatHistory([
    'chatId' => '79999999999@c.us',
    'count' => 50
]);
```

## Examples

See the `examples/` directory for complete working examples:

- [Send Message](examples/send_message.php)
- [Send File](examples/send_file.php)
- [Create Group](examples/create_group.php)
- [Webhook Handler](examples/webhook_handler.php)
- [QR Code Authorization](examples/qr_authorization.php)
- [Instance Management](examples/instance_management.php)

## Error Handling

The client throws exceptions for HTTP errors and invalid responses:

```php
use SDKWA\Exceptions\WhatsAppApiException;

try {
    $response = $client->sendMessage([
        'chatId' => '79999999999@c.us',
        'message' => 'Hello!'
    ]);
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
