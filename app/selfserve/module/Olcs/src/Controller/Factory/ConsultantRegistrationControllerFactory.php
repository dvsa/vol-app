<?php

namespace Olcs\Controller\Factory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\ConsultantRegistrationController;
use Olcs\Controller\Mapper\CreateAccountMapper;
use Olcs\Session\ConsultantRegistration;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\UserRegistrationController;
use LmcRbacMvc\Service\AuthorizationService;

class ConsultantRegistrationControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return UserRegistrationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConsultantRegistrationController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $urlHelper = $container->get(UrlHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $consultantRegistrationSession = $container->get(ConsultantRegistration::class);
        $formatDataMapper = $container->get(CreateAccountMapper::class);

        return new ConsultantRegistrationController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $scriptFactory,
            $translationHelper,
            $urlHelper,
            $flashMessengerHelper,
            $consultantRegistrationSession,
            $formatDataMapper
        );
    }
}
