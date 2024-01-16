<?php

/**
 * Internal Variation Vehicles Declarations Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\DataHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\VariationControllerInterface;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Variation Vehicles Declarations Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesDeclarationsController extends Lva\AbstractVehiclesDeclarationsController implements
    VariationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'internal';
    protected StringHelperService $stringHelper;
    protected $navigation;
    protected FlashMessengerHelperService $flashMessengerHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FormServiceManager $formServiceManager
     * @param ScriptFactory $scriptFactory
     * @param DataHelperService $dataHelper
     * @param StringHelperService $stringHelper
     * @param $navigation
     * @param FlashMessengerHelperService $flashMessengerHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FormServiceManager $formServiceManager,
        ScriptFactory $scriptFactory,
        DataHelperService $dataHelper,
        StringHelperService $stringHelper,
        $navigation,
        FlashMessengerHelperService $flashMessengerHelper
    ) {
        $this->stringHelper = $stringHelper;
        $this->flashMessengerHelper = $flashMessengerHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $scriptFactory,
            $dataHelper
        );
        $this->navigation = $navigation;
    }
}
