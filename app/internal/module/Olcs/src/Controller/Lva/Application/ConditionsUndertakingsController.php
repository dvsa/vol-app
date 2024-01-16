<?php

/**
 * Internal Application Conditions Undertakings Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Common\Controller\Lva\Adapters\ApplicationConditionsUndertakingsAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Application Conditions Undertakings Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionsUndertakingsController extends Lva\AbstractConditionsUndertakingsController implements
    ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location = 'internal';

    protected StringHelperService $stringHelper;
    protected RestrictionHelperService $restrictionHelper;
    protected $navigation;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormServiceManager $formServiceManager
     * @param TableFactory $tableFactory
     * @param StringHelperService $stringHelper
     * @param ApplicationConditionsUndertakingsAdapter $lvaAdapter
     * @param RestrictionHelperService $restrictionHelper
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        TableFactory $tableFactory,
        StringHelperService $stringHelper,
        ApplicationConditionsUndertakingsAdapter $lvaAdapter,
        RestrictionHelperService $restrictionHelper,
        $navigation
    ) {
        $this->stringHelper = $stringHelper;
        $this->restrictionHelper = $restrictionHelper;
        $this->navigation = $navigation;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $tableFactory,
            $lvaAdapter
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    protected function getRenderVariables()
    {
        return array('title' => null);
    }
}
