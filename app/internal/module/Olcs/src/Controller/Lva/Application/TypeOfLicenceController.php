<?php

/**
 * Internal Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\Application\AbstractTypeOfLicenceController;
use Common\FormService\FormServiceManager;
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
 * Internal Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractTypeOfLicenceController implements ApplicationControllerInterface
{
    use ApplicationControllerTrait {
        ApplicationControllerTrait::getSectionsForView as genericGetSectionsForView;
    }

    protected string $location = 'internal';
    protected $lva = 'application';

    protected StringHelperService $stringHelper;
    protected RestrictionHelperService $restrictionHelper;

    protected $navigation;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param ScriptFactory $scriptFactory
     * @param FormServiceManager $formServiceManager
     * @param StringHelperService $stringHelper
     * @param RestrictionHelperService $restrictionHelper
     * @param FormHelperService $formHelper
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        ScriptFactory $scriptFactory,
        FormServiceManager $formServiceManager,
        StringHelperService $stringHelper,
        RestrictionHelperService $restrictionHelper,
        FormHelperService $formHelper,
        $navigation
    ) {
        $this->stringHelper = $stringHelper;
        $this->restrictionHelper = $restrictionHelper;
        $this->navigation = $navigation;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $scriptFactory,
            $formServiceManager,
            $formHelper
        );
    }
}
