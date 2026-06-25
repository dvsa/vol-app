<?php

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Dvsa\Olcs\Auth\Controller\LogoutController;
use Psr\Container\ContainerInterface;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\Response;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Session\Container;
use RuntimeException;

class LogoutControllerFactory implements FactoryInterface
{
    protected const HEADER_REALM_KEY = 'HTTP_X_REALM';

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LogoutController
    {
        /** @var array $config */
        $config = $container->get('Config');

        /** @var Request $requestService */
        $requestService = $container->get('Request');

        $sessionName = $config['auth']['session_name'] ?? '';
        if (empty($sessionName)) {
            throw new RunTimeException("Missing auth.session_name from config");
        }

        $session = new Container($sessionName);

        return new LogoutController(
            $this->isSelfServeUser($requestService),
            $this->getSelfServeLogoutUrl($config),
            $session
        );
    }

    /**
     * Check if the current session is self serve
     *
     * @param Request $requestService Laminas request service
     *
     * @return bool
     */
    private function isSelfServeUser(Request $requestService)
    {
        $realmName = $requestService->getServer(self::HEADER_REALM_KEY);
        return ($realmName === 'selfserve' || empty($realmName));
    }

    /**
     * Retrieve URL to use when we redirect Self Serve user
     *
     * @param array $config Config from service locator
     *
     * @return string
     */
    private function getSelfServeLogoutUrl(array $config)
    {
        if (empty($config['selfserve_logout_redirect_url'])) {
            throw new \InvalidArgumentException('Selfserve logout redirect is not available in config');
        }

        return $config['selfserve_logout_redirect_url'];
    }
}
