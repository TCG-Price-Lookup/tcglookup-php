<?php

declare(strict_types=1);

namespace TcgPriceLookup\Resource;

use TcgPriceLookup\Client;

/**
 * Operations on sets.
 */
final class SetsResource
{
    public function __construct(private readonly Client $client)
    {
    }

    /**
     * List sets across all games, or filter by game slug.
     *
     * @param array{game?: string, limit?: int, offset?: int} $params
     * @return array<mixed>
     */
    public function list(array $params = []): array
    {
        $result = $this->client->request('/sets', [
            'game' => $params['game'] ?? null,
            'limit' => $params['limit'] ?? null,
            'offset' => $params['offset'] ?? null,
        ]);
        return $result ?? ['data' => [], 'total' => 0, 'limit' => 0, 'offset' => 0];
    }
}
