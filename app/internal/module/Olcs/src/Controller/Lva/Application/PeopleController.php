<?php

/**
 * Internal Application People Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Common\Controller\Lva\Adapters\ApplicationPeopleAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Application People Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PeopleController extends Lva\AbstractPeopleController implements ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location = 'internal';

    protected StringHelperService $stringHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FormServiceManager $formServiceManager
     * @param ScriptFactory $scriptFactory
     * @param VariationLvaService $variationLvaService
     * @param GuidanceHelperService $guidanceHelper
     * @param StringHelperService $stringHelper
     * @param ApplicationPeopleAdapter $lvaAdapter
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
        VariationLvaService $variationLvaService,
        GuidanceHelperService $guidanceHelper,
        StringHelperService $stringHelper,
        ApplicationPeopleAdapter $lvaAdapter,
        protected RestrictionHelperService $restrictionHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        protected $navigation
    ) {
        $this->stringHelper = $stringHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $scriptFactory,
            $variationLvaService,
            $guidanceHelper,
            $lvaAdapter,
            $flashMessengerHelper
        );
    }
}
