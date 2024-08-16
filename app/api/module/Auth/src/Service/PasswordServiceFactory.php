<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth\Service;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PasswordServiceFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PasswordService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PasswordService
    {
        return new PasswordService();
    }
}
