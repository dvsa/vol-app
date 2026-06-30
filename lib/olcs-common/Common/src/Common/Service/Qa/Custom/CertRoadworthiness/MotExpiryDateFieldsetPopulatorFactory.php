<?php

namespace Common\Service\Qa\Custom\CertRoadworthiness;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class MotExpiryDateFieldsetPopulatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): MotExpiryDateFieldsetPopulator
    {
        return new MotExpiryDateFieldsetPopulator(
            $container->get('Helper\Translation'),
            $container->get('QaCommonHtmlAdder'),
            $container->get('QaCommonFileUploadFieldsetGenerator')
        );
    }
}
