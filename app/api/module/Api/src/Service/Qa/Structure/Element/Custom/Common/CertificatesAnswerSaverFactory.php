<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CertificatesAnswerSaverFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CertificatesAnswerSaver
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CertificatesAnswerSaver
    {
        return new CertificatesAnswerSaver(
            $container->get('QaBaseAnswerSaver')
        );
    }
}
