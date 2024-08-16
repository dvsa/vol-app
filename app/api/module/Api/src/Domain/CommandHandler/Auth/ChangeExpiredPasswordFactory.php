<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Auth;

use Psr\Container\ContainerInterface;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * @see ChangeExpiredPassword
 */
class ChangeExpiredPasswordFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ChangeExpiredPassword
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ChangeExpiredPassword
    {
        $adapter = $container->get(ValidatableAdapterInterface::class);
        $userRepository = $container->get('RepositoryServiceManager')->get('User');
        return (new ChangeExpiredPassword($adapter, $userRepository))->__invoke($container, $requestedName, $options);
    }
}
