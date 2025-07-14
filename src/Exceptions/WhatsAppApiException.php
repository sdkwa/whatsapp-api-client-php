<?php

namespace SDKWA\Exceptions;

use Exception;
use Throwable;

/**
 * WhatsApp API Exception
 */
class WhatsAppApiException extends Exception
{
    private int $statusCode;

    /**
     * Constructor
     *
     * @param string $message
     * @param int $statusCode
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', int $statusCode = 0, ?Throwable $previous = null)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Get HTTP status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
