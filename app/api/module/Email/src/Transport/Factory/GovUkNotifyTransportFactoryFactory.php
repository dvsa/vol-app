<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Transport\Factory;

use Alphagov\Notifications\Client as NotifyClient;
use Dvsa\Olcs\Email\Service\NotifyClientFactory;
use Dvsa\Olcs\Email\Service\NotifyConfig;
use Dvsa\Olcs\Email\Transport\GovUkNotifyTransportFactory;
use Dvsa\Olcs\Email\Transport\NotifyMessageRecorderInterface;
use Dvsa\Olcs\Email\View\NotifyChrome;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Laminas ServiceManager factory for {@see GovUkNotifyTransportFactory}.
 *
 * Reads the Notify passthrough template UUIDs and API key from the `notify` config block (see
 * {@see NotifyConfig}). The Notify client is constructed lazily per DSN, so no key is needed at
 * container-build time. A key in the DSN wins over `notify.api_key`.
 */
final class GovUkNotifyTransportFactoryFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): GovUkNotifyTransportFactory
    {
        $config = $container->get('config');

        // Blank and unresolved `%placeholder%` ids are left out, so the transport throws a meaningful
        // error rather than calling Notify with a literal placeholder string.
        $templates = NotifyConfig::passthroughTemplates($config);

        $chromeTemplate = NotifyConfig::devChromeTemplate($config) ?? NotifyChrome::template();

        $markdownConverter = NotifyChrome::markdownConverter();

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
            NotifyConfig::apiKey($config),
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
