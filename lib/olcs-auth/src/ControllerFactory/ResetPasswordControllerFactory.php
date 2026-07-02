<?php

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Controller\ResetPasswordController;
use Dvsa\Olcs\Auth\Service\Auth\PasswordService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ResetPasswordControllerFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ResetPasswordController
    {
        /** @var FormHelperService $formHelperService */
        $formHelperService = $container->get('Helper\Form');

        /** @var FlashMessengerHelperService $flashMessenger */
        $flashMessenger = $container->get('Helper\FlashMessenger');

        /** @var PasswordService $passwordService */
        $passwordService = $container->get(PasswordService::class);

        return new ResetPasswordController(
            $formHelperService,
            $flashMessenger,
            $passwordService,
        );
    }
}
