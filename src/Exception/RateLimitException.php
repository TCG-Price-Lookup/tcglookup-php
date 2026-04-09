<?php

declare(strict_types=1);

namespace TcgPriceLookup\Exception;

/**
 * 429 — rate limit exceeded.
 *
 * Inspect $client->rateLimit after the call to see your current quota.
 */
final class RateLimitException extends TcgLookupException
{
}
