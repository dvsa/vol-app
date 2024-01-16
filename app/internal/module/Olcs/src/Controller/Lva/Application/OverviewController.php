<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractController;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Olcs\Controller\Lva\Traits\ApplicationOverviewTrait;
use Olcs\Service\Helper\ApplicationOverviewHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OverviewController extends AbstractController implements ApplicationControllerInterface
{
    use ApplicationControllerTrait;
    use ApplicationOverviewTrait;

    protected $lva = 'application';
    protected string $location = 'internal';

    protected StringHelperService $stringHelper;
    protected ApplicationOverviewHelperService $applicationOverviewHelper;
    protected FormHelperService $formHelper;
    protected RestrictionHelperService $restrictionHelper;
    protected FlashMessengerHelperService $flashMessengerHelper;
    protected $navigation;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param StringHelperService $stringHelper
     * @param ApplicationOverviewHelperService $applicationOverviewHelper
     * @param FormHelperService $formHelper
     * @param RestrictionHelperService $restrictionHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        StringHelperService $stringHelper,
        ApplicationOverviewHelperService $applicationOverviewHelper,
        FormHelperService $formHelper,
        RestrictionHelperService $restrictionHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        $navigation
    ) {
        $this->stringHelper = $stringHelper;
        $this->applicationOverviewHelper = $applicationOverviewHelper;
        $this->formHelper = $formHelper;
        $this->restrictionHelper = $restrictionHelper;
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->navigation = $navigation;

        parent::__construct($niTextTranslationUtil, $authService);
    }
}
