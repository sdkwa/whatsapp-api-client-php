<?php

/**
 * WhatsApp Bot Example
 *
 * This example demonstrates how to create a comprehensive WhatsApp bot
 * that can handle various types of messages and commands.
 */

require_once '../vendor/autoload.php';

use SDKWA\WhatsAppApiClient;
use SDKWA\Exceptions\WhatsAppApiException;

class WhatsAppBot
{
    private WhatsAppApiClient $client;
    private array $commands = [];
    private array $userSessions = [];

    public function __construct(array $config)
    {
        $this->client = new WhatsAppApiClient($config);
        $this->setupCommands();
        $this->setupWebhookHandlers();
    }

    private function setupCommands(): void
    {
        $this->commands = [
            '/start' => 'Welcome to our WhatsApp Bot! 🤖',
            '/help' => $this->getHelpMessage(),
            '/time' => fn() => 'Current time: ' . date('Y-m-d H:i:s T'),
            '/weather' => fn($args) => $this->getWeather($args[0] ?? 'London'),
            '/joke' => fn() => $this->getRandomJoke(),
            '/qr' => fn($args) => $this->generateQRCode($args[0] ?? 'Hello World'),
            '/quote' => fn() => $this->getRandomQuote(),
            '/ping' => '🏓 Pong!',
            '/status' => '✅ Bot is online and working properly!',
            '/menu' => $this->getMenuMessage(),
        ];
    }

    private function setupWebhookHandlers(): void
    {
        $handler = $this->client->getWebhookHandler();

        $handler->onIncomingMessageText(function ($data) {
            $this->handleTextMessage($data);
        });

        $handler->onIncomingMessageFile(function ($data) {
            $this->handleFileMessage($data);
        });

        $handler->onIncomingMessageLocation(function ($data) {
            $this->handleLocationMessage($data);
        });

        $handler->onIncomingMessageContact(function ($data) {
            $this->handleContactMessage($data);
        });
    }

    private function handleTextMessage(array $data): void
    {
        $senderId = $data['senderData']['sender'];
        $chatId = $data['senderData']['chatId'];
        $message = trim($data['messageData']['textMessageData']['textMessage']);
        $senderName = $data['senderData']['senderName'] ?? 'Unknown';

        // Log the message
        $this->log("Text message from {$senderName} ({$senderId}): {$message}");

        // Check if it's a command
        if (strpos($message, '/') === 0) {
            $this->handleCommand($chatId, $message);
        } else {
            $this->handleRegularMessage($chatId, $message, $senderId);
        }
    }

    private function handleCommand(string $chatId, string $message): void
    {
        $parts = explode(' ', $message);
        $command = $parts[0];
        $args = array_slice($parts, 1);

        if (isset($this->commands[$command])) {
            $response = $this->commands[$command];

            if (is_callable($response)) {
                $response = $response($args);
            }

            $this->sendMessage($chatId, $response);
        } else {
            $this->sendMessage($chatId, "Unknown command: {$command}\nType /help for available commands.");
        }
    }

    private function handleRegularMessage(string $chatId, string $message, string $senderId): void
    {
        $lowerMessage = strtolower($message);

        // Greeting responses
        if (in_array($lowerMessage, ['hello', 'hi', 'hey', 'good morning', 'good evening'])) {
            $this->sendMessage($chatId, "Hello! 👋 Thanks for contacting us. How can I help you today?\n\nType /menu to see available options.");
            return;
        }

        // Thank you responses
        if (in_array($lowerMessage, ['thank you', 'thanks', 'thx'])) {
            $this->sendMessage($chatId, "You're welcome! 😊 Is there anything else I can help you with?");
            return;
        }

        // Goodbye responses
        if (in_array($lowerMessage, ['bye', 'goodbye', 'see you', 'farewell'])) {
            $this->sendMessage($chatId, "Goodbye! 👋 Feel free to message us anytime. Have a great day!");
            return;
        }

        // Question detection
        if (strpos($lowerMessage, '?') !== false) {
            $this->sendMessage($chatId, "I see you have a question! 🤔 While I'm still learning, you can try these commands:\n\n" . $this->getHelpMessage());
            return;
        }

        // Default response
        $this->sendMessage($chatId, "I received your message: \"{$message}\"\n\nI'm a bot and still learning! Type /help to see what I can do. 🤖");
    }

