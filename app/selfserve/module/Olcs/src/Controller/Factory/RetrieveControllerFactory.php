<?php

namespace Olcs\Controller\Factory;

use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Http\PhpEnvironment\RemoteAddress;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Controller\RetrieveController;
use Psr\Container\ContainerInterface;

class RetrieveControllerFactory implements FactoryInterface
{
    /**
     * @param string     $requestedName
     * @param array|null $options
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): RetrieveController
    {
        $config = $container->get('config');
        $retrieveConfig = $config['retrieve_document'] ?? [];

        return new RetrieveController(
            $container->get(NiTextTranslation::class),
            $container->get(AuthorizationService::class),
            $container->get(FormHelperService::class),
            new RemoteAddress(),
            is_string($retrieveConfig['presigned_fetch_proxy'] ?? null) ? $retrieveConfig['presigned_fetch_proxy'] : '',
            (int) ($retrieveConfig['presigned_fetch_timeout'] ?? 30),
            (bool) ($retrieveConfig['grant_cookie_secure'] ?? true),
        );
    }
}
