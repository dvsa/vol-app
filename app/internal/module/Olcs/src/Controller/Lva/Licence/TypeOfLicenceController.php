<?php

/**
 * Internal Licence Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\Licence\AbstractTypeOfLicenceController;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Licence Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractTypeOfLicenceController implements LicenceControllerInterface
{
    use Traits\LicenceControllerTrait;

    protected string $location = 'internal';
    protected $lva = 'licence';

    protected FormHelperService $formHelper;
    protected $navigation;

    /**
     * @param NiTextTranslation           $niTextTranslationUtil
     * @param AuthorizationService        $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param ScriptFactory               $scriptFactory
     * @param FormServiceManager          $formServiceManager
     * @param FormHelperService           $formHelper
     * @param VariationLvaService         $variationLvaService
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        ScriptFactory $scriptFactory,
        FormServiceManager $formServiceManager,
        FormHelperService $formHelper,
        VariationLvaService $variationLvaService,
        $navigation
    ) {
        $this->formHelper = $formHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $scriptFactory,
            $formServiceManager,
            $variationLvaService
        );

        $this->navigation = $navigation;
    }
}
