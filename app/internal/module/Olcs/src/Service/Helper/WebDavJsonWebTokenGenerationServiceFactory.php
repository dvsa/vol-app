<?php

namespace Olcs\Service\Helper;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class WebDavJsonWebTokenGenerationServiceFactory implements FactoryInterface
{
    public const CONFIG_KEY_NAMESPACE = 'webdav';
    public const CONFIG_KEY_DEFAULT_LIFETIME_SECONDS = 'default_lifetime_seconds';
    public const CONFIG_KEY_PRIVATE_KEY = 'private_key';
    public const CONFIG_KEY_URL_PATTERN = 'url_pattern';

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return WebDavJsonWebTokenGenerationService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): WebDavJsonWebTokenGenerationService
    {
        $config = $container->get('Config');
        if (array_key_exists(static::CONFIG_KEY_NAMESPACE, $config)) {
            $config = $config[static::CONFIG_KEY_NAMESPACE];
        } else {
            throw new \InvalidArgumentException('Config does not contain CONFIG_KEY: ' . static::CONFIG_KEY_NAMESPACE);
        }

        return new WebDavJsonWebTokenGenerationService(
            $this->getPrivateKeyFromConfig($config),
            $this->getDefaultLifetimeSecondsFromConfig($config),
            $this->getUrlPatternConfig($config)
        );
    }

    protected function getDefaultLifetimeSecondsFromConfig(array $config): int
    {
        $defaultLifetimeSeconds = $config[static::CONFIG_KEY_DEFAULT_LIFETIME_SECONDS] ?? null;
        if (empty($defaultLifetimeSeconds)) {
            throw new \InvalidArgumentException('Config key default_lifetime_seconds: value must be set and not empty()', 0x10);
        }

        return $defaultLifetimeSeconds;
    }

    protected function getPrivateKeyFromConfig(array $config): string
    {
        $privateKeyConfigValue = $config[static::CONFIG_KEY_PRIVATE_KEY] ?? null;
        if (empty($privateKeyConfigValue)) {
            throw new \InvalidArgumentException('Config key private_key: value must be set and not empty()', 0x20);
        }

        return $privateKeyConfigValue;
    }

    protected function getUrlPatternConfig(array $config): string
    {
        $urlPattern = $config[static::CONFIG_KEY_URL_PATTERN] ?? null;
        if (empty($urlPattern)) {
            throw new \InvalidArgumentException('Config key url_pattern: value must be set and not empty()', 0x30);
        }

        return $urlPattern;
    }
}
