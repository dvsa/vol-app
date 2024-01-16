<?php

/**
 * Variation Withdraw Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Olcs\Controller\Lva\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\VariationControllerInterface;
use Olcs\Controller\Lva\AbstractWithdrawController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Withdraw Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class WithdrawController extends AbstractWithdrawController implements VariationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'internal';
    protected StringHelperService $stringHelper;
    protected FormServiceManager $formServiceManager;
    protected $navigation;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param StringHelperService $stringHelper
     * @param FormServiceManager $formServiceManager
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        StringHelperService $stringHelper,
        FormServiceManager $formServiceManager,
        $navigation
    ) {
        $this->stringHelper = $stringHelper;
        $this->formServiceManager = $formServiceManager;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $translationHelper,
            $formHelper
        );
        $this->navigation = $navigation;
    }
}
