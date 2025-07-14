<?php

namespace SDKWA\Tests;

use PHPUnit\Framework\TestCase;
use SDKWA\WebhookHandler;

class WebhookHandlerTest extends TestCase
{
    private WebhookHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new WebhookHandler();
    }

    public function testOnIncomingMessageText(): void
    {
        $called = false;
        $this->handler->onIncomingMessageText(function ($data) use (&$called) {
            $called = true;
            $this->assertEquals('Hello', $data['messageData']['textMessageData']['textMessage']);
        });

        $webhookData = [
            'typeWebhook' => 'incomingMessageReceived',
            'messageData' => [
                'typeMessage' => 'textMessage',
                'textMessageData' => [
                    'textMessage' => 'Hello'
                ]
            ]
        ];

        $this->handler->processWebhook($webhookData);
        $this->assertTrue($called);
    }

    public function testOnOutgoingMessageStatus(): void
    {
        $called = false;
        $this->handler->onOutgoingMessageStatus(function ($data) use (&$called) {
            $called = true;
            $this->assertEquals('sent', $data['status']);
        });

        $webhookData = [
            'typeWebhook' => 'outgoingMessageStatus',
            'status' => 'sent'
        ];

        $this->handler->processWebhook($webhookData);
        $this->assertTrue($called);
    }

    public function testOnStateInstance(): void
    {
        $called = false;
        $this->handler->onStateInstance(function ($data) use (&$called) {
            $called = true;
            $this->assertEquals('authorized', $data['stateInstance']);
        });

        $webhookData = [
            'typeWebhook' => 'stateInstanceChanged',
            'stateInstance' => 'authorized'
        ];

        $this->handler->processWebhook($webhookData);
        $this->assertTrue($called);
    }

    public function testMethodChaining(): void
    {
        $result = $this->handler
            ->onIncomingMessageText(function ($data) {
            })
            ->onOutgoingMessageStatus(function ($data) {
            })
            ->onStateInstance(function ($data) {
            });

        $this->assertInstanceOf(WebhookHandler::class, $result);
    }

    public function testUnknownWebhookType(): void
    {
        $called = false;
        $this->handler->onIncomingMessageText(function ($data) use (&$called) {
            $called = true;
        });

        $webhookData = [
            'typeWebhook' => 'unknownType'
        ];

        $this->handler->processWebhook($webhookData);
        $this->assertFalse($called);
    }

    public function testInvalidWebhookData(): void
    {
        $called = false;
        $this->handler->onIncomingMessageText(function ($data) use (&$called) {
            $called = true;
        });

        $webhookData = []; // No typeWebhook

        $this->handler->processWebhook($webhookData);
        $this->assertFalse($called);
    }

    public function testFileMessageHandler(): void
    {
        $called = false;
        $this->handler->onIncomingMessageFile(function ($data) use (&$called) {
            $called = true;
            $this->assertEquals('image.jpg', $data['messageData']['fileMessageData']['fileName']);
        });

        $webhookData = [
            'typeWebhook' => 'incomingMessageReceived',
            'messageData' => [
                'typeMessage' => 'imageMessage',
                'fileMessageData' => [
                    'fileName' => 'image.jpg',
                    'downloadUrl' => 'https://example.com/image.jpg'
                ]
            ]
        ];

        $this->handler->processWebhook($webhookData);
        $this->assertTrue($called);
    }

    public function testLocationMessageHandler(): void
    {
        $called = false;
        $this->handler->onIncomingMessageLocation(function ($data) use (&$called) {
            $called = true;
            $this->assertEquals('London', $data['messageData']['locationMessageData']['nameLocation']);
        });

        $webhookData = [
            'typeWebhook' => 'incomingMessageReceived',
            'messageData' => [
                'typeMessage' => 'locationMessage',
                'locationMessageData' => [
                    'nameLocation' => 'London',
                    'latitude' => 51.5074,
                    'longitude' => -0.1278
                ]
            ]
        ];

        $this->handler->processWebhook($webhookData);
        $this->assertTrue($called);
    }

    public function testContactMessageHandler(): void
    {
        $called = false;
        $this->handler->onIncomingMessageContact(function ($data) use (&$called) {
            $called = true;
            $this->assertEquals('John Doe', $data['messageData']['contactMessageData']['displayName']);
        });

        $webhookData = [
            'typeWebhook' => 'incomingMessageReceived',
            'messageData' => [
                'typeMessage' => 'contactMessage',
                'contactMessageData' => [
                    'displayName' => 'John Doe',
                    'vcard' => 'BEGIN:VCARD...'
                ]
            ]
        ];

        $this->handler->processWebhook($webhookData);
        $this->assertTrue($called);
    }
}
