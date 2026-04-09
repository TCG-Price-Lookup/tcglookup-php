<?php

declare(strict_types=1);

namespace TcgPriceLookup\Exception;

/**
 * 403 — your plan does not include access to this resource.
 *
 * Free-tier API keys hit this on price history endpoints.
 * Upgrade at https://tcgpricelookup.com/tcg-api.
 */
final class PlanAccessException extends TcgLookupException
{
}
