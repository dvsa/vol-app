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

        // Constructs with whatever secret is configured (possibly empty). SessionGrantService
        // validates the secret at use time, so gate=none flows that construct but never use it
        // (e.g. the shared Download handler) work with no secret; OTP flows fail loudly if unset.
        return new SessionGrantService((string) ($config['retrieval']['session_secret'] ?? ''));
    }
}
