<?php

namespace App\Exception;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class StatutErrorException extends \RuntimeException implements HttpExceptionInterface
{
    private $statusCode;
    private $headers;


    public function __construct(string $message, int $statusCode = 400, \Throwable $previous = null, array $headers = [], ?int $code = 0)
    {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
