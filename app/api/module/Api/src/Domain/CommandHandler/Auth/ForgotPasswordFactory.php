<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Auth;

use Dvsa\Olcs\Auth\Service\PasswordService;
use Psr\Container\ContainerInterface;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ForgotPasswordFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ForgotPassword
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ForgotPassword
    {
        $pluginManager = $container;

        $authAdapter = $container->get(ValidatableAdapterInterface::class);
        $passwordService = $container->get(PasswordService::class);

        $instance = new ForgotPassword($authAdapter, $passwordService);
        return $instance->__invoke($pluginManager, $requestedName, $options);
    }
}
