<?php

/**
 * External Application Licence History Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use ZfcRbac\Service\AuthorizationService;

/**
 * External Application Licence History Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceHistoryController extends Lva\AbstractLicenceHistoryController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location  = 'external';

    protected RestrictionHelperService $restrictionHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormServiceManager $formServiceManager
     * @param ScriptFactory $scriptFactory
     * @param StringHelperService $stringHelper
     * @param TableFactory $tableFactory
     * @param FormHelperService $formHelper
     * @param RestrictionHelperService $restrictionHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        ScriptFactory $scriptFactory,
        StringHelperService $stringHelper,
        TableFactory $tableFactory,
        FormHelperService $formHelper,
        RestrictionHelperService $restrictionHelper
    ) {
        $this->restrictionHelper = $restrictionHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $stringHelper,
            $tableFactory,
            $formHelper
        );
    }
}
