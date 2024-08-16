<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth\Client;

use Dvsa\Authentication\Ldap\Client;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Ldap\Ldap;

class LdapClientFactory implements FactoryInterface
{
    public const CONFIG_NAMESPACE = 'auth';
    public const CONFIG_ADAPTERS = 'adapters';
    public const CONFIG_ADAPTER = 'ldap';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Client
    {
        $config = $container->get('Config')[self::CONFIG_NAMESPACE][static::CONFIG_ADAPTERS][static::CONFIG_ADAPTER];

        $ldap = Ldap::create('ext_ldap', [
            'host' => $config['host'],
            'port' => $config['port'],
            'encryption' => 'none',
        ]);

        $ldap->bind($config['admin_dn'], $config['admin_password']);

        return new Client(
            $ldap,
            $config['rdn'],
            $config['base_dn'],
            $config['object_class'],
            $config['secret'],
        );
    }
}
