<?php

declare(strict_types=1);

namespace Sec4Dev\Exception;

use Exception;

class Sec4DevException extends Exception
{
    public int $statusCode = 0;

    /** @var mixed */
    public $responseBody;

    public function __construct(
        string $message,
        int $statusCode = 0,
        $responseBody = null
    ) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }
}
