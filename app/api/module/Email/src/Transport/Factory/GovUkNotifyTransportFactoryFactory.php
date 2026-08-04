<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Transport\Factory;

use Alphagov\Notifications\Client as NotifyClient;
use Dvsa\Olcs\Email\Transport\GovUkNotifyTransportFactory;
use Dvsa\Olcs\Email\View\NotifyChrome;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Laminas\ServiceManager\Factory\FactoryInterface;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Psr\Container\ContainerInterface;

/**
 * Laminas ServiceManager factory for {@see GovUkNotifyTransportFactory}.
 *
 * Reads Notify passthrough template UUIDs from application config
 * (`config['email']['notify']['passthrough_templates']`). The Notify client is constructed lazily
 * per DSN so the API key (which comes from the DSN) is not required at container-build time.
 */
final class GovUkNotifyTransportFactoryFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): GovUkNotifyTransportFactory
    {
        $config = $container->get('config');
        $notifyConfig = $config['email']['notify'] ?? [];

        /** @var array<string, string> $templates */
        $templates = $notifyConfig['passthrough_templates'] ?? [];
        // Strip unresolved `%placeholder%` values so the transport throws a meaningful error
        // rather than calling Notify with a literal placeholder string.
        $templates = array_filter($templates, static fn (string $v): bool => $v !== '' && !preg_match('/^%[^%]+%$/', $v));

        $chromeTemplate = is_string($notifyConfig['dev_chrome_template'] ?? null)
            ? $notifyConfig['dev_chrome_template']
            : NotifyChrome::template();

        $markdownConverter = new GithubFlavoredMarkdownConverter();

        // In deployed environments outbound internet egress is only reachable via the shared
        // forward proxy, so the Notify client must be routed through it like every other external
        // integration. Locally (and before the proxy is resolved from Parameter Store) this is
        // empty and the client connects directly.
        $guzzleOptions = self::resolveGuzzleOptions($notifyConfig['proxy'] ?? null);

        $clientFactory = static function (string $apiKey) use ($guzzleOptions): NotifyClient {
            return new NotifyClient([
                'apiKey' => $apiKey,
                'httpClient' => new GuzzleAdapter(new GuzzleClient($guzzleOptions)),
            ]);
        };

        return new GovUkNotifyTransportFactory(
            $templates,
            $clientFactory,
            $markdownConverter,
            $chromeTemplate,
        );
    }

    /**
     * Build the Guzzle client options for the Notify HTTP client.
     *
     * Returns the shared egress proxy when one is configured. An empty value or an unresolved
     * `%placeholder%` (which still contains a `%`, e.g. when running locally where the cloud
     * parameter providers are not active) is ignored so the client falls back to a direct
     * connection rather than pointing Guzzle at a bogus proxy host.
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
