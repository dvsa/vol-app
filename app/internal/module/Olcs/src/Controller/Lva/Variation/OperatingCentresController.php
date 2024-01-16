<?php

/**
 * Internal Operating Centres Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Common\Controller\Lva\Traits\VariationOperatingCentresControllerTrait;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\VariationControllerInterface;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Operating Centres Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController implements
    VariationControllerInterface
{
    use VariationControllerTrait;
    use VariationOperatingCentresControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'internal';

    protected StringHelperService $stringHelper;
    protected $navigation;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormServiceManager $formServiceManager
     * @param TranslationHelperService $translationHelper
     * @param ScriptFactory $scriptFactory
     * @param VariationLvaService $variationLvaService
     * @param StringHelperService $stringHelper
     * @param FileUploadHelperService $uploadHelper
     * @param $navigation
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
        StringHelperService $stringHelper,
        FileUploadHelperService $uploadHelper,
        $navigation
    ) {
        $this->stringHelper = $stringHelper;
        $this->navigation = $navigation;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $translationHelper,
            $scriptFactory,
            $variationLvaService,
            $uploadHelper
        );
    }
}
