<?php

namespace Olcs\Controller\Lva\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Application Undertakings Controller
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeclarationsInternalController extends \Olcs\Controller\Lva\AbstractDeclarationsInternalController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'internal';

    protected StringHelperService $stringHelper;
    protected $navigation;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormServiceManager $formServiceManager
     * @param TranslationHelperService $translationHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param StringHelperService $stringHelper
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormServiceManager $formServiceManager,
        TranslationHelperService $translationHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        StringHelperService $stringHelper,
        $navigation
    ) {
        $this->stringHelper = $stringHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formServiceManager,
            $translationHelper,
            $flashMessengerHelper
        );
        $this->navigation = $navigation;
    }
}
