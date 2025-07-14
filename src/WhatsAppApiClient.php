<?php

namespace SDKWA;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use SDKWA\Exceptions\WhatsAppApiException;
use SDKWA\WebhookHandler;

/**
 * WhatsApp API Client for SDKWA
 */
class WhatsAppApiClient
{
    private string $apiHost;
    private string $idInstance;
    private string $apiTokenInstance;
    private ?string $userId;
    private ?string $userToken;
    private string $basePath;
    private array $headers;
    private Client $httpClient;
    private WebhookHandler $webhookHandler;

    /**
     * Constructor
     *
     * @param array $options Configuration options
     * @throws WhatsAppApiException
     */
    public function __construct(array $options)
    {
        $this->validateOptions($options);

        $this->apiHost = rtrim($options['apiHost'] ?? 'https://api.sdkwa.pro', '/');
        $this->idInstance = $options['idInstance'];
        $this->apiTokenInstance = $options['apiTokenInstance'];
        $this->userId = $options['userId'] ?? null;
        $this->userToken = $options['userToken'] ?? null;
        $this->basePath = "/whatsapp/{$this->idInstance}";
        $this->headers = [
            'Authorization' => "Bearer {$this->apiTokenInstance}",
            'Content-Type' => 'application/json',
            'User-Agent' => 'SDKWA-PHP-Client/1.0'
        ];

        $this->httpClient = new Client([
            'base_uri' => $this->apiHost,
            'timeout' => 30,
            'verify' => false
        ]);

        $this->webhookHandler = new WebhookHandler();
    }

    /**
     * Validate constructor options
     *
     * @param array $options
     * @throws WhatsAppApiException
     */
    private function validateOptions(array $options): void
    {
        if (empty($options['idInstance']) || !is_string($options['idInstance'])) {
            throw new WhatsAppApiException('idInstance is required and must be a non-empty string');
        }

        if (empty($options['apiTokenInstance']) || !is_string($options['apiTokenInstance'])) {
            throw new WhatsAppApiException('apiTokenInstance is required and must be a non-empty string');
        }
    }

