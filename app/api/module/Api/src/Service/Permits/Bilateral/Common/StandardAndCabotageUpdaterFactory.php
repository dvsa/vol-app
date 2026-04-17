<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Common;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class StandardAndCabotageUpdaterFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new StandardAndCabotageUpdater(
            $container->get('PermitsBilateralCommonModifiedAnswerUpdater')
        );
    }
}
