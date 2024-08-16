<?php

namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva\AbstractVehiclesPsvController;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * External Application Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesPsvController extends AbstractVehiclesPsvController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location  = 'external';

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FormServiceManager $formServiceManager
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param ScriptFactory $scriptFactory
     * @param UrlHelperService $urlHelper
     * @param ResponseHelperService $responseHelper
     * @param TableFactory $tableFactory
     * @param TranslationHelperService $translatorHelper
     * @param GuidanceHelperService $guidanceHelper
     * @param RestrictionHelperService $restrictionHelper
     * @param StringHelperService $stringHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FormServiceManager $formServiceManager,
        FlashMessengerHelperService $flashMessengerHelper,
        ScriptFactory $scriptFactory,
        UrlHelperService $urlHelper,
        ResponseHelperService $responseHelper,
        TableFactory $tableFactory,
        TranslationHelperService $translatorHelper,
        GuidanceHelperService $guidanceHelper,
        protected RestrictionHelperService $restrictionHelper,
        protected StringHelperService $stringHelper
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $flashMessengerHelper,
            $scriptFactory,
            $urlHelper,
            $responseHelper,
            $tableFactory,
            $translatorHelper,
            $guidanceHelper
        );
    }
}
