<?php

namespace Olcs\Controller\IrhpPermits;

use Common\Controller\Traits\GenericReceipt;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Traits\FeesActionTrait;
use LmcRbacMvc\Identity\IdentityProviderInterface;

class IrhpApplicationFeesController extends AbstractIrhpPermitController
{
    use FeesActionTrait;
    use GenericReceipt;
    use IrhpFeesTrait;

    protected UrlHelperService $urlHelper;
    protected IdentityProviderInterface $identityProvider;
    protected TranslationHelperService $translationHelper;
    protected DateHelperService $dateHelper;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        protected FlashMessengerHelperService $flashMessengerHelper,
        UrlHelperService $urlHelper,
        IdentityProviderInterface $identityProvider,
        TranslationHelperService $translationHelper,
        DateHelperService $dateHelper
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager
        );
        $this->urlHelper = $urlHelper;
        $this->identityProvider = $identityProvider;
        $this->translationHelper = $translationHelper;
        $this->dateHelper = $dateHelper;
    }

    /**
     * Route (prefix) for fees action redirects
     *
     * @see    Olcs\Controller\Traits\FeesActionTrait
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'licence/irhp-application-fees';
    }

    /**
     * The fees route redirect params
     *
     * @see    Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesRouteParams()
    {
        return [
            'licence' => $this->getFromRoute('licence'),
            'irhpApplication' => $this->getFromRoute('irhpAppId'),
            'irhpAppId' => $this->getFromRoute('irhpAppId'),
        ];
    }

    /**
     * The controller specific fees table params
     *
     * @see    Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesTableParams()
    {
        return [
            'irhpApplication' => $this->getFromRoute('irhpAppId'),
            'status' => 'current',
        ];
    }

    /**
     * Render layout
     *
     * @param string|\Laminas\View\Model\ViewModel $view View
     *
     * @see    Olcs\Controller\Traits\FeesActionTrait
     * @return \Laminas\View\Model\ViewModel
     */
    protected function renderLayout($view)
    {
        return $this->renderView($view);
    }

    /**
     * Get fee type dto data
     *
     * @see    Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeeTypeDtoData()
    {
        return [
            'irhpAppId' => $this->getFromRoute('irhpAppId'),
        ];
    }

    /**
     * Get create fee dto data
     *
     * @param array $formData Data
     *
     * @see    Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getCreateFeeDtoData(array $formData)
    {
        return [
            'invoicedDate' => $formData['fee-details']['createdDate'],
            'feeType' => $formData['fee-details']['feeType'],
            'licence' => $this->params()->fromRoute('licence'),
            'irhpApplication' => $this->params()->fromRoute('irhpAppId'),
        ];
    }
}
