<?php

namespace Olcs\Controller\Lva\Factory\Controller\Licence;

use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Licence\TrailersController;
use LmcRbacMvc\Service\AuthorizationService;

class TrailersControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return TrailersController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TrailersController
    {

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $dateHelper = $container->get(DateHelperService::class);
        $querySender = $container->get(QuerySender::class);
        $navigation = $container->get('navigation');

        return new TrailersController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $flashMessengerHelper,
            $tableFactory,
            $scriptFactory,
            $dateHelper,
            $querySender,
            $navigation
        );
    }
}
