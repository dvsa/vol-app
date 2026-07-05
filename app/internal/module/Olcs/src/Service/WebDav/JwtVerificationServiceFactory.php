<?php

declare(strict_types=1);

namespace Olcs\Service\WebDav;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationServiceFactory;
use Psr\Container\ContainerInterface;

class JwtVerificationServiceFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): JwtVerificationService
    {
        $config = $container->get('Config');
        $webdavConfig = $config[WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_NAMESPACE] ?? [];
        $privateKeyValue = $webdavConfig[WebDavJsonWebTokenGenerationServiceFactory::CONFIG_KEY_PRIVATE_KEY] ?? '';

        $privateKey = @file_exists($privateKeyValue) ? file_get_contents($privateKeyValue) : base64_decode($privateKeyValue, true);

        if (!$privateKey) {
            throw new \InvalidArgumentException('WebDAV private key is not configured or invalid');
        }

        $privateKeyResource = openssl_pkey_get_private($privateKey);
        if ($privateKeyResource === false) {
            throw new \InvalidArgumentException('WebDAV private key PEM is malformed');
        }

        $keyDetails = openssl_pkey_get_details($privateKeyResource);
        if ($keyDetails === false || !isset($keyDetails['key'])) {
            throw new \InvalidArgumentException('Failed to extract public key from WebDAV private key');
        }

        return new JwtVerificationService($keyDetails['key']);
    }
}
