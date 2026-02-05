<?php

/**
 * External Licence Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractVehiclesPsvController;
use Common\Controller\Lva\Adapters\LicenceLvaAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * External Licence Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesPsvController extends AbstractVehiclesPsvController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected string $location = 'external';

    #[\Override]
    protected function getScripts(): array
    {
        $scripts  = parent::getScripts();
        $scripts[] = 'vehicles';
        return $scripts;
    }

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
     * @param LicenceLvaAdapter $lvaAdapter
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
        protected LicenceLvaAdapter $lvaAdapter
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
