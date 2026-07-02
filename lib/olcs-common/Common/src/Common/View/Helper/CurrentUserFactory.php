<?php

namespace Common\View\Helper;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use RuntimeException;
use LmcRbacMvc\Service\AuthorizationService;

class CurrentUserFactory implements FactoryInterface
{
    public const MSG_MISSING_ANALYTICS_CONFIG = 'Missing auth.user_unique_id_salt from config';

    /**
     * @param $requestedName
     * @param array|null $options
     * @throws RuntimeException
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CurrentUser
    {
        $config = $container->get('Config');

        if (!isset($config['auth']['user_unique_id_salt'])) {
            throw new RunTimeException(self::MSG_MISSING_ANALYTICS_CONFIG);
        }

        return new CurrentUser(
            $container->get(AuthorizationService::class),
            $config['auth']['user_unique_id_salt']
        );
    }
}
