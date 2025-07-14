# Quick Start Guide

Get up and running with WhatsApp API Client PHP in 5 minutes!

## 1. Installation

```bash
composer require sdkwa/whatsapp-api-client-php
```

## 2. Get Your Credentials

1. Sign up at [SDKWA](https://sdkwa.pro)
2. Create a new WhatsApp instance
3. Get your `idInstance` and `apiTokenInstance`

## 3. Basic Setup

Create a file `test.php`:

```php
<?php
require_once 'vendor/autoload.php';

use SDKWA\WhatsAppApiClient;

$client = new WhatsAppApiClient([
    'idInstance' => 'YOUR_INSTANCE_ID',
    'apiTokenInstance' => 'YOUR_API_TOKEN'
]);

// Check account state
$state = $client->getStateInstance();
echo "Account state: " . $state['stateInstance'] . "\n";
```

## 4. Authorization

If your account is not authorized, you need to scan a QR code:

```php
// Get QR code
$qr = $client->getQr();

if ($qr['type'] === 'qrCode') {
    // Save QR code as image
    $qrData = preg_replace('/^data:image\/png;base64,/', '', $qr['message']);
    file_put_contents('qr.png', base64_decode($qrData));
    echo "QR code saved as qr.png - scan it with WhatsApp!\n";
}
```

## 5. Send Your First Message

```php
try {
    $response = $client->sendMessage([
        'chatId' => '79999999999@c.us', // Replace with actual phone number
        'message' => 'Hello from PHP! 🚀'
    ]);
    
    echo "Message sent! ID: " . $response['idMessage'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## 6. Handle Incoming Messages

Create a webhook endpoint `webhook.php`:

```php
<?php
require_once 'vendor/autoload.php';

use SDKWA\WhatsAppApiClient;

$client = new WhatsAppApiClient([
    'idInstance' => 'YOUR_INSTANCE_ID',
    'apiTokenInstance' => 'YOUR_API_TOKEN'
]);

$webhookHandler = $client->getWebhookHandler();

// Auto-reply to incoming messages
$webhookHandler->onIncomingMessageText(function($data) use ($client) {
    $chatId = $data['senderData']['chatId'];
    $message = $data['messageData']['textMessageData']['textMessage'];
    
    // Simple auto-reply
    $client->sendMessage([
        'chatId' => $chatId,
        'message' => "You said: {$message}"
    ]);
});

// Process webhook
$input = file_get_contents('php://input');
$webhookData = json_decode($input, true);
$webhookHandler->processWebhook($webhookData);

echo 'OK';
```

## 7. Set Up Webhook URL

Configure your webhook URL in SDKWA dashboard:

```php
$client->setSettings([
    'webhookUrl' => 'https://yourserver.com/webhook.php',
    'outgoingWebhook' => 'yes',
    'incomingWebhook' => 'yes'
]);
```

## 8. Advanced Features

### Send Files

```php
// Send a file
$client->sendFileByUpload([
    'chatId' => '79999999999@c.us',
    'file' => '/path/to/image.jpg',
    'fileName' => 'photo.jpg',
    'caption' => 'Check this out!'
]);
```

### Create Groups

```php
$response = $client->createGroup('My PHP Group', [
    '79999999999@c.us',
    '79999999998@c.us'
]);

echo "Group created: " . $response['chatId'] . "\n";
```

### Send Locations

```php
$client->sendLocation([
    'chatId' => '79999999999@c.us',
    'latitude' => 51.5074,
    'longitude' => -0.1278,
    'nameLocation' => 'London Eye',
    'address' => 'London, UK'
]);
```

## 9. Error Handling

Always wrap API calls in try-catch:

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

## 10. Next Steps

- 📖 Read the [full documentation](docs/API.md)
- 🔍 Explore the [examples](examples/) directory
- 🤖 Build a [complete bot](examples/whatsapp_bot.php)
- 🌐 Set up [webhook endpoints](examples/webhook_endpoint.php)

## Common Chat ID Formats

- **Personal chat**: `79999999999@c.us`
- **Group chat**: `79999999999-1234567890@g.us`
- **Broadcast**: `79999999999@broadcast`

## Tips

1. **Test with your own number first**
2. **Use the examples** to understand the workflow
3. **Check account state** before sending messages
4. **Handle webhooks** for real-time interactions
5. **Monitor API logs** for debugging

## Support

- 📚 [Documentation](docs/API.md)
- 💬 [GitHub Issues](https://github.com/sdkwa/whatsapp-api-client-php/issues)
- 🌐 [SDKWA Support](https://sdkwa.pro)

Happy coding! 🎉
