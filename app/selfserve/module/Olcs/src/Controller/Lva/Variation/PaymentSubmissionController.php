<?php

namespace Olcs\Controller\Lva\Variation;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\AbstractPaymentSubmissionController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use ZfcRbac\Service\AuthorizationService;

/**
 * External Variation Payment Submission Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PaymentSubmissionController extends AbstractPaymentSubmissionController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'external';

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param TranslationHelperService $translationHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param TableFactory $tableFactory
     * @param FormHelperService $formHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        TranslationHelperService $translationHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        TableFactory $tableFactory,
        FormHelperService $formHelper
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $translationHelper,
            $flashMessengerHelper,
            $tableFactory,
            $formHelper
        );
    }
}
