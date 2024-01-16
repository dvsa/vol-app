<?php

/**
 * Internal Application Vehicles Declarations Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\DataHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Application Vehicles Declarations Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesDeclarationsController extends Lva\AbstractVehiclesDeclarationsController implements
    ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location = 'internal';

    protected StringHelperService $stringHelper;
    protected RestrictionHelperService $restrictionHelper;
    protected FlashMessengerHelperService $flashMessengerHelper;

    protected $navigation;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FormServiceManager $formServiceManager
     * @param ScriptFactory $scriptFactory
     * @param DataHelperService $dataHelper
     * @param StringHelperService $stringHelper
     * @param RestrictionHelperService $restrictionHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FormServiceManager $formServiceManager,
        ScriptFactory $scriptFactory,
        DataHelperService $dataHelper,
        StringHelperService $stringHelper,
        RestrictionHelperService $restrictionHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        $navigation
    ) {
        $this->stringHelper = $stringHelper;
        $this->restrictionHelper = $restrictionHelper;
        $this->navigation = $navigation;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $scriptFactory,
            $dataHelper,
            $flashMessengerHelper
        );
    }
}
