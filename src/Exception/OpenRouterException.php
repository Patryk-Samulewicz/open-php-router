<?php

declare(strict_types=1);

namespace OpenPhpRouter\Exception;

use Exception;

class OpenRouterException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
} 