<?php

/**
 * Payload Validation Listener Factory
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Mvc;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Payload Validation Listener Factory
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PayloadValidationListenerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PayloadValidationListener
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PayloadValidationListener
    {
        return new PayloadValidationListener($container->get('TransferAnnotationBuilder'));
    }
}
