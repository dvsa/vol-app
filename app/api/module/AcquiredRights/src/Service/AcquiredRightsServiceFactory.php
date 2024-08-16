<?php

namespace Dvsa\Olcs\AcquiredRights\Service;

use Dvsa\Olcs\AcquiredRights\Client\AcquiredRightsClient;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Logging\Log\Logger;
use phpDocumentor\Reflection\Types\Boolean;

class AcquiredRightsServiceFactory implements FactoryInterface
{
    protected const CONFIG_NAMESPACE = 'acquired_rights';
    protected const CONFIG_KEY_EXPIRY = 'expiry';
    protected const CONFIG_KEY_CHECK_ENABLED = 'check_enabled';

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return AcquiredRightsService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AcquiredRightsService
    {

        $logger = $container->get('Logger');
        $acquiredRightsExpiry = $this->getAcquiredRightsExpiryFromConfig($container->get('config'));
        $acquiredRightsCheckEnabled = $this->getAcquiredRightsCheckEnabledFromConfig($container->get('config'));
        $acquiredRightsClient = $container->get(AcquiredRightsClient::class);

        return new AcquiredRightsService($logger, $acquiredRightsClient, $acquiredRightsExpiry, $acquiredRightsCheckEnabled);
    }

    /**
     * @return \DateTimeImmutable
     */
    protected function getAcquiredRightsExpiryFromConfig(array $config): \DateTimeImmutable
    {
        $acquiredRightsExpiry = $config[static::CONFIG_NAMESPACE][static::CONFIG_KEY_EXPIRY] ?? null;
        if (empty($acquiredRightsExpiry)) {
            $errorMsg = sprintf(
                'Configuration is undefined or empty(): %s -> %s',
                static::CONFIG_NAMESPACE,
                static::CONFIG_KEY_EXPIRY
            );
            Logger::err($errorMsg);
            throw new \InvalidArgumentException($errorMsg);
        }
        if (!$acquiredRightsExpiry instanceof \DateTimeImmutable) {
            $errorMsg = sprintf(
                'Value must be instance of \DateTimeImmutable: %s -> %s',
                static::CONFIG_NAMESPACE,
                static::CONFIG_KEY_EXPIRY
            );
            Logger::err($errorMsg);
            throw new \InvalidArgumentException($errorMsg);
        }
        return $acquiredRightsExpiry;
    }

    /**
     * @return bool
     */
    protected function getAcquiredRightsCheckEnabledFromConfig(array $config): bool
    {
        $acquiredRightsCheckEnabled = $config[static::CONFIG_NAMESPACE][static::CONFIG_KEY_CHECK_ENABLED] ?? null;
        if (is_null($acquiredRightsCheckEnabled)) {
            $errorMsg = sprintf(
                'Configuration is undefined or null: %s -> %s',
                static::CONFIG_NAMESPACE,
                static::CONFIG_KEY_CHECK_ENABLED
            );
            Logger::err($errorMsg);
            throw new \InvalidArgumentException($errorMsg);
        }
        if (!is_bool($acquiredRightsCheckEnabled)) {
            $errorMsg = sprintf(
                'Value must be instance of bool: %s -> %s',
                static::CONFIG_NAMESPACE,
                static::CONFIG_KEY_CHECK_ENABLED
            );
            Logger::err($errorMsg);
            throw new \InvalidArgumentException($errorMsg);
        }
        return $acquiredRightsCheckEnabled;
    }
}
