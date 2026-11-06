<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class SmsGateFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SmsGate
    {
        return new SmsGate(
            $container->get(SmsConfig::class),
            $container->get(ToggleService::class),
        );
    }
}
