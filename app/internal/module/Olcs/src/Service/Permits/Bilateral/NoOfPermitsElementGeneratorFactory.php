<?php

namespace Olcs\Service\Permits\Bilateral;

use Interop\Container\ContainerInterface;
use Laminas\Form\Factory as FormFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;

class NoOfPermitsElementGeneratorFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return NoOfPermitsElementGenerator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : NoOfPermitsElementGenerator
    {
        return new NoOfPermitsElementGenerator(
            $container->get('Helper\Translation'),
            new FormFactory()
        );
    }
}
