<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AnswerWriterFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return AnswerWriter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AnswerWriter
    {
        return new AnswerWriter(
            $container->get('RepositoryServiceManager')->get('IrhpPermitApplication')
        );
    }
}
