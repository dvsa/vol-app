<?php

namespace Admin\Controller;

use Common\Controller\Traits\GenericMethods;
use Common\Controller\Traits\GenericReceipt;
use Common\Controller\Traits\GenericRenderView;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Common\Util\FlashMessengerTrait;
use Laminas\Mvc\Controller\AbstractActionController as LaminasAbstractActionController;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits\FeesActionTrait;
use LmcRbacMvc\Identity\IdentityProviderInterface;

class PaymentProcessingFeesController extends LaminasAbstractActionController implements LeftViewProvider
{
    use FeesActionTrait;
    use GenericReceipt;
    use GenericRenderView;
    use GenericMethods;
    use FlashMessengerTrait;

    protected ScriptFactory $scriptFactory;
    protected TableFactory $tableFactory;
    protected FormHelperService $formHelper;
    protected UrlHelperService $urlHelper;
    protected IdentityProviderInterface $identityProvider;
    protected TranslationHelperService $translationHelper;
    protected DateHelperService $dateHelper;


    public function __construct(
        ScriptFactory $scriptFactory,
        TableFactory $tableFactory,
        FormHelperService $formHelper,
        UrlHelperService $urlHelper,
        IdentityProviderInterface $identityProvider,
        TranslationHelperService $translationHelper,
        DateHelperService $dateHelper
    ) {
        $this->scriptFactory = $scriptFactory;
        $this->tableFactory = $tableFactory;
        $this->formHelper = $formHelper;
        $this->urlHelper = $urlHelper;
        $this->identityProvider = $identityProvider;
        $this->translationHelper = $translationHelper;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @inheritdoc
     */
    protected function alterFeeTable($table, $results)
    {
        // no-op
        return $table;
    }

    /**
     * @inheritdoc
     */
    protected function alterCreateFeeForm($form)
    {
        $options = $this->fetchFeeTypeValueOptions();
        $form->get('fee-details')->get('feeType')->setValueOptions($options);

        // remove IRFO fields
        $formHelper = $this->formHelper;
        $formHelper->remove($form, 'fee-details->irfoGvPermit');
        $formHelper->remove($form, 'fee-details->irfoPsvAuth');

        $formHelper->remove($form, 'fee-details->quantity');
        $formHelper->remove($form, 'fee-details->vatRate');

        return $form;
    }

    /**
     * Route (prefix) for fees action redirects
     *
     * @see    Olcs\Controller\Traits\FeesActionTrait
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'admin-dashboard/admin-payment-processing/misc-fees';
    }

    /**
     * The fees route redirect params
     *
     * @see    Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesRouteParams()
    {
        return [];
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
            'isMiscellaneous' => 1,
            'status' => 'current',
        ];
    }

    /**
     * Index action
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function indexAction()
    {
        return $this->feesAction();
    }

    /**
     * @inheritdoc
     */
    protected function renderLayout($view, $pageTitle = null, $pageSubTitle = null)
    {
        return $this->renderView($view, 'Payment processing', $pageSubTitle);
    }

    public function getLeftView()
    {
        $status = $this->params()->fromQuery('status');
        $filters = [
            'status' => $status
        ];

        $this->placeholder()->setPlaceholder('tableFilters', $this->getFeeFilterForm($filters));

        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-payment-processing',
                'navigationTitle' => 'Payment Processing'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * @inheritdoc
     */
    protected function maybeClearLeft($layout)
    {
        $this->placeholder()->setPlaceholder('tableFilters', null);
    }

    /**
     * Redirect action
     *
     * @return \Laminas\Http\Response
     */
    public function redirectAction()
    {
        return $this->redirectToRouteAjax(
            'admin-dashboard/admin-payment-processing/misc-fees',
            ['action' => 'index'],
            ['code' => '303'],
            true
        );
    }

    protected function getFeeTypeDtoData()
    {
        return ['isMiscellaneous' => 'Y'];
    }

    protected function getCreateFeeDtoData($formData)
    {
        return [
            'invoicedDate' => $formData['fee-details']['createdDate'],
            'feeType' => $formData['fee-details']['feeType'],
            'amount' => $formData['fee-details']['amount'],
        ];
    }
}
