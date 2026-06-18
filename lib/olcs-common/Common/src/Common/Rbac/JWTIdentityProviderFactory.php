<?php

namespace Common\Rbac;

use Common\Auth\Service\RefreshTokenService;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Exception;
use Psr\Container\ContainerInterface;
use Laminas\Authentication\Storage\Session;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Session\Container;
use RuntimeException;

/**
 * @see JWTIdentityProvider
 */
class JWTIdentityProviderFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @throws Exception
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): JWTIdentityProvider
    {
        $sessionName = $container->get('config')['auth']['session_name'] ?? '';
        if (empty($sessionName)) {
            throw new RunTimeException("Missing auth.session_name from config");
        }

        return new JWTIdentityProvider(
            new Container($sessionName),
            $container->get('QuerySender'),
            $container->get(CacheEncryption::class),
            $container->get(RefreshTokenService::class),
            $container->get(Session::class)
        );
    }
}
