<?php

namespace SDKWA;

/**
 * Webhook Handler for processing incoming webhooks
 */
class WebhookHandler
{
    /**
     * @var array<string, callable>
     */
    private array $callbacks = [];

    /**
     * Register callback for state instance changes
     *
     * @param callable $callback
     * @return self
     */
    public function onStateInstance(callable $callback): self
    {
        $this->callbacks['stateInstanceChanged'] = $callback;
        return $this;
    }

    /**
     * Register callback for outgoing message status updates
     *
     * @param callable $callback
     * @return self
     */
    public function onOutgoingMessageStatus(callable $callback): self
    {
        $this->callbacks['outgoingMessageStatus'] = $callback;
        return $this;
    }

    /**
     * Register callback for incoming text messages
     *
     * @param callable $callback
     * @return self
     */
    public function onIncomingMessageText(callable $callback): self
    {
        $this->callbacks['incomingMessageReceived_textMessage'] = $callback;
        return $this;
    }

    /**
     * Register callback for incoming file messages
     *
     * @param callable $callback
     * @return self
     */
    public function onIncomingMessageFile(callable $callback): self
    {
        $this->callbacks['incomingMessageReceived_imageMessage'] = $callback;
        return $this;
    }

    /**
     * Register callback for incoming location messages
     *
     * @param callable $callback
     * @return self
     */
    public function onIncomingMessageLocation(callable $callback): self
    {
        $this->callbacks['incomingMessageReceived_locationMessage'] = $callback;
        return $this;
    }

    /**
     * Register callback for incoming contact messages
     *
     * @param callable $callback
     * @return self
     */
    public function onIncomingMessageContact(callable $callback): self
    {
        $this->callbacks['incomingMessageReceived_contactMessage'] = $callback;
        return $this;
    }

    /**
     * Register callback for incoming extended text messages
     *
     * @param callable $callback
     * @return self
     */
    public function onIncomingMessageExtendedText(callable $callback): self
    {
        $this->callbacks['incomingMessageReceived_extendedTextMessage'] = $callback;
        return $this;
    }

    /**
     * Register callback for device info
     *
     * @param callable $callback
     * @return self
     */
    public function onDeviceInfo(callable $callback): self
    {
        $this->callbacks['deviceInfo'] = $callback;
        return $this;
    }

    /**
     * Process webhook data
     *
     * @param array<string, mixed> $data
     * @return void
     */
    public function processWebhook(array $data): void
    {
        $webhookType = $this->determineWebhookType($data);

        if ($webhookType && isset($this->callbacks[$webhookType])) {
            call_user_func($this->callbacks[$webhookType], $data);
        }
    }

    /**
     * Determine webhook type from data
     *
     * @param array<string, mixed> $data
     * @return string|null
     */
    private function determineWebhookType(array $data): ?string
    {
        if (!isset($data['typeWebhook'])) {
            return null;
        }

        $baseType = $data['typeWebhook'];

        // For message webhooks, append the message type
        if (isset($data['messageData']['typeMessage'])) {
            return $baseType . '_' . $data['messageData']['typeMessage'];
        }

        return $baseType;
    }

    /**
     * Handle webhook request (for use with web frameworks)
     *
     * @param array<string, mixed> $requestData
     * @return void
     */
    public function handleRequest(array $requestData): void
    {
        $this->processWebhook($requestData);
    }
}
