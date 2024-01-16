<?php

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Application\Fees;

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
use Olcs\Controller\Application\ApplicationController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits\FeesActionTrait;
use LmcRbacMvc\Identity\IdentityProviderInterface;

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationFeesController extends ApplicationController implements LeftViewProvider
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
        PluginManager $dataServiceManager,
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
            $dataServiceManager,
            $oppositionHelper,
            $complaintsHelper,
            $flashMessengerHelper,
            $navigation
        );
        $this->urlHelper = $urlHelper;
        $this->identityProvider = $identityProvider;
        $this->translationHelper = $translationHelper;
        $this->dateHelper = $dateHelper;
    }

    /**
     * render Layout
     *
     * @param string $view      view
     * @param null   $pageTitle pageTitle
     *
     * @return mixed
     */
    protected function renderLayout($view, $pageTitle = null)
    {
        return $this->render($view, $pageTitle);
    }

    /**
     * Route (prefix) for fees action redirects
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     *
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'lva-application/fees';
    }

    /**
     * The fees route redirect params
     *
     * @see Olcs\Controller\Traits\FeesActionTrait
     *
     * @return array
     */
    protected function getFeesRouteParams()
    {
        return [
            'application' => $this->getFromRoute('application')
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
            'licence' => $this->getLicenceIdForApplication(),
            'status' => 'current',
        ];
    }

    /**
     * fee type D to data get Method
     *
     * @return array
     */
    protected function getFeeTypeDtoData()
    {
        return ['application' => $this->params()->fromRoute('application')];
    }

    /**
     * Create Fee D to Data get method
     *
     * @param string $formData formdata
     *
     * @return array
     */
    protected function getCreateFeeDtoData($formData)
    {
        return [
            'invoicedDate' => $formData['fee-details']['createdDate'],
            'feeType' => $formData['fee-details']['feeType'],
            'application' => $this->params()->fromRoute('application'),
        ];
    }
}
