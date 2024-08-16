<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransactioningCommandHandler;
use Dvsa\Olcs\Api\Service\EventHistory\Creator;
use Dvsa\Olcs\Auth\Service\PasswordService;
use Psr\Container\ContainerInterface;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class UpdateUserSelfserveFactory implements FactoryInterface
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
        $authAdapter = $container->get(ValidatableAdapterInterface::class);
        $passwordService = $container->get(PasswordService::class);
        $eventHistoryCreator = $container->get(Creator::class);

        return (new UpdateUserSelfserve($authAdapter, $passwordService, $eventHistoryCreator))->__invoke($container, $requestedName, $options);
    }
}
