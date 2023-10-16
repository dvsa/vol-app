<?php

/**
 * Application Withdraw Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Olcs\Controller\Lva\Application;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\AbstractWithdrawController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use ZfcRbac\Service\AuthorizationService;

/**
 * Application Withdraw Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class WithdrawController extends AbstractWithdrawController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location = 'internal';

    protected StringHelperService $stringHelper;
    protected RestrictionHelperService $restrictionHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param StringHelperService $stringHelper
     * @param RestrictionHelperService $restrictionHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        StringHelperService $stringHelper,
        RestrictionHelperService $restrictionHelper
    ) {
        $this->stringHelper = $stringHelper;
        $this->restrictionHelper = $restrictionHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $translationHelper,
            $formHelper
        );
    }
}
