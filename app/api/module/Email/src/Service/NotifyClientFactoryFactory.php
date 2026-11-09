<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class NotifyClientFactoryFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): NotifyClientFactory
    {
        $config = $container->get('config');

        return new NotifyClientFactory(
            NotifyClientFactory::resolveGuzzleOptions(NotifyConfig::proxy($config))
        );
    }
}
