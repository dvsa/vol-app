<?php

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Controller\ForgotPasswordController;
use Dvsa\Olcs\Auth\Service\Auth\PasswordService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ForgotPasswordControllerFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ForgotPasswordController
    {
        /** @var FormHelperService $formHelperService */
        $formHelperService = $container->get('Helper\Form');

        /** @var PasswordService $passwordService */
        $passwordService = $container->get(PasswordService::class);

        return new ForgotPasswordController(
            $formHelperService,
            $passwordService,
        );
    }
}
