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
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GovUkNotifyTransportFactory
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

        $clientFactory = static function (string $apiKey): NotifyClient {
            return new NotifyClient([
                'apiKey' => $apiKey,
                'httpClient' => new GuzzleAdapter(new GuzzleClient()),
            ]);
        };

        return new GovUkNotifyTransportFactory(
            $templates,
            $clientFactory,
            $markdownConverter,
            $chromeTemplate,
        );
    }

}
