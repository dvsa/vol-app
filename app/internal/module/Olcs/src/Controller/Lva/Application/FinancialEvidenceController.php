<?php

/**
 * Internal Application Financial Evidence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
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
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Application Financial Evidence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialEvidenceController extends Lva\AbstractFinancialEvidenceController implements
    ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location = 'internal';

    protected $navigation;
    protected StringHelperService $stringHelper;
    protected RestrictionHelperService $restrictionHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormServiceManager $formServiceManager
     * @param ScriptFactory $scriptFactory
     * @param TableFactory $tableFactory
     * @param AnnotationBuilder $transferAnnotationBuilder
     * @param CommandService $commandService
     * @param StringHelperService $stringHelper
     * @param ApplicationFinancialEvidenceAdapter $lvaAdapter
     * @param FileUploadHelperService $fileUploadHelper
     * @param RestrictionHelperService $restrictionHelper
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        ScriptFactory $scriptFactory,
        TableFactory $tableFactory,
        AnnotationBuilder $transferAnnotationBuilder,
        CommandService $commandService,
        StringHelperService $stringHelper,
        ApplicationFinancialEvidenceAdapter $lvaAdapter,
        FileUploadHelperService $fileUploadHelper,
        RestrictionHelperService $restrictionHelper,
        $navigation
    ) {
        $this->stringHelper = $stringHelper;
        $this->restrictionHelper = $restrictionHelper;
        $this->navigation = $navigation;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $tableFactory,
            $transferAnnotationBuilder,
            $commandService,
            $lvaAdapter,
            $fileUploadHelper
        );
    }
}
