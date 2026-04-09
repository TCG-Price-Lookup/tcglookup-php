<?php

declare(strict_types=1);

namespace TcgPriceLookup\Exception;

/**
 * Base exception for any non-2xx response from the TCG Price Lookup API.
 *
 * Subclasses cover the most common HTTP statuses (401, 403, 404, 429).
 */
class TcgLookupException extends \RuntimeException
{
    /**
     * @param array<mixed>|null $body Decoded response body.
     */
    public function __construct(
        string $message,
        public readonly int $status,
        public readonly string $url,
        public readonly ?array $body,
    ) {
        parent::__construct($message, $status);
    }
}
