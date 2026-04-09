<?php

declare(strict_types=1);

namespace TcgPriceLookup\Resource;

use TcgPriceLookup\Client;

/**
 * Operations on cards.
 */
final class CardsResource
{
    /** Backend hard cap on the `ids` parameter per request. */
    public const SEARCH_IDS_CHUNK_SIZE = 20;

    public function __construct(private readonly Client $client)
    {
    }

    /**
     * Search cards by name, set, game, or batch by IDs.
     *
     * Passing more than 20 IDs auto-chunks into multiple requests.
     *
     * @param array{
     *     q?: string,
     *     ids?: array<string>,
     *     game?: string,
     *     set?: string,
     *     limit?: int,
     *     offset?: int
     * } $params
     *
     * @return array<mixed>
     */
    public function search(array $params = []): array
    {
        $ids = $params['ids'] ?? [];
        unset($params['ids']);

        if ($ids === []) {
            return $this->searchOnce($params, null);
        }
        if (count($ids) <= self::SEARCH_IDS_CHUNK_SIZE) {
            return $this->searchOnce($params, implode(',', $ids));
        }
        return $this->searchChunked($params, $ids);
    }

    /**
     * Get a single card by its UUID.
     *
     * @return array<mixed>
     */
    public function get(string $id): array
    {
        if ($id === '') {
            throw new \InvalidArgumentException('id is required');
        }
        $result = $this->client->request('/cards/' . rawurlencode($id));
        return $result ?? [];
    }

    /**
     * Daily price history. Trader plan and above.
     *
     * @param array{period?: string} $params
     * @return array<mixed>
     */
    public function history(string $id, array $params = []): array
    {
        if ($id === '') {
            throw new \InvalidArgumentException('id is required');
        }
        $result = $this->client->request(
            '/cards/' . rawurlencode($id) . '/history',
            ['period' => $params['period'] ?? null],
        );
        return $result ?? [];
    }

    /**
     * @param array<string, mixed> $params
     * @return array<mixed>
     */
    private function searchOnce(array $params, ?string $ids): array
    {
        $result = $this->client->request('/cards/search', [
            'q' => $params['q'] ?? null,
            'ids' => $ids,
            'game' => $params['game'] ?? null,
            'set' => $params['set'] ?? null,
            'limit' => $params['limit'] ?? null,
            'offset' => $params['offset'] ?? null,
        ]);
        return $result ?? ['data' => [], 'total' => 0, 'limit' => 0, 'offset' => 0];
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string> $ids
     * @return array<mixed>
     */
    private function searchChunked(array $params, array $ids): array
    {
        $merged = [];
        foreach (array_chunk($ids, self::SEARCH_IDS_CHUNK_SIZE) as $chunk) {
            $page = $this->searchOnce($params, implode(',', $chunk));
            foreach ($page['data'] ?? [] as $card) {
                $merged[] = $card;
            }
        }
        return [
            'data' => $merged,
            'total' => count($merged),
            'limit' => $params['limit'] ?? count($merged),
            'offset' => $params['offset'] ?? 0,
        ];
    }
}
