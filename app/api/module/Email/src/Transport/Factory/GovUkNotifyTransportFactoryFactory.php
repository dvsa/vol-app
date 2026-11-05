<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Transport\Factory;

use Alphagov\Notifications\Client as NotifyClient;
use Dvsa\Olcs\Email\Service\NotifyClientFactory;
use Dvsa\Olcs\Email\Transport\GovUkNotifyTransportFactory;
use Dvsa\Olcs\Email\Transport\NotifyMessageRecorderInterface;
use Dvsa\Olcs\Email\View\NotifyChrome;
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

        // The client factory routes through the shared egress proxy where one is configured.
        $notifyClientFactory = $container->get(NotifyClientFactory::class);
        $clientFactory = static fn (string $apiKey): NotifyClient => $notifyClientFactory->fromApiKey($apiKey);

        return new GovUkNotifyTransportFactory(
            $templates,
            $clientFactory,
            $markdownConverter,
            $chromeTemplate,
            $container->has(NotifyMessageRecorderInterface::class)
                ? $container->get(NotifyMessageRecorderInterface::class)
                : null,
        );
    }

    /**
     * @return array{proxy?: string}
     */
    public static function resolveGuzzleOptions(mixed $proxy): array
    {
        return NotifyClientFactory::resolveGuzzleOptions($proxy);
    }
}
