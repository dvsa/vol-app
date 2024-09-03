<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth\Adapter;

use Dvsa\Authentication\Ldap\Client;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class LdapAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LdapAdapter
    {
        /**
         * @var Client $client
         */
        $client = $container->get(Client::class);

        // Using an object class without a `userAccountControl` attribute.
        $client->setUserAccountControlAttribute(null);

        return new LdapAdapter($client);
    }
}
