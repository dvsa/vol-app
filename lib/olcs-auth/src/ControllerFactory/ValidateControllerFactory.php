<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Dvsa\Olcs\Auth\Controller\ValidateController;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Identity\IdentityProviderInterface;

/**
 * @see ValidateController
 */
class ValidateControllerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ValidateController
    {
        $identityProvider = $container->get(IdentityProviderInterface::class);
        return new ValidateController($identityProvider);
    }
}
