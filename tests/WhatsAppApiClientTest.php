<?php

namespace SDKWA\Tests;

use PHPUnit\Framework\TestCase;
use SDKWA\WhatsAppApiClient;
use SDKWA\Exceptions\WhatsAppApiException;

class WhatsAppApiClientTest extends TestCase
{
    private array $config;

    protected function setUp(): void
    {
        $this->config = [
            'apiHost' => 'https://api.sdkwa.pro',
            'idInstance' => 'test_instance',
            'apiTokenInstance' => 'test_token'
        ];
    }

    public function testConstructorWithValidConfig(): void
    {
        $client = new WhatsAppApiClient($this->config);
        $this->assertInstanceOf(WhatsAppApiClient::class, $client);
    }

    public function testConstructorWithMissingIdInstance(): void
    {
        $this->expectException(WhatsAppApiException::class);
        $this->expectExceptionMessage('idInstance is required and must be a non-empty string');

        $config = $this->config;
        unset($config['idInstance']);
        new WhatsAppApiClient($config);
    }

    public function testConstructorWithMissingApiToken(): void
    {
        $this->expectException(WhatsAppApiException::class);
        $this->expectExceptionMessage('apiTokenInstance is required and must be a non-empty string');

        $config = $this->config;
        unset($config['apiTokenInstance']);
        new WhatsAppApiClient($config);
    }

    public function testConstructorWithEmptyIdInstance(): void
    {
        $this->expectException(WhatsAppApiException::class);
        $this->expectExceptionMessage('idInstance is required and must be a non-empty string');

        $config = $this->config;
        $config['idInstance'] = '';
        new WhatsAppApiClient($config);
    }

    public function testConstructorWithEmptyApiToken(): void
    {
        $this->expectException(WhatsAppApiException::class);
        $this->expectExceptionMessage('apiTokenInstance is required and must be a non-empty string');

        $config = $this->config;
        $config['apiTokenInstance'] = '';
        new WhatsAppApiClient($config);
    }

    public function testGetWebhookHandler(): void
    {
        $client = new WhatsAppApiClient($this->config);
        $handler = $client->getWebhookHandler();

        $this->assertInstanceOf(\SDKWA\WebhookHandler::class, $handler);
    }

    public function testDefaultApiHost(): void
    {
        $config = $this->config;
        unset($config['apiHost']);

        $client = new WhatsAppApiClient($config);
        $this->assertInstanceOf(WhatsAppApiClient::class, $client);
    }

    public function testUserCredentialsForInstanceManagement(): void
    {
        $client = new WhatsAppApiClient($this->config);

        $this->expectException(WhatsAppApiException::class);
        $this->expectExceptionMessage('userId and userToken are required for instance management operations');

        $client->getInstances();
    }

    public function testWithUserCredentials(): void
    {
        $config = $this->config;
        $config['userId'] = 'test_user';
        $config['userToken'] = 'test_user_token';

        $client = new WhatsAppApiClient($config);
        $this->assertInstanceOf(WhatsAppApiClient::class, $client);
    }
}
