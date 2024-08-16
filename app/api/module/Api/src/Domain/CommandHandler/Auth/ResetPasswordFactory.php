<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Auth;

use Psr\Container\ContainerInterface;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * @see ResetPasswordFactory
 */
class ResetPasswordFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ResetPassword
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ResetPassword
    {
        $adapter = $container->get(ValidatableAdapterInterface::class);
        $eventHistoryCreator = $container->get('EventHistoryCreator');
        $instance = new ResetPassword($adapter, $eventHistoryCreator);
        return $instance->__invoke($container, $requestedName, $options);
    }
}
