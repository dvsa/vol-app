<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Auth;

use Psr\Container\ContainerInterface;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * @see ChangePasswordFactory
 */
class ChangePasswordFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ChangePassword
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ChangePassword
    {
        $adapter = $container->get(ValidatableAdapterInterface::class);
        $instance = new ChangePassword($adapter);
        return $instance->__invoke($container, $requestedName, $options);
    }
}
