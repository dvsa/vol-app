<?php

/**
 * Licence Fees Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Licence\Fees;

use Common\Controller\Traits\GenericReceipt;
use Common\Service\Data\PluginManager;
use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Licence\LicenceController;
use Olcs\Controller\Traits\FeesActionTrait;
use LmcRbacMvc\Identity\IdentityProviderInterface;

/**
 * Licence Fees Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceFeesController extends LicenceController implements LeftViewProvider
{
    use FeesActionTrait;
    use GenericReceipt;

    protected PluginManager $dataServiceManager;
    protected OppositionHelperService $oppositionHelper;
    protected ComplaintsHelperService $complaintsHelper;
    protected FlashMessengerHelperService $flashMessengerHelper;
    protected UrlHelperService $urlHelper;
    protected IdentityProviderInterface $identityProvider;
    protected TranslationHelperService $translationHelper;
    protected DateHelperService $dateHelper;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        OppositionHelperService $oppositionHelper,
        ComplaintsHelperService $complaintsHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        UrlHelperService $urlHelper,
        IdentityProviderInterface $identityProvider,
        TranslationHelperService $translationHelper,
        DateHelperService $dateHelper,
        $navigation
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $oppositionHelper,
            $complaintsHelper,
            $navigation,
            $flashMessengerHelper
        );
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->urlHelper = $urlHelper;
        $this->identityProvider = $identityProvider;
        $this->translationHelper = $translationHelper;
        $this->dateHelper = $dateHelper;
    }

    /**
     * render layout
     *
     * @param ViewModel $view view
     *
     * @return ViewModel
     */
    protected function renderLayout($view)
    {
        $tmp = $this->getViewWithLicence($view->getVariables());
        $view->setVariables($tmp->getVariables());

        return $this->renderView($view);
    }

    /**
     * getFeesRoute
     * Route (prefix) for fees action redirects
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     *
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'licence/fees';
    }

    /**
     * The fees route redirect params
     *
     * see Olcs\Controller\Traits\FeesActionTrait
     *
     * @return array
     */
    protected function getFeesRouteParams()
    {
        return [
            'licence' => $this->params()->fromRoute('licence')
        ];
    }

    /**
     * The controller specific fees table params
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     *
     * @return array
     */
    protected function getFeesTableParams()
    {
        return [
            'licence' => $this->params()->fromRoute('licence'),
            'status' => 'current',
        ];
    }

    /**
     * getFeetypeDtoData
     *
     * @return array
     */
    protected function getFeeTypeDtoData()
    {
        return ['licence' => $this->params()->fromRoute('licence')];
    }

    /**
     * getCreateFeeDtoData
     *
     * @param array $formData formData
     *
     * @return array
     */
    protected function getCreateFeeDtoData($formData)
    {
        return [
            'invoicedDate' => $formData['fee-details']['createdDate'],
            'feeType' => $formData['fee-details']['feeType'],
            'licence' => $this->params()->fromRoute('licence'),
        ];
    }
}
