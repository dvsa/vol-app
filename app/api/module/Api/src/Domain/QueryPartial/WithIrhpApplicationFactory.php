<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class WithIrhpApplicationFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return WithIrhpApplication
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): WithIrhpApplication
    {
        return new WithIrhpApplication(
            $container->get('QueryPartialServiceManager')->get('with')
        );
    }
}