    private function handleFileMessage(array $data): void
    {
        $senderId = $data['senderData']['sender'];
        $chatId = $data['senderData']['chatId'];
        $fileName = $data['messageData']['fileMessageData']['fileName'];
        $fileUrl = $data['messageData']['fileMessageData']['downloadUrl'];
        $caption = $data['messageData']['fileMessageData']['caption'] ?? '';

        $this->log("File message from {$senderId}: {$fileName}");

        // Determine file type
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $response = $this->getFileResponse($extension, $fileName);

        if ($caption) {
            $response .= "\n\nCaption: {$caption}";
        }

        $this->sendMessage($chatId, $response);
    }

    private function handleLocationMessage(array $data): void
    {
        $senderId = $data['senderData']['sender'];
        $chatId = $data['senderData']['chatId'];
        $locationName = $data['messageData']['locationMessageData']['nameLocation'];
        $address = $data['messageData']['locationMessageData']['address'];
        $latitude = $data['messageData']['locationMessageData']['latitude'];
        $longitude = $data['messageData']['locationMessageData']['longitude'];

        $this->log("Location message from {$senderId}: {$locationName}");

        $response = "📍 Thanks for sharing your location!\n\n";
        $response .= "📌 **{$locationName}**\n";
        $response .= "🏠 Address: {$address}\n";
        $response .= "🌐 Coordinates: {$latitude}, {$longitude}\n\n";
        $response .= "You can view this location on Google Maps: https://maps.google.com/?q={$latitude},{$longitude}";

        $this->sendMessage($chatId, $response);
    }

    private function handleContactMessage(array $data): void
    {
        $senderId = $data['senderData']['sender'];
        $chatId = $data['senderData']['chatId'];
        $contactName = $data['messageData']['contactMessageData']['displayName'];

        $this->log("Contact message from {$senderId}: {$contactName}");

        $response = "👤 Thanks for sharing the contact: **{$contactName}**\n\n";
        $response .= "I've received the contact information. Is there anything specific you'd like me to help you with regarding this contact?";

        $this->sendMessage($chatId, $response);
    }

    private function getFileResponse(string $extension, string $fileName): string
    {
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $videoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv'];
        $audioTypes = ['mp3', 'wav', 'ogg', 'aac'];
        $documentTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];

