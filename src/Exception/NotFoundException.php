<?php

declare(strict_types=1);

namespace TcgPriceLookup\Exception;

/** 404 — card / set / game does not exist. */
final class NotFoundException extends TcgLookupException
{
}
