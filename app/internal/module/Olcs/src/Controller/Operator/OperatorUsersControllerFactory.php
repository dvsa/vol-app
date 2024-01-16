<?php

namespace Olcs\Controller\Operator;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;

class OperatorUsersControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OperatorUsersController
    {
        $translationHelperService = $container->get(TranslationHelperService::class);
        assert($translationHelperService instanceof TranslationHelperService);

        $formHelper = $container->get(FormHelperService::class);
        assert($formHelper instanceof FormHelperService);

        $flashMessengerHelperService = $container->get(FlashMessengerHelperService::class);
        assert($flashMessengerHelperService instanceof FlashMessengerHelperService);

        $navigation = $container->get('Navigation');
        assert($navigation instanceof Navigation);

        return new OperatorUsersController(
            $translationHelperService,
            $formHelper,
            $flashMessengerHelperService,
            $navigation
        );
    }
}
