# tcglookup-php

[![Packagist Version](https://img.shields.io/packagist/v/tcgpricelookup/sdk.svg)](https://packagist.org/packages/tcgpricelookup/sdk)
[![PHP Version](https://img.shields.io/badge/php-8.1+-blue.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Powered by TCG Price Lookup](https://img.shields.io/badge/powered%20by-TCG%20Price%20Lookup-purple.svg)](https://tcgpricelookup.com/tcg-api)

The official PHP SDK for the [**TCG Price Lookup API**](https://tcgpricelookup.com/tcg-api) — live trading card prices across **Pokemon, Magic: The Gathering, Yu-Gi-Oh!, Disney Lorcana, One Piece TCG, Star Wars: Unlimited, and Flesh and Blood**.

One API for every major trading card game. TCGPlayer market prices, eBay sold averages, and PSA / BGS / CGC graded comps — all in one place.

## Install

```bash
composer require tcgpricelookup/sdk
```

Requires PHP 8.1+.

## Quickstart

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use TcgPriceLookup\Client;

$client = new Client('tlk_live_...');

$results = $client->cards->search([
    'q' => 'charizard',
    'game' => 'pokemon',
    'limit' => 5,
]);

foreach ($results['data'] as $card) {
    echo $card['name'] . ' — ' . $card['set']['name'] . PHP_EOL;
}
```

## Get an API key

Sign up at [tcgpricelookup.com/tcg-api](https://tcgpricelookup.com/tcg-api). Free tier includes 10,000 requests per month with TCGPlayer market prices. Trader plan unlocks eBay sold averages, PSA / BGS / CGC graded prices, and full price history.

## API surface

### Cards

```php
// Search
$client->cards->search([
    'q' => 'blue-eyes white dragon',
    'game' => 'yugioh',  // pokemon | mtg | yugioh | onepiece | lorcana | swu | fab
    'set' => 'lob',
    'limit' => 20,
    'offset' => 0,
]);

// Get one
$card = $client->cards->get('<card-uuid>');

// Daily price history (Trader plan)
$history = $client->cards->history('<card-uuid>', ['period' => '30d']);
```

### Sets

```php
$sets = $client->sets->list(['game' => 'mtg', 'limit' => 50]);
```

### Games

```php
$games = $client->games->list();
foreach ($games['data'] as $game) {
    echo $game['slug'] . ': ' . $game['count'] . ' cards' . PHP_EOL;
}
```

### Batch lookups

Pass an `ids` array and the SDK auto-chunks into 20-ID batches:

```php
$results = $client->cards->search([
    'ids' => ['uuid1', 'uuid2', /* ... */],
]);
```

## Error handling

```php
use TcgPriceLookup\Exception\{
    AuthenticationException,
    PlanAccessException,
    NotFoundException,
    RateLimitException,
};

try {
    $history = $client->cards->history('<uuid>', ['period' => '1y']);
} catch (AuthenticationException $e) {
    echo "Bad API key\n";
} catch (PlanAccessException $e) {
    echo "History requires Trader plan — upgrade at tcgpricelookup.com/tcg-api\n";
} catch (NotFoundException $e) {
    echo "Card not found\n";
} catch (RateLimitException $e) {
    echo "Rate limited. Quota: {$client->rateLimit['remaining']}/{$client->rateLimit['limit']}\n";
}
```

## Configuration

```php
$client = new Client('tlk_live_...', [
    'base_url' => 'https://api.tcgpricelookup.com/v1',
    'timeout' => 60.0,
    'user_agent' => 'my-app/1.0',
]);
```

## Sister SDKs

- [tcglookup-js](https://github.com/TCG-Price-Lookup/tcglookup-js) — JavaScript / TypeScript
- [tcglookup-py](https://github.com/TCG-Price-Lookup/tcglookup-py) — Python
- [tcglookup-go](https://github.com/TCG-Price-Lookup/tcglookup-go) — Go
- [tcglookup-rs](https://github.com/TCG-Price-Lookup/tcglookup-rs) — Rust
- [tcglookup CLI](https://www.npmjs.com/package/tcglookup) — terminal client

The full developer ecosystem index lives at **[awesome-tcg](https://github.com/TCG-Price-Lookup/awesome-tcg)**.

## License

MIT — see [LICENSE](LICENSE).

---

Built by [TCG Price Lookup](https://tcgpricelookup.com). Get a free API key at [tcgpricelookup.com/tcg-api](https://tcgpricelookup.com/tcg-api).
