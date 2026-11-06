<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Notify;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class NotifyMessageRecorderFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): NotifyMessageRecorder
    {
        return new NotifyMessageRecorder($container);
    }
}
