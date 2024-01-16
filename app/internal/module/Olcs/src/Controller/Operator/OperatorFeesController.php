<?php

/**
 * Operator Fees Controller
 */

namespace Olcs\Controller\Operator;

use Common\Controller\Traits\GenericReceipt;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Traits\FeesActionTrait;
use Olcs\Service\Data\Licence;
use LmcRbacMvc\Identity\IdentityProviderInterface;

/**
 * Operator Fees Controller
 */
class OperatorFeesController extends OperatorController
{
    use FeesActionTrait;
    use GenericReceipt;

    /**
     * @var string
     */
    protected $section = 'fees';

    /**
     * @var string
     */
    protected $subNavRoute = 'operator_fees';

    protected TranslationHelperService $translationHelper;
    protected UrlHelperService $urlHelper;
    protected IdentityProviderInterface $identityProvider;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        DateHelperService $dateHelper,
        AnnotationBuilder $transferAnnotationBuilder,
        CommandService $commandService,
        FlashMessengerHelperService $flashMessengerHelper,
        Licence $licenceDataService,
        QueryService $queryService,
        \Laminas\Navigation\Navigation $navigation,
        TranslationHelperService $translatorHelper,
        UrlHelperService $urlHelper,
        IdentityProviderInterface $identityProvider
    ) {
        $this->translationHelper = $translatorHelper;
        $this->urlHelper = $urlHelper;
        $this->identityProvider = $identityProvider;
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $dateHelper,
            $transferAnnotationBuilder,
            $commandService,
            $flashMessengerHelper,
            $licenceDataService,
            $queryService,
            $navigation
        );
    }

    /**
     * Route (prefix) for fees action redirects
     *
     * @see    Olcs\Controller\Traits\FeesActionTrait
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'operator/fees';
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
            'organisation' => $this->params()->fromRoute('organisation'),
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
            'organisation' => $this->params()->fromRoute('organisation'),
            'status' => 'current',
        ];
    }

    /**
     * Render layout
     *
     * @param \Laminas\View\Model\ViewModel $view view
     *
     * @return \Laminas\View\Model\ViewModel
     */
    protected function renderLayout($view)
    {
        return $this->renderView($view);
    }

    /**
     * Get fee type dto data
     *
     * @return array
     */
    protected function getFeeTypeDtoData()
    {
        return ['organisation' => $this->params()->fromRoute('organisation')];
    }

    /**
     * Alter create fee form
     *
     * @param \Laminas\Form\Form $form form
     *
     * @return \Laminas\Form\Form
     */
    protected function alterCreateFeeForm($form)
    {
        $formHelper = $this->formHelper;

        // disable amount validation by default
        $formHelper->disableEmptyValidationOnElement($form, 'fee-details->amount');
        $form->get('fee-details')->get('amount')->setAttribute('readonly', true);

        // populate fee type select
        $options = $this->fetchFeeTypeValueOptions();
        $form->get('fee-details')->get('feeType')->setValueOptions($options);

        $request = $this->getRequest();

        $post = $request->getPost()->toArray();
        // populate GV Permit and PSV Auth dropdowns
        $currentFeeType = isset($post['fee-details']['feeType']) ? $post['fee-details']['feeType'] : null;
        $data = $this->fetchFeeTypeListData(null, $currentFeeType);

        if (isset($data['extra']['valueOptions']['irfoGvPermit'])) {
            $form->get('fee-details')->get('irfoGvPermit')->setValueOptions(
                $data['extra']['valueOptions']['irfoGvPermit']
            );
        }

        if (isset($data['extra']['valueOptions']['irfoPsvAuth'])) {
            $form->get('fee-details')->get('irfoPsvAuth')->setValueOptions(
                $data['extra']['valueOptions']['irfoPsvAuth']
            );
        }
        if ($request->isPost() && !$data['extra']['showQuantity'] && $currentFeeType) {
            $formHelper->remove($form, 'fee-details->quantity');
        }

        if (isset($data['extra']['showVatRate']) && !$data['extra']['showVatRate']) {
            $formHelper->remove($form, 'fee-details->vatRate');
        }

        return $form;
    }

    /**
     * Get create fee dto data
     *
     * @param array $formData form data
     *
     * @return array
     */
    protected function getCreateFeeDtoData($formData)
    {
        $feeDetails = $formData['fee-details'];
        $params = [
            'invoicedDate' => $feeDetails['createdDate'],
            'feeType' => $feeDetails['feeType'],
            'irfoGvPermit' => $feeDetails['irfoGvPermit'],
            'irfoPsvAuth' => $feeDetails['irfoPsvAuth'],
        ];
        if (isset($feeDetails['quantity'])) {
            $params['quantity'] = $feeDetails['quantity'];
        }
        return $params;
    }

    /**
     * Alter table
     *
     * @param TableBuilder $table   table
     * @param array        $results results
     *
     * @return TableBuilder
     */
    protected function alterFeeTable($table, $results)
    {
        $translator = $this->translationHelper;
        $table->setVariable(
            'title',
            $translator->translate(
                'internal-navigation-operator-irfo-fees' . (count($results['results']) === 1 ? '-singular' : '')
            )
        );
        if ($results['extra']['allowFeePayments'] === false) {
            $table->disableAction('pay');
        }

        return $table;
    }
}
