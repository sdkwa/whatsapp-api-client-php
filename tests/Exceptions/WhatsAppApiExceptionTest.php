<?php

namespace SDKWA\Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use SDKWA\Exceptions\WhatsAppApiException;

class WhatsAppApiExceptionTest extends TestCase
{
    public function testExceptionWithMessage(): void
    {
        $message = 'Test error message';
        $exception = new WhatsAppApiException($message);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getStatusCode());
    }

    public function testExceptionWithStatusCode(): void
    {
        $message = 'Test error message';
        $statusCode = 400;
        $exception = new WhatsAppApiException($message, $statusCode);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($statusCode, $exception->getStatusCode());
        $this->assertEquals($statusCode, $exception->getCode());
    }

    public function testExceptionWithPreviousException(): void
    {
        $previousException = new \Exception('Previous exception');
        $exception = new WhatsAppApiException('Test error', 500, $previousException);

        $this->assertEquals('Test error', $exception->getMessage());
        $this->assertEquals(500, $exception->getStatusCode());
        $this->assertEquals($previousException, $exception->getPrevious());
    }

    public function testExceptionDefaults(): void
    {
        $exception = new WhatsAppApiException();

        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(0, $exception->getStatusCode());
        $this->assertNull($exception->getPrevious());
    }
}
