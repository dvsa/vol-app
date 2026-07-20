<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Retrieval;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class SessionGrantServiceFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SessionGrantService
    {
        $config = $container->get('config');

        // Constructing with an empty/short secret throws — but only lazily, when an OTP flow
        // actually needs a grant. Non-OTP envs never instantiate this, so they boot fine.
        return new SessionGrantService((string) ($config['retrieval']['session_secret'] ?? ''));
    }
}
