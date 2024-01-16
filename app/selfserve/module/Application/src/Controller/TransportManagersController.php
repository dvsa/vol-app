<?php

namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva\Adapters\ApplicationTransportManagerAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\TransportManagerHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;

use Olcs\Controller\Lva\AbstractTransportManagersController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @see \Dvsa\Olcs\Application\Controller\Factory\TransportManagersControllerFactory
 * @see TransportManagersControllerTest
 */
class TransportManagersController extends AbstractTransportManagersController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location  = 'external';

    protected RestrictionHelperService $restrictionHelper;
    protected StringHelperService $stringHelper;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->initialized === true;
    }

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FormServiceManager $formServiceManager
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param ScriptFactory $scriptFactory
     * @param QueryService $queryService
     * @param CommandService $commandService
     * @param AnnotationBuilder $transferAnnotationBuilder
     * @param TransportManagerHelperService $transportManagerHelper
     * @param TranslationHelperService $translationHelper
     * @param RestrictionHelperService $restrictionHelper
     * @param StringHelperService $stringHelper
     * @param ApplicationTransportManagerAdapter $lvaAdapter
     * @param TableFactory $tableFactory
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FormServiceManager $formServiceManager,
        FlashMessengerHelperService $flashMessengerHelper,
        ScriptFactory $scriptFactory,
        QueryService $queryService,
        CommandService $commandService,
        AnnotationBuilder $transferAnnotationBuilder,
        TransportManagerHelperService $transportManagerHelper,
        TranslationHelperService $translationHelper,
        RestrictionHelperService $restrictionHelper,
        StringHelperService $stringHelper,
        ApplicationTransportManagerAdapter $lvaAdapter,
        TableFactory $tableFactory,
        FileUploadHelperService $uploadHelper
    ) {
        $this->restrictionHelper = $restrictionHelper;
        $this->stringHelper = $stringHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $flashMessengerHelper,
            $scriptFactory,
            $queryService,
            $commandService,
            $transferAnnotationBuilder,
            $transportManagerHelper,
            $translationHelper,
            $lvaAdapter,
            $tableFactory,
            $uploadHelper
        );
    }
}
