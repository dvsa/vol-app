<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\DigitalSignature;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransactioningCommandHandler;
use Dvsa\Olcs\Api\Service\EventHistory\Creator;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class UpdateSurrenderFactory implements FactoryInterface
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
        $eventHistoryCreator = $container->get(Creator::class);

        return (new UpdateSurrender($eventHistoryCreator))->__invoke($container, $requestedName, $options);
    }
}
