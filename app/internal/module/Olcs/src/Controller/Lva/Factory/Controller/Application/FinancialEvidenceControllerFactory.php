<?php

namespace Olcs\Controller\Lva\Factory\Controller\Application;

use Common\Controller\Lva\Adapters\ApplicationFinancialEvidenceAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Application\FinancialEvidenceController;
use LmcRbacMvc\Service\AuthorizationService;

class FinancialEvidenceControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return FinancialEvidenceController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FinancialEvidenceController
    {
        
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $tableFactory = $container->get(TableFactory::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $stringHelper = $container->get(StringHelperService::class);
        $commandService = $container->get(CommandService::class);
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        $lvaAdapter = $container->get(ApplicationFinancialEvidenceAdapter::class);
        $uploadHelper = $container->get(FileUploadHelperService::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $navigation = $container->get('Navigation');

        return new FinancialEvidenceController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $tableFactory,
            $transferAnnotationBuilder,
            $commandService,
            $stringHelper,
            $lvaAdapter,
            $uploadHelper,
            $restrictionHelper,
            $navigation
        );
    }
}
