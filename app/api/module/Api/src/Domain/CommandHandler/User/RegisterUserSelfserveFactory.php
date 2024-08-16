<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransactioningCommandHandler;
use Dvsa\Olcs\Auth\Service\PasswordService;
use Psr\Container\ContainerInterface;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RegisterUserSelfserveFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TransactioningCommandHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransactioningCommandHandler
    {
        $adapter = $container->get(ValidatableAdapterInterface::class);

        $passwordService = $container->get(PasswordService::class);
        $instance = new RegisterUserSelfserve($passwordService, $adapter);
        return $instance->__invoke($container, $requestedName, $options);
    }
}
