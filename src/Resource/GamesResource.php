<?php

declare(strict_types=1);

namespace TcgPriceLookup\Resource;

use TcgPriceLookup\Client;

/**
 * Operations on games.
 */
final class GamesResource
{
    public function __construct(private readonly Client $client)
    {
    }

    /**
     * List every supported trading card game.
     *
     * @param array{limit?: int, offset?: int} $params
     * @return array<mixed>
     */
    public function list(array $params = []): array
    {
        $result = $this->client->request('/games', [
            'limit' => $params['limit'] ?? null,
            'offset' => $params['offset'] ?? null,
        ]);
        return $result ?? ['data' => [], 'total' => 0, 'limit' => 0, 'offset' => 0];
    }
}
