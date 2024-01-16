<?php

/**
 * Variation Grant Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Olcs\Controller\Lva\Variation;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\VariationControllerInterface;
use Olcs\Controller\Lva\AbstractGrantController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Grant Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GrantController extends AbstractGrantController implements VariationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'internal';

    protected StringHelperService $stringHelper;
    protected $navigation;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormHelperService $formHelper
     * @param ScriptFactory $scriptFactory
     * @param TranslationHelperService $translationHelper
     * @param StringHelperService $stringHelper
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        FormHelperService $formHelper,
        ScriptFactory $scriptFactory,
        TranslationHelperService $translationHelper,
        StringHelperService $stringHelper,
        $navigation
    ) {
        $this->stringHelper = $stringHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $formHelper,
            $scriptFactory,
            $translationHelper
        );
        $this->navigation = $navigation;
    }
}
