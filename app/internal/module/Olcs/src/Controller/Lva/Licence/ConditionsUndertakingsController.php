<?php

/**
 * Internal Licence Conditions Undertakings Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Common\Controller\Lva\Adapters\LicenceConditionsUndertakingsAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Licence Conditions Undertakings Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionsUndertakingsController extends Lva\AbstractConditionsUndertakingsController implements
    LicenceControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected string $location = 'internal';

    protected $navigation;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormServiceManager $formServiceManager
     * @param TableFactory $tableFactory
     * @param LicenceConditionsUndertakingsAdapter $lvaAdapter
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        TableFactory $tableFactory,
        LicenceConditionsUndertakingsAdapter $lvaAdapter,
        $navigation
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $tableFactory,
            $lvaAdapter
        );
        $this->navigation = $navigation;
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    protected function getRenderVariables()
    {
        return ['title' => null];
    }
}
