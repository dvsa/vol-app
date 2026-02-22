<?php

/**
 * Licence Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractVariationController;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationController extends AbstractVariationController implements LicenceControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected string $location = 'internal';

    /**
     * @param NiTextTranslation        $niTextTranslationUtil
     * @param AuthorizationService     $authService
     * @param TranslationHelperService $translationHelper
     * @param $processingCreateVariation
     * @param FormHelperService        $formHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        TranslationHelperService $translationHelper,
        $processingCreateVariation,
        protected FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        protected $navigation
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $translationHelper,
            $processingCreateVariation,
            $flashMessengerHelper
        );
    }
}
