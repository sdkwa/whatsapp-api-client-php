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
    /**
     * @var array<string, string>
     */
    private array $headers;
    private Client $httpClient;
    private WebhookHandler $webhookHandler;

    /**
     * Constructor
     *
     * @param array<string, mixed> $options Configuration options
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
     * @param array<string, mixed> $options
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
     * @param array<string, mixed> $options
     * @param string $messengerType Messenger type: 'whatsapp' or 'telegram'
     * @return array<string, mixed><string, mixed>
     * @throws WhatsAppApiException
     */
    private function request(string $method, string $path, array $options = [], string $messengerType = 'whatsapp'): array
    {
        // Build the full path with messenger type
        $fullPath = "/{$messengerType}/{$this->idInstance}{$path}";

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
            $response = $this->httpClient->request($method, $fullPath, $config);
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
     * @return array<string, mixed><string, mixed>
     * @throws WhatsAppApiException
     */
    public function getSettings(string $messengerType = 'whatsapp'): array
    {
        return $this->request('GET', '/getSettings', [], $messengerType);
    }

    /**
     * Set account settings
     *
     * @param array<string, mixed> $settings
     * @return array<string, mixed><string, mixed>
     * @throws WhatsAppApiException
     */
    public function setSettings(array $settings, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/setSettings', [
            'json' => $settings
        ], $messengerType);
    }

    /**
     * Get account state
     *
     * @return array<string, mixed><string, mixed>
     * @throws WhatsAppApiException
     */
    public function getStateInstance(string $messengerType = 'whatsapp'): array
    {
        return $this->request('GET', '/getStateInstance', [], $messengerType);
    }

    /**
     * Get warming phone status
     *
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function getWarmingPhoneStatus(string $messengerType = 'whatsapp'): array
    {
        return $this->request('GET', '/getWarmingPhoneStatus', [], $messengerType);
    }

    /**
     * Reboot account
     *
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function reboot(string $messengerType = 'whatsapp'): array
    {
        return $this->request('GET', '/reboot', [], $messengerType);
    }

    /**
     * Logout account
     *
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function logout(string $messengerType = 'whatsapp'): array
    {
        return $this->request('GET', '/logout', [], $messengerType);
    }

    /**
     * Get QR code for account authorization
     *
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function getQr(string $messengerType = 'whatsapp'): array
    {
        return $this->request('GET', '/qr', [], $messengerType);
    }

    /**
     * Get authorization code for account authorization
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function getAuthorizationCode(array $params, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/getAuthorizationCode', [
            'json' => $params
        ], $messengerType);
    }

    /**
     * Request registration code
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function requestRegistrationCode(array $params, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/requestRegistrationCode', [
            'json' => $params
        ], $messengerType);
    }

    /**
     * Send registration code
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function sendRegistrationCode(array $params, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/sendRegistrationCode', [
            'json' => $params
        ], $messengerType);
    }

    // --- Sending methods ---

    /**
     * Send message
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function sendMessage(array $params, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/sendMessage', [
            'json' => $params
        ], $messengerType);
    }

    /**
     * Send contact
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function sendContact(array $params, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/sendContact', [
            'json' => $params
        ], $messengerType);
    }

    /**
     * Send file by upload
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function sendFileByUpload(array $params, string $messengerType = 'whatsapp'): array
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

        return $this->request('POST', '/sendFileByUpload', [
            'multipart' => $multipart
        ], $messengerType);
    }

    /**
     * Send file by URL
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function sendFileByUrl(array $params, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/sendFileByUrl', [
            'json' => $params
        ], $messengerType);
    }

    /**
     * Send location
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function sendLocation(array $params, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/sendLocation', [
            'json' => $params
        ], $messengerType);
    }

    /**
     * Upload file
     *
     * @param mixed $file
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function uploadFile($file, string $messengerType = 'whatsapp'): array
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

        return $this->request('POST', '/uploadFile', [
            'multipart' => $multipart
        ], $messengerType);
    }

    /**
     * Get chat history
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function getChatHistory(array $params, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/getChatHistory', [
            'json' => $params
        ], $messengerType);
    }

    // --- Receiving methods ---

    /**
     * Receive notification
     *
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function receiveNotification(string $messengerType = 'whatsapp'): array
    {
        return $this->request('GET', '/receiveNotification', [], $messengerType);
    }

    /**
     * Delete notification
     *
     * @param int $receiptId
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function deleteNotification(int $receiptId, string $messengerType = 'whatsapp'): array
    {
        return $this->request('DELETE', "/deleteNotification/{$receiptId}");
    }

    // --- Chat/Contact methods ---

    /**
     * Get contacts
     *
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function getContacts(string $messengerType = 'whatsapp'): array
    {
        return $this->request('GET', '/getContacts', [], $messengerType);
    }

    /**
     * Get chats
     *
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function getChats(string $messengerType = 'whatsapp'): array
    {
        return $this->request('GET', '/getChats', [], $messengerType);
    }

    /**
     * Get contact information
     *
     * @param string $chatId
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function getContactInfo(string $chatId, string $messengerType = 'whatsapp'): array
    {
        return $this->request('GET', '/getContactInfo', [
            'json' => ['chatId' => $chatId]
        ]);
    }

    /**
     * Set profile picture
     *
     * @param mixed $file
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function setProfilePicture($file, string $messengerType = 'whatsapp'): array
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

        return $this->request('POST', '/setProfilePicture', [
            'multipart' => $multipart
        ], $messengerType);
    }

    /**
     * Set profile name
     *
     * @param string $name
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function setProfileName(string $name, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/setProfileName', [
            'json' => ['name' => $name]
        ]);
    }

    /**
     * Set profile status
     *
     * @param string $status
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function setProfileStatus(string $status, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/setProfileStatus', [
            'json' => ['status' => $status]
        ]);
    }

    /**
     * Get avatar
     *
     * @param string $chatId
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function getAvatar(string $chatId, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/getAvatar', [
            'json' => ['chatId' => $chatId]
        ]);
    }

    /**
     * Check WhatsApp account availability
     *
     * @param int $phoneNumber
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function checkWhatsapp(int $phoneNumber, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/checkWhatsapp', [
            'json' => ['phoneNumber' => $phoneNumber]
        ]);
    }

    // --- Group methods ---

    /**
     * Update group name
     *
     * @param string $groupId
     * @param string $groupName
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function updateGroupName(string $groupId, string $groupName, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/updateGroupName', [
            'json' => ['groupId' => $groupId, 'groupName' => $groupName]
        ]);
    }

    /**
     * Get group chat data
     *
     * @param string $groupId
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function getGroupData(string $groupId, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/getGroupData', [
            'json' => ['groupId' => $groupId]
        ]);
    }

    /**
     * Leave group chat
     *
     * @param string $groupId
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function leaveGroup(string $groupId, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/leaveGroup', [
            'json' => ['groupId' => $groupId]
        ]);
    }

    /**
     * Set group participant as administrator
     *
     * @param string $groupId
     * @param string $participantChatId
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function setGroupAdmin(string $groupId, string $participantChatId, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/setGroupAdmin', [
            'json' => ['groupId' => $groupId, 'participantChatId' => $participantChatId]
        ]);
    }

    /**
     * Remove group participant
     *
     * @param string $groupId
     * @param string $participantChatId
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function removeGroupParticipant(string $groupId, string $participantChatId, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/removeGroupParticipant', [
            'json' => ['groupId' => $groupId, 'participantChatId' => $participantChatId]
        ]);
    }

    /**
     * Remove group admin rights
     *
     * @param string $groupId
     * @param string $participantChatId
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function removeAdmin(string $groupId, string $participantChatId, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/removeAdmin', [
            'json' => ['groupId' => $groupId, 'participantChatId' => $participantChatId]
        ]);
    }

    /**
     * Create group chat
     *
     * @param string $groupName
     * @param array<string> $chatIds
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function createGroup(string $groupName, array $chatIds, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/createGroup', [
            'json' => ['groupName' => $groupName, 'chatIds' => $chatIds]
        ]);
    }

    /**
     * Add participant to group chat
     *
     * @param string $groupId
     * @param string $participantChatId
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function addGroupParticipant(string $groupId, string $participantChatId, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/addGroupParticipant', [
            'json' => ['groupId' => $groupId, 'participantChatId' => $participantChatId]
        ]);
    }

    /**
     * Set group picture
     *
     * @param string $groupId
     * @param mixed $file
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function setGroupPicture(string $groupId, $file, string $messengerType = 'whatsapp'): array
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

        return $this->request('POST', '/setGroupPicture', [
            'multipart' => $multipart
        ], $messengerType);
    }

    // --- Read mark ---

    /**
     * Mark chat messages as read
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function readChat(array $params, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/readChat', [
            'json' => $params
        ], $messengerType);
    }

    // --- Archive/Unarchive ---

    /**
     * Archive chat
     *
     * @param string $chatId
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function archiveChat(string $chatId, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/archiveChat', [
            'json' => ['chatId' => $chatId]
        ]);
    }

    /**
     * Unarchive chat
     *
     * @param string $chatId
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function unarchiveChat(string $chatId, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/unarchiveChat', [
            'json' => ['chatId' => $chatId]
        ]);
    }

    // --- Message deletion ---

    /**
     * Delete message
     *
     * @param string $chatId
     * @param string $idMessage
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function deleteMessage(string $chatId, string $idMessage, string $messengerType = 'whatsapp'): array
    {
        return $this->request('POST', '/deleteMessage', [
            'json' => ['chatId' => $chatId, 'idMessage' => $idMessage]
        ]);
    }

    // --- Queue ---

    /**
     * Clear messages queue
     *
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function clearMessagesQueue(string $messengerType = 'whatsapp'): array
    {
        return $this->request('GET', '/clearMessagesQueue', [], $messengerType);
    }

    /**
     * Show messages queue
     *
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function showMessagesQueue(string $messengerType = 'whatsapp'): array
    {
        return $this->request('GET', '/showMessagesQueue', [], $messengerType);
    }

    // --- Instance Management (user-level) ---

    /**
     * Get user account instances
     *
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function getInstances(string $messengerType = 'whatsapp'): array
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
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function createInstance(string $tariff, string $period, ?string $paymentType = null, string $messengerType = 'whatsapp'): array
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
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function extendInstance(int $idInstance, string $tariff, string $period, ?string $paymentType = null, string $messengerType = 'whatsapp'): array
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
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function deleteInstance(int $idInstance, string $messengerType = 'whatsapp'): array
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
     * @return array<string, mixed>
     * @throws WhatsAppApiException
     */
    public function restoreInstance(int $idInstance, string $messengerType = 'whatsapp'): array
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
