<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Auth;

use Psr\Container\ContainerInterface;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * @see RefreshTokens
 */
class RefreshTokensFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return RefreshTokens
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RefreshTokens
    {
        $adapter = $container->get(ValidatableAdapterInterface::class);
        $instance = new RefreshTokens($adapter);
        return $instance->__invoke($container, $requestedName, $options);
    }
}
