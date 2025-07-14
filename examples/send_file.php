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

    // Send file by upload (local file)
    $response = $client->sendFileByUpload([
        'chatId' => '79999999999@c.us',
        'file' => '/path/to/your/file.jpg', // Local file path
        'fileName' => 'my-image.jpg',
        'caption' => 'Check out this image uploaded from PHP!'
    ]);

    echo "File sent by upload!" . PHP_EOL;
    echo "Message ID: " . $response['idMessage'] . PHP_EOL;

    // Send file by URL
    $response = $client->sendFileByUrl([
        'chatId' => '79999999999@c.us',
        'urlFile' => 'https://via.placeholder.com/300x200.png',
        'fileName' => 'placeholder.png',
        'caption' => 'This image was sent via URL from PHP SDK'
    ]);

    echo "File sent by URL!" . PHP_EOL;
    echo "Message ID: " . $response['idMessage'] . PHP_EOL;

    // Upload file first, then send
    $uploadResponse = $client->uploadFile('/path/to/your/document.pdf');
    echo "File uploaded to: " . $uploadResponse['urlFile'] . PHP_EOL;

    $response = $client->sendFileByUrl([
        'chatId' => '79999999999@c.us',
        'urlFile' => $uploadResponse['urlFile'],
        'fileName' => 'document.pdf',
        'caption' => 'Document uploaded and sent via PHP SDK'
    ]);

    echo "Uploaded file sent!" . PHP_EOL;
    echo "Message ID: " . $response['idMessage'] . PHP_EOL;

} catch (WhatsAppApiException $e) {
    echo "WhatsApp API Error: " . $e->getMessage() . PHP_EOL;
    echo "Status Code: " . $e->getStatusCode() . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
