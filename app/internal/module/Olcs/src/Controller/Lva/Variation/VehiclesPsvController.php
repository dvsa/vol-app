<?php

/**
 * Internal Variation Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva\AbstractVehiclesPsvController;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\VariationControllerInterface;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Variation Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesPsvController extends AbstractVehiclesPsvController implements VariationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'internal';
    protected StringHelperService $stringHelper;
    protected $navigation;

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
     * @param StringHelperService $stringHelper
     * @param $navigation
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
        StringHelperService $stringHelper,
        $navigation
    ) {
        $this->stringHelper = $stringHelper;

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
        $this->navigation = $navigation;
    }
}
