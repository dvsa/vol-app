<?php

namespace Olcs\Service\Data;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * AbstractPublicInquiryDataFactory
 */
class AbstractPublicInquiryDataFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new $requestedName(
            $container->get(AbstractPublicInquiryDataServices::class)
        );
    }

    /**
     * Create service method for Laminas v2 compatibility
     *
     * @param ServiceLocatorInterface $services
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $services)
    {
        // see Laminas\ServiceManager\ServiceManager line 1091
        // additional arguments are passed into this method beyond those defined in the interface
        $args = func_get_args();
        $requestedName = $args[2];

        return $this($services, $requestedName);
    }
}
