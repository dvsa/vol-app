<?php

/**
 * Variation Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva\AbstractController;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\VariationControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationOverviewTrait;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Olcs\Service\Helper\ApplicationOverviewHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractController implements VariationControllerInterface
{
    use VariationControllerTrait;
    use ApplicationOverviewTrait;

    protected $lva = 'variation';
    protected string $location = 'internal';

    protected ApplicationOverviewHelperService $applicationOverviewHelper;
    protected StringHelperService $stringHelper;
    protected FormHelperService $formHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param ApplicationOverviewHelperService $applicationOverviewHelper
     * @param StringHelperService $stringHelper
     * @param FormHelperService $formHelper
     * @param FormServiceManager $formServiceManager
     * @param $navigation
     * @param FlashMessengerHelperService $flashMessengerHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        ApplicationOverviewHelperService $applicationOverviewHelper,
        StringHelperService $stringHelper,
        FormHelperService $formHelper,
        protected FormServiceManager $formServiceManager,
        protected $navigation,
        protected FlashMessengerHelperService $flashMessengerHelper
    ) {
        $this->applicationOverviewHelper = $applicationOverviewHelper;
        $this->stringHelper = $stringHelper;
        $this->formHelper = $formHelper;

        parent::__construct($niTextTranslationUtil, $authService);
    }
}
