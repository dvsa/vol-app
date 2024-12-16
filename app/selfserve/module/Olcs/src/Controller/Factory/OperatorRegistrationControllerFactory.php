<?php

namespace Olcs\Controller\Factory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Controller\Mapper\CreateAccountMapper;
use Olcs\Controller\OperatorRegistrationController;
use Psr\Container\ContainerInterface;

class OperatorRegistrationControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OperatorRegistrationController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $urlHelper = $container->get(UrlHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formatDataMapper = $container->get(CreateAccountMapper::class);

        return new OperatorRegistrationController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $scriptFactory,
            $translationHelper,
            $urlHelper,
            $flashMessengerHelper,
            $formatDataMapper
        );
    }
}
