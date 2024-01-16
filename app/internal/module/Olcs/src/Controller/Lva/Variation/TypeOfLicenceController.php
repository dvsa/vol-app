<?php

/**
 * Internal Variation Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva\Variation\AbstractTypeOfLicenceController;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\VariationControllerInterface;
use Olcs\Controller\Lva\Traits;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Variation Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractTypeOfLicenceController implements VariationControllerInterface
{
    use Traits\VariationControllerTrait;

    protected string $location = 'internal';
    protected $lva = 'variation';
    protected StringHelperService $stringHelper;
    protected $navigation;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param ScriptFactory $scriptFactory
     * @param FormServiceManager $formServiceManager
     * @param FormHelperService $formHelper
     * @param StringHelperService $stringHelper
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        ScriptFactory $scriptFactory,
        FormServiceManager $formServiceManager,
        FormHelperService $formHelper,
        StringHelperService $stringHelper,
        $navigation
    ) {
        $this->stringHelper = $stringHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $scriptFactory,
            $formServiceManager,
            $formHelper
        );
        $this->navigation = $navigation;
    }
}
