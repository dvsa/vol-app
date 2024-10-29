<?php

declare(strict_types=1);

namespace Olcs\Mvc;

use Common\Rbac\JWTIdentityProvider;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class TermsAgreedListenerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TermsAgreedListener
    {
        return new TermsAgreedListener(
            $container->get(JWTIdentityProvider::class),
            $container->get('Helper\Url')
        );
    }
}
