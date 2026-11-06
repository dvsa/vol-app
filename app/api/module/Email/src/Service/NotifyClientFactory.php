<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Service;

use Alphagov\Notifications\Client as NotifyClient;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;

/**
 * Builds GOV.UK Notify clients, routed through the shared egress proxy when one is configured.
 */
class NotifyClientFactory
{
    /**
     * @param array<string, mixed> $guzzleOptions extra Guzzle options, e.g. the proxy
     */
    public function __construct(private readonly array $guzzleOptions = [])
    {
    }

    public function fromApiKey(string $apiKey): NotifyClient
    {
        return new NotifyClient([
            'apiKey' => $apiKey,
            // Notify turns error responses into an ApiException with its own message. Left on,
            // Guzzle throws first and the error recorded is Guzzle's text instead.
            'httpClient' => new GuzzleAdapter(new GuzzleClient(['http_errors' => false] + $this->guzzleOptions)),
        ]);
    }

    /**
     * Returns the proxy option only for a non-empty value with no unresolved `%placeholder%`,
     * so local runs fall back to a direct connection.
     *
     * @return array{proxy?: string}
     */
    public static function resolveGuzzleOptions(mixed $proxy): array
    {
        if (is_string($proxy) && $proxy !== '' && !str_contains($proxy, '%')) {
            return ['proxy' => $proxy];
        }

        return [];
    }
}
