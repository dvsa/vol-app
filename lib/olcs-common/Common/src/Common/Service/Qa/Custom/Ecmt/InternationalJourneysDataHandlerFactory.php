<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class InternationalJourneysDataHandlerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): InternationalJourneysDataHandler
    {
        return new InternationalJourneysDataHandler(
            $container->get('QaCommonIsValidBasedWarningAdder'),
            $container->get('QaEcmtInternationalJourneysIsValidHandler')
        );
    }
}