    /**
     * Make HTTP request
     *
     * @param string $method
     * @param string $path
     * @param array $options
     * @return array
     * @throws WhatsAppApiException
     */
    private function request(string $method, string $path, array $options = []): array
    {
        $config = [
            'headers' => array_merge($this->headers, $options['headers'] ?? [])
        ];

        if (isset($options['json'])) {
            $config['json'] = $options['json'];
        }

        if (isset($options['multipart'])) {
            $config['multipart'] = $options['multipart'];
            unset($config['headers']['Content-Type']); // Let Guzzle set the correct Content-Type
        }

        if (isset($options['query'])) {
            $config['query'] = $options['query'];
        }

        try {
            $response = $this->httpClient->request($method, $path, $config);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new WhatsAppApiException('Invalid JSON response: ' . json_last_error_msg());
            }

            return $data;
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
            $errorMessage = $e->getMessage();

            if ($e->getResponse()) {
                $errorBody = $e->getResponse()->getBody()->getContents();
                $errorData = json_decode($errorBody, true);
                if ($errorData && isset($errorData['message'])) {
                    $errorMessage = $errorData['message'];
                }
            }

            throw new WhatsAppApiException($errorMessage, $statusCode, $e);
        }
    }

    /**
     * Get webhook handler
     *
     * @return WebhookHandler
     */
    public function getWebhookHandler(): WebhookHandler
    {
        return $this->webhookHandler;
    }

    // --- Account methods ---

    /**
     * Get current account settings
     *
     * @return array
     * @throws WhatsAppApiException
     */
    public function getSettings(): array
    {
        return $this->request('GET', $this->basePath . '/getSettings');
    }

    /**
     * Set account settings
     *
     * @param array $settings
     * @return array
     * @throws WhatsAppApiException
     */
    public function setSettings(array $settings): array
    {
        return $this->request('POST', $this->basePath . '/setSettings', [
            'json' => $settings
        ]);
    }

    /**
     * Get account state
     *
     * @return array
     * @throws WhatsAppApiException
     */
    public function getStateInstance(): array
    {
        return $this->request('GET', $this->basePath . '/getStateInstance');
    }

    /**
     * Get warming phone status
     *
     * @return array
     * @throws WhatsAppApiException
     */
    public function getWarmingPhoneStatus(): array
    {
        return $this->request('GET', $this->basePath . '/getWarmingPhoneStatus');
    }

    /**
     * Reboot account
     *
     * @return array
     * @throws WhatsAppApiException
     */
    public function reboot(): array
    {
        return $this->request('GET', $this->basePath . '/reboot');
    }

    /**
     * Logout account
     *
     * @return array
     * @throws WhatsAppApiException
     */
    public function logout(): array
    {
        return $this->request('GET', $this->basePath . '/logout');
    }

    /**
     * Get QR code for account authorization
     *
     * @return array
     * @throws WhatsAppApiException
     */
    public function getQr(): array
    {
        return $this->request('GET', $this->basePath . '/qr');
    }

    /**
     * Get authorization code for account authorization
     *
     * @param array $params
     * @return array
     * @throws WhatsAppApiException
     */
    public function getAuthorizationCode(array $params): array
    {
        return $this->request('POST', $this->basePath . '/getAuthorizationCode', [
            'json' => $params
        ]);
    }

    /**
     * Request registration code
     *
     * @param array $params
     * @return array
     * @throws WhatsAppApiException
     */
    public function requestRegistrationCode(array $params): array
    {
        return $this->request('POST', $this->basePath . '/requestRegistrationCode', [
            'json' => $params
        ]);
    }

    /**
     * Send registration code
     *
     * @param array $params
     * @return array
     * @throws WhatsAppApiException
     */
    public function sendRegistrationCode(array $params): array
    {
        return $this->request('POST', $this->basePath . '/sendRegistrationCode', [
            'json' => $params
        ]);
    }

    // --- Sending methods ---

    /**
     * Send message
     *
     * @param array $params
     * @return array
     * @throws WhatsAppApiException
     */
    public function sendMessage(array $params): array
    {
        return $this->request('POST', $this->basePath . '/sendMessage', [
            'json' => $params
        ]);
    }

    /**
     * Send contact
     *
     * @param array $params
     * @return array
     * @throws WhatsAppApiException
     */
    public function sendContact(array $params): array
    {
        return $this->request('POST', $this->basePath . '/sendContact', [
            'json' => $params
        ]);
    }

    /**
     * Send file by upload
     *
     * @param array $params
     * @return array
     * @throws WhatsAppApiException
     */
    public function sendFileByUpload(array $params): array
    {
        $multipart = [
            [
                'name' => 'chatId',
                'contents' => $params['chatId']
            ]
        ];

        // Handle file
        if (is_string($params['file'])) {
            // File path
            $multipart[] = [
                'name' => 'file',
                'contents' => fopen($params['file'], 'r'),
                'filename' => $params['fileName']
            ];
        } else {
            // File content
            $multipart[] = [
                'name' => 'file',
                'contents' => $params['file'],
                'filename' => $params['fileName']
            ];
        }

        if (isset($params['caption'])) {
            $multipart[] = [
                'name' => 'caption',
                'contents' => $params['caption']
            ];
        }

        if (isset($params['quotedMessageId'])) {
            $multipart[] = [
                'name' => 'quotedMessageId',
                'contents' => $params['quotedMessageId']
            ];
        }

        return $this->request('POST', $this->basePath . '/sendFileByUpload', [
            'multipart' => $multipart
        ]);
    }

    /**
     * Send file by URL
     *
     * @param array $params
     * @return array
     * @throws WhatsAppApiException
     */
    public function sendFileByUrl(array $params): array
    {
        return $this->request('POST', $this->basePath . '/sendFileByUrl', [
            'json' => $params
        ]);
    }

    /**
     * Send location
     *
     * @param array $params
     * @return array
     * @throws WhatsAppApiException
     */
    public function sendLocation(array $params): array
    {
        return $this->request('POST', $this->basePath . '/sendLocation', [
            'json' => $params
        ]);
    }

    /**
     * Upload file
     *
     * @param mixed $file
     * @return array
     * @throws WhatsAppApiException
     */
    public function uploadFile($file): array
    {
        $multipart = [];

        if (is_string($file)) {
            // File path
            $multipart[] = [
                'name' => 'file',
                'contents' => fopen($file, 'r'),
                'filename' => basename($file)
            ];
        } else {
            // File content
            $multipart[] = [
                'name' => 'file',
                'contents' => $file,
                'filename' => 'file'
            ];
        }

        return $this->request('POST', $this->basePath . '/uploadFile', [
            'multipart' => $multipart
        ]);
    }

    /**
     * Get chat history
     *
     * @param array $params
     * @return array
     * @throws WhatsAppApiException
     */
    public function getChatHistory(array $params): array
    {
        return $this->request('POST', $this->basePath . '/getChatHistory', [
            'json' => $params
        ]);
    }

    // --- Receiving methods ---

    /**
     * Receive notification
     *
     * @return array
     * @throws WhatsAppApiException
     */
    public function receiveNotification(): array
    {
        return $this->request('GET', $this->basePath . '/receiveNotification');
    }

    /**
     * Delete notification
     *
     * @param int $receiptId
     * @return array
     * @throws WhatsAppApiException
     */
    public function deleteNotification(int $receiptId): array
    {
        return $this->request('DELETE', $this->basePath . "/deleteNotification/{$receiptId}");
    }

    // --- Chat/Contact methods ---

    /**
     * Get contacts
     *
     * @return array
     * @throws WhatsAppApiException
     */
    public function getContacts(): array
    {
        return $this->request('GET', $this->basePath . '/getContacts');
    }

    /**
     * Get chats
     *
     * @return array
     * @throws WhatsAppApiException
     */
    public function getChats(): array
    {
        return $this->request('GET', $this->basePath . '/getChats');
    }

    /**
     * Get contact information
     *
     * @param string $chatId
     * @return array
     * @throws WhatsAppApiException
     */
    public function getContactInfo(string $chatId): array
    {
        return $this->request('GET', $this->basePath . '/getContactInfo', [
            'json' => ['chatId' => $chatId]
        ]);
    }

    /**
     * Set profile picture
     *
     * @param mixed $file
     * @return array
     * @throws WhatsAppApiException
     */
    public function setProfilePicture($file): array
    {
        $multipart = [];

        if (is_string($file)) {
            // File path
            $multipart[] = [
                'name' => 'file',
                'contents' => fopen($file, 'r'),
                'filename' => basename($file)
            ];
        } else {
            // File content
            $multipart[] = [
                'name' => 'file',
                'contents' => $file,
                'filename' => 'profile.jpg'
            ];
        }

        return $this->request('POST', $this->basePath . '/setProfilePicture', [
            'multipart' => $multipart
        ]);
    }

    /**
     * Set profile name
     *
     * @param string $name
     * @return array
     * @throws WhatsAppApiException
     */
    public function setProfileName(string $name): array
    {
        return $this->request('POST', $this->basePath . '/setProfileName', [
            'json' => ['name' => $name]
        ]);
    }

    /**
     * Set profile status
     *
     * @param string $status
     * @return array
     * @throws WhatsAppApiException
     */
    public function setProfileStatus(string $status): array
    {
        return $this->request('POST', $this->basePath . '/setProfileStatus', [
            'json' => ['status' => $status]
        ]);
    }

    /**
     * Get avatar
     *
     * @param string $chatId
     * @return array
     * @throws WhatsAppApiException
     */
    public function getAvatar(string $chatId): array
    {
        return $this->request('POST', $this->basePath . '/getAvatar', [
            'json' => ['chatId' => $chatId]
        ]);
    }

    /**
     * Check WhatsApp account availability
     *
     * @param int $phoneNumber
     * @return array
     * @throws WhatsAppApiException
     */
    public function checkWhatsapp(int $phoneNumber): array
    {
        return $this->request('POST', $this->basePath . '/checkWhatsapp', [
            'json' => ['phoneNumber' => $phoneNumber]
        ]);
    }

    // --- Group methods ---

    /**
     * Update group name
     *
     * @param string $groupId
     * @param string $groupName
     * @return array
     * @throws WhatsAppApiException
     */
    public function updateGroupName(string $groupId, string $groupName): array
    {
        return $this->request('POST', $this->basePath . '/updateGroupName', [
            'json' => ['groupId' => $groupId, 'groupName' => $groupName]
        ]);
    }

    /**
     * Get group chat data
     *
     * @param string $groupId
     * @return array
     * @throws WhatsAppApiException
     */
    public function getGroupData(string $groupId): array
    {
        return $this->request('POST', $this->basePath . '/getGroupData', [
            'json' => ['groupId' => $groupId]
        ]);
    }

    /**
     * Leave group chat
     *
     * @param string $groupId
     * @return array
     * @throws WhatsAppApiException
     */
    public function leaveGroup(string $groupId): array
    {
        return $this->request('POST', $this->basePath . '/leaveGroup', [
            'json' => ['groupId' => $groupId]
        ]);
    }

    /**
     * Set group participant as administrator
     *
     * @param string $groupId
     * @param string $participantChatId
     * @return array
     * @throws WhatsAppApiException
     */
    public function setGroupAdmin(string $groupId, string $participantChatId): array
    {
        return $this->request('POST', $this->basePath . '/setGroupAdmin', [
            'json' => ['groupId' => $groupId, 'participantChatId' => $participantChatId]
        ]);
    }

    /**
     * Remove group participant
     *
     * @param string $groupId
     * @param string $participantChatId
     * @return array
     * @throws WhatsAppApiException
     */
    public function removeGroupParticipant(string $groupId, string $participantChatId): array
    {
        return $this->request('POST', $this->basePath . '/removeGroupParticipant', [
            'json' => ['groupId' => $groupId, 'participantChatId' => $participantChatId]
        ]);
    }

    /**
     * Remove group admin rights
     *
     * @param string $groupId
     * @param string $participantChatId
     * @return array
     * @throws WhatsAppApiException
     */
    public function removeAdmin(string $groupId, string $participantChatId): array
    {
        return $this->request('POST', $this->basePath . '/removeAdmin', [
            'json' => ['groupId' => $groupId, 'participantChatId' => $participantChatId]
        ]);
    }

    /**
     * Create group chat
     *
     * @param string $groupName
     * @param array $chatIds
     * @return array
     * @throws WhatsAppApiException
     */
    public function createGroup(string $groupName, array $chatIds): array
    {
        return $this->request('POST', $this->basePath . '/createGroup', [
            'json' => ['groupName' => $groupName, 'chatIds' => $chatIds]
        ]);
    }

    /**
     * Add participant to group chat
     *
     * @param string $groupId
     * @param string $participantChatId
     * @return array
     * @throws WhatsAppApiException
     */
    public function addGroupParticipant(string $groupId, string $participantChatId): array
    {
        return $this->request('POST', $this->basePath . '/addGroupParticipant', [
            'json' => ['groupId' => $groupId, 'participantChatId' => $participantChatId]
        ]);
    }

    /**
     * Set group picture
     *
     * @param string $groupId
     * @param mixed $file
     * @return array
     * @throws WhatsAppApiException
     */
    public function setGroupPicture(string $groupId, $file): array
    {
        $multipart = [
            [
                'name' => 'groupId',
                'contents' => $groupId
            ]
        ];

        if (is_string($file)) {
            // File path
            $multipart[] = [
                'name' => 'file',
                'contents' => fopen($file, 'r'),
                'filename' => basename($file)
            ];
        } else {
            // File content
            $multipart[] = [
                'name' => 'file',
                'contents' => $file,
                'filename' => 'group.jpg'
            ];
        }

        return $this->request('POST', $this->basePath . '/setGroupPicture', [
            'multipart' => $multipart
        ]);
    }

    // --- Read mark ---

    /**
     * Mark chat messages as read
     *
     * @param array $params
     * @return array
     * @throws WhatsAppApiException
     */
    public function readChat(array $params): array
    {
        return $this->request('POST', $this->basePath . '/readChat', [
            'json' => $params
        ]);
    }

    // --- Archive/Unarchive ---

    /**
     * Archive chat
     *
     * @param string $chatId
     * @return array
     * @throws WhatsAppApiException
     */
    public function archiveChat(string $chatId): array
    {
        return $this->request('POST', $this->basePath . '/archiveChat', [
            'json' => ['chatId' => $chatId]
        ]);
    }

    /**
     * Unarchive chat
     *
     * @param string $chatId
     * @return array
     * @throws WhatsAppApiException
     */
    public function unarchiveChat(string $chatId): array
    {
        return $this->request('POST', $this->basePath . '/unarchiveChat', [
            'json' => ['chatId' => $chatId]
        ]);
    }

    // --- Message deletion ---

    /**
     * Delete message
     *
     * @param string $chatId
     * @param string $idMessage
     * @return array
     * @throws WhatsAppApiException
     */
    public function deleteMessage(string $chatId, string $idMessage): array
    {
        return $this->request('POST', $this->basePath . '/deleteMessage', [
            'json' => ['chatId' => $chatId, 'idMessage' => $idMessage]
        ]);
    }

    // --- Queue ---

    /**
     * Clear messages queue
     *
     * @return array
     * @throws WhatsAppApiException
     */
    public function clearMessagesQueue(): array
    {
        return $this->request('GET', $this->basePath . '/clearMessagesQueue');
    }

    /**
     * Show messages queue
     *
     * @return array
     * @throws WhatsAppApiException
     */
    public function showMessagesQueue(): array
    {
        return $this->request('GET', $this->basePath . '/showMessagesQueue');
    }

    // --- Instance Management (user-level) ---

    /**
     * Get user account instances
     *
     * @return array
     * @throws WhatsAppApiException
     */
    public function getInstances(): array
    {
        $this->requireUserCredentials();

        return $this->request('POST', '/api/v1/instance/user/instances/list', [
            'headers' => [
                'x-user-id' => $this->userId,
                'x-user-token' => $this->userToken
            ]
        ]);
    }

    /**
     * Create new user instance
     *
     * @param string $tariff
     * @param string $period
     * @param string|null $paymentType
     * @return array
     * @throws WhatsAppApiException
     */
    public function createInstance(string $tariff, string $period, ?string $paymentType = null): array
    {
        $this->requireUserCredentials();

        $data = ['tariff' => $tariff, 'period' => $period];
        if ($paymentType) {
            $data['paymentType'] = $paymentType;
        }

        return $this->request('POST', '/api/v1/instance/user/instance/createByOrder', [
            'headers' => [
                'x-user-id' => $this->userId,
                'x-user-token' => $this->userToken
            ],
            'json' => $data
        ]);
    }

    /**
     * Extend paid user instance
     *
     * @param int $idInstance
     * @param string $tariff
     * @param string $period
     * @param string|null $paymentType
     * @return array
     * @throws WhatsAppApiException
     */
    public function extendInstance(int $idInstance, string $tariff, string $period, ?string $paymentType = null): array
    {
        $this->requireUserCredentials();

        $data = ['idInstance' => $idInstance, 'tariff' => $tariff, 'period' => $period];
        if ($paymentType) {
            $data['paymentType'] = $paymentType;
        }

        return $this->request('POST', '/api/v1/instance/user/instance/extendByOrder', [
            'headers' => [
                'x-user-id' => $this->userId,
                'x-user-token' => $this->userToken
            ],
            'json' => $data
        ]);
    }

    /**
     * Delete user instance
     *
     * @param int $idInstance
     * @return array
     * @throws WhatsAppApiException
     */
    public function deleteInstance(int $idInstance): array
    {
        $this->requireUserCredentials();

        return $this->request('POST', '/api/v1/instance/user/instance/delete', [
            'headers' => [
                'x-user-id' => $this->userId,
                'x-user-token' => $this->userToken
            ],
            'json' => ['idInstance' => $idInstance]
        ]);
    }

    /**
     * Restore user instance
     *
     * @param int $idInstance
     * @return array
     * @throws WhatsAppApiException
     */
    public function restoreInstance(int $idInstance): array
    {
        $this->requireUserCredentials();

        return $this->request('POST', '/api/v1/instance/user/instance/restore', [
            'headers' => [
                'x-user-id' => $this->userId,
                'x-user-token' => $this->userToken
            ],
            'json' => ['idInstance' => $idInstance]
        ]);
    }

    /**
     * Require user credentials for instance management
     *
     * @throws WhatsAppApiException
     */
    private function requireUserCredentials(): void
    {
        if (!$this->userId || !$this->userToken) {
            throw new WhatsAppApiException('userId and userToken are required for instance management operations');
        }
    }
}
