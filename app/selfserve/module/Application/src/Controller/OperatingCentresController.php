<?php

namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use ZfcRbac\Service\AuthorizationService;

/**
 * External Application Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location  = 'external';

    protected RestrictionHelperService $restrictionHelper;
    protected StringHelperService $stringHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormServiceManager $formServiceManager
     * @param TranslationHelperService $translationHelper
     * @param ScriptFactory $scriptFactory
     * @param VariationLvaService $variationLvaService
     * @param RestrictionHelperService $restrictionHelper
     * @param StringHelperService $stringHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        TranslationHelperService $translationHelper,
        ScriptFactory $scriptFactory,
        VariationLvaService $variationLvaService,
        RestrictionHelperService $restrictionHelper,
        StringHelperService $stringHelper
    ) {
        $this->restrictionHelper = $restrictionHelper;
        $this->stringHelper = $stringHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $translationHelper,
            $scriptFactory,
            $variationLvaService
        );
    }
}
