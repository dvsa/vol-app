<?php

/**
 * Licence Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractVariationController;
use Common\Controller\Lva\Adapters\LicenceLvaAdapter;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use ZfcRbac\Service\AuthorizationService;

/**
 * Licence Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationController extends AbstractVariationController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected string $location = 'external';

    protected LicenceLvaAdapter $licenceLvaAdapter;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param TranslationHelperService $translationHelper
     * @param $processingCreateVariation
     * @param LicenceLvaAdapter $licenceLvaAdapter
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        TranslationHelperService $translationHelper,
        $processingCreateVariation,
        LicenceLvaAdapter $licenceLvaAdapter,
        FlashMessengerHelperService $flashMessengerHelper
    ) {
        $this->licenceLvaAdapter = $licenceLvaAdapter;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $translationHelper,
            $processingCreateVariation,
            $flashMessengerHelper
        );
    }
}