        if (in_array($extension, $imageTypes)) {
            return "🖼️ Nice image! I received your photo: **{$fileName}**\n\nI can see images but I'm still learning to analyze them. Soon I'll be able to describe what I see!";
        } elseif (in_array($extension, $videoTypes)) {
            return "🎥 Video received: **{$fileName}**\n\nI got your video! While I can't play it, I can confirm it was delivered successfully.";
        } elseif (in_array($extension, $audioTypes)) {
            return "🎵 Audio file received: **{$fileName}**\n\nI received your audio message! I can receive audio files but I'm still learning to process them.";
        } elseif (in_array($extension, $documentTypes)) {
            return "📄 Document received: **{$fileName}**\n\nI got your document! I can receive various document types. Is there anything specific you'd like me to help you with regarding this document?";
        } else {
            return "📁 File received: **{$fileName}**\n\nI received your file successfully! While I can't process all file types yet, I can confirm it was delivered.";
        }
    }

    private function getHelpMessage(): string
    {
        return "🤖 **Available Commands:**\n\n" .
               "🏁 /start - Welcome message\n" .
               "❓ /help - Show this help\n" .
               "⏰ /time - Get current time\n" .
               "🌤️ /weather [city] - Get weather info\n" .
               "😂 /joke - Get a random joke\n" .
               "📱 /qr [text] - Generate QR code\n" .
               "💬 /quote - Get inspirational quote\n" .
               "🏓 /ping - Test bot response\n" .
               "✅ /status - Check bot status\n" .
               "📋 /menu - Show main menu\n\n" .
               "You can also send me files, locations, or just chat normally!";
    }

    private function getMenuMessage(): string
    {
        return "📋 **Main Menu**\n\n" .
               "What would you like to do?\n\n" .
               "1️⃣ Get help - Type /help\n" .
               "2️⃣ Check weather - Type /weather [city]\n" .
               "3️⃣ Get a joke - Type /joke\n" .
               "4️⃣ Generate QR code - Type /qr [text]\n" .
               "5️⃣ Get quote - Type /quote\n" .
               "6️⃣ Check time - Type /time\n\n" .
               "Or just send me a message and I'll try to help! 😊";
    }

    private function getWeather(string $city): string
    {
        // This is a mock implementation
        // In a real bot, you'd integrate with a weather API
        $weather = [
            'london' => '🌤️ London: 15°C, Partly cloudy',
            'paris' => '☀️ Paris: 18°C, Sunny',
            'tokyo' => '🌧️ Tokyo: 22°C, Rainy',
            'new york' => '⛅ New York: 12°C, Cloudy',
            'sydney' => '☀️ Sydney: 25°C, Sunny'
        ];

        $cityLower = strtolower($city);
        return $weather[$cityLower] ?? "🌍 Weather for {$city}: Data not available. Try: London, Paris, Tokyo, New York, or Sydney";
    }

    private function getRandomJoke(): string
    {
        $jokes = [
            "Why don't scientists trust atoms? Because they make up everything! 😄",
            "Why did the scarecrow win an award? He was outstanding in his field! 🌾",
            "Why don't eggs tell jokes? They'd crack each other up! 🥚",
            "What do you call a fake noodle? An impasta! 🍝",
            "Why did the math book look so sad? Because it had too many problems! 📚"
        ];

        return $jokes[array_rand($jokes)];
    }

    private function generateQRCode(string $text): string
    {
        // This is a mock implementation
        // In a real bot, you'd generate an actual QR code
        return "📱 QR Code generated for: \"{$text}\"\n\n" .
               "To implement actual QR code generation, you can use libraries like:\n" .
               "- endroid/qr-code\n" .
               "- bacon/bacon-qr-code\n\n" .
               "The QR code would contain: {$text}";
    }

    private function getRandomQuote(): string
    {
        $quotes = [
            "\"The only way to do great work is to love what you do.\" - Steve Jobs 💼",
            "\"Innovation distinguishes between a leader and a follower.\" - Steve Jobs 🚀",
            "\"Life is what happens to you while you're busy making other plans.\" - John Lennon 🎵",
            "\"The future belongs to those who believe in the beauty of their dreams.\" - Eleanor Roosevelt 🌟",
            "\"It is during our darkest moments that we must focus to see the light.\" - Aristotle 💡"
        ];

        return $quotes[array_rand($quotes)];
    }

    private function sendMessage(string $chatId, string $message): void
    {
        try {
            $this->client->sendMessage([
                'chatId' => $chatId,
                'message' => $message
            ]);
        } catch (WhatsAppApiException $e) {
            $this->log("Error sending message: " . $e->getMessage());
        }
    }

    private function log(string $message): void
    {
        echo "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL;
    }

    public function processWebhook(array $data): void
    {
        $this->client->getWebhookHandler()->processWebhook($data);
    }

    public function getClient(): WhatsAppApiClient
    {
        return $this->client;
    }
}

// Example usage
if (php_sapi_name() === 'cli') {
    // Running from command line - test mode
    echo "WhatsApp Bot Example\n";
    echo "===================\n\n";

    $bot = new WhatsAppBot([
        'apiHost' => $_ENV['API_HOST'] ?? 'https://api.sdkwa.pro',
        'idInstance' => $_ENV['ID_INSTANCE'] ?? 'YOUR_INSTANCE_ID',
        'apiTokenInstance' => $_ENV['API_TOKEN_INSTANCE'] ?? 'YOUR_API_TOKEN'
    ]);

    // Test with sample webhook data
    $sampleData = [
        'typeWebhook' => 'incomingMessageReceived',
        'senderData' => [
            'sender' => '79999999999@c.us',
            'chatId' => '79999999999@c.us',
            'senderName' => 'Test User'
        ],
        'messageData' => [
            'typeMessage' => 'textMessage',
            'textMessageData' => [
                'textMessage' => '/help'
            ]
        ]
    ];

    echo "Testing bot with sample data...\n";
    $bot->processWebhook($sampleData);
} else {
    // Running from web server - webhook mode
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $bot = new WhatsAppBot([
            'apiHost' => $_ENV['API_HOST'] ?? 'https://api.sdkwa.pro',
            'idInstance' => $_ENV['ID_INSTANCE'] ?? 'YOUR_INSTANCE_ID',
            'apiTokenInstance' => $_ENV['API_TOKEN_INSTANCE'] ?? 'YOUR_API_TOKEN'
        ]);

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if ($data) {
            $bot->processWebhook($data);
            echo json_encode(['status' => 'success']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid webhook data']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
}
