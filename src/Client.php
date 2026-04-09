<?php

declare(strict_types=1);

namespace TcgPriceLookup;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use TcgPriceLookup\Exception\AuthenticationException;
use TcgPriceLookup\Exception\NotFoundException;
use TcgPriceLookup\Exception\PlanAccessException;
use TcgPriceLookup\Exception\RateLimitException;
use TcgPriceLookup\Exception\TcgLookupException;
use TcgPriceLookup\Resource\CardsResource;
use TcgPriceLookup\Resource\GamesResource;
use TcgPriceLookup\Resource\SetsResource;

/**
 * Synchronous PHP client for the TCG Price Lookup REST API.
 *
 * Get a free API key at https://tcgpricelookup.com/tcg-api
 *
 * Example:
 *
 *   $client = new \TcgPriceLookup\Client('tlk_live_...');
 *   $results = $client->cards->search(['q' => 'charizard', 'game' => 'pokemon']);
 *   foreach ($results['data'] as $card) {
 *       echo $card['name'] . "\n";
 *   }
 */
final class Client
{
    public const VERSION = '0.1.0';
    private const DEFAULT_BASE_URL = 'https://api.tcgpricelookup.com/v1';
    private const DEFAULT_USER_AGENT = 'tcglookup-php/0.1.0';

    public readonly CardsResource $cards;
    public readonly SetsResource $sets;
    public readonly GamesResource $games;

    /** @var array{limit: ?int, remaining: ?int} */
    public array $rateLimit = ['limit' => null, 'remaining' => null];

    private readonly HttpClient $http;
    private readonly string $apiKey;
    private readonly string $baseUrl;
    private readonly string $userAgent;

    /**
     * @param string $apiKey  Your API key from tcgpricelookup.com/tcg-api
     * @param array{
     *     base_url?: string,
     *     timeout?: float,
     *     user_agent?: string,
     *     http?: HttpClient
     * } $options
     */
    public function __construct(string $apiKey, array $options = [])
    {
        if ($apiKey === '') {
            throw new \InvalidArgumentException('apiKey is required');
        }
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($options['base_url'] ?? self::DEFAULT_BASE_URL, '/');
        $this->userAgent = $options['user_agent'] ?? self::DEFAULT_USER_AGENT;
        $this->http = $options['http'] ?? new HttpClient([
            'timeout' => $options['timeout'] ?? 30.0,
        ]);

        $this->cards = new CardsResource($this);
        $this->sets = new SetsResource($this);
        $this->games = new GamesResource($this);
    }

    /**
     * Internal request hook used by resource classes.
     *
     * @param array<string, mixed> $query
     * @return array<mixed>|null
     *
     * @throws TcgLookupException
     */
    public function request(string $path, array $query = []): ?array
    {
        $url = $this->baseUrl . (str_starts_with($path, '/') ? $path : '/' . $path);
        $cleanQuery = [];
        foreach ($query as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $cleanQuery[$key] = (string) $value;
        }

        try {
            $response = $this->http->request('GET', $url, [
                'query' => $cleanQuery,
                'headers' => [
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                    'User-Agent' => $this->userAgent,
                ],
                'http_errors' => false,
            ]);
        } catch (GuzzleException $e) {
            throw new TcgLookupException(
                $e->getMessage(),
                0,
                $url,
                null,
            );
        }

        $this->captureRateLimit($response);
        $body = (string) $response->getBody();
        $decoded = $body === '' ? null : json_decode($body, true);

        $status = $response->getStatusCode();
        if ($status >= 400) {
            throw $this->errorFromResponse($status, $url, $decoded);
        }

        return is_array($decoded) ? $decoded : null;
    }

    private function captureRateLimit(Response $response): void
    {
        $limit = $response->getHeaderLine('x-ratelimit-limit');
        $remaining = $response->getHeaderLine('x-ratelimit-remaining');
        $this->rateLimit = [
            'limit' => $limit !== '' ? (int) $limit : null,
            'remaining' => $remaining !== '' ? (int) $remaining : null,
        ];
    }

    /**
     * @param array<mixed>|null $body
     */
    private function errorFromResponse(int $status, string $url, ?array $body): TcgLookupException
    {
        $message = is_array($body) && isset($body['error']) && is_string($body['error'])
            ? $body['error']
            : "HTTP {$status}";

        return match ($status) {
            401 => new AuthenticationException($message, $status, $url, $body),
            403 => new PlanAccessException($message, $status, $url, $body),
            404 => new NotFoundException($message, $status, $url, $body),
            429 => new RateLimitException($message, $status, $url, $body),
            default => new TcgLookupException($message, $status, $url, $body),
        };
    }
}
