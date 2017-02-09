<?php

/**
 * Operator Irfo Psv Authorisations Controller
 */
namespace Olcs\Controller\Operator;

use Dvsa\Olcs\Transfer\Command\Irfo\CreateIrfoPsvAuth as CreateDto;
use Dvsa\Olcs\Transfer\Command\Irfo\UpdateIrfoPsvAuth as UpdateDto;
use Dvsa\Olcs\Transfer\Command\Irfo\GenerateIrfoPsvAuth as GenerateDto;
use Dvsa\Olcs\Transfer\Command\Irfo\GrantIrfoPsvAuth as GrantDto;
use Dvsa\Olcs\Transfer\Command\Irfo\ApproveIrfoPsvAuth as ApproveDto;
use Dvsa\Olcs\Transfer\Command\Irfo\RefuseIrfoPsvAuth as RefuseDto;
use Dvsa\Olcs\Transfer\Command\Irfo\WithdrawIrfoPsvAuth as WithdrawDto;
use Dvsa\Olcs\Transfer\Command\Irfo\CnsIrfoPsvAuth as CnsDto;
use Dvsa\Olcs\Transfer\Command\Irfo\ResetIrfoPsvAuth as ResetDto;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoPsvAuth as ItemDto;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoPsvAuthList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\OperatorControllerInterface;
use Olcs\Data\Mapper\IrfoPsvAuth as Mapper;
use Olcs\Form\Model\Form\IrfoPsvAuth as Form;
use Zend\Form\Element\Hidden;
use Zend\View\Model\ViewModel;
use Common\RefData;
use Zend\Form\Form as ZendForm;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Olcs\Mvc\Controller\ParameterProvider\ConfirmItem;

/**
 * Operator Irfo Psv Authorisations Controller
 */
class OperatorIrfoPsvAuthorisationsController extends AbstractInternalController implements
    OperatorControllerInterface,
    LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'operator_irfo_psv_authorisations';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
        'addAction' => [
            'forms/irfo-psv-auth-copies',
            'forms/irfo-psv-auth-expiry-date'
        ],
        'editAction' => [
            'forms/irfo-psv-auth-copies',
            'forms/irfo-psv-auth-expiry-date'
        ],
    ];

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $tableName = 'operator.irfo.psv-authorisations';
    protected $listDto = ListDto::class;
    protected $listVars = ['organisation'];

    private $allActions = ['grant', 'approve', 'generate', 'cns', 'withdraw', 'refuse', 'reset'];

    protected $crudConfig = [
        'reset' => ['requireRows' => true]
    ];

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/operator/partials/left');

        return $view;
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $itemDto = ItemDto::class;

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add IRFO PSV Authorisation';
    protected $editContentTitle = 'Edit IRFO PSV Authorisation';

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $createCommand = CreateDto::class;

    /**
     * Form data for the add form.
     *
     * Format is name => value
     * name => "route" means get value from route,
     * see conviction controller
     *
     * @var array
     */
    protected $defaultData = [
        'organisation' => 'route',
        'status' => 'irfo_auth_s_pending',
    ];

    /**
     * Determines the command needed being performed based on posted data
     *
     * @param $data
     * @return null
     */
    protected function determineCommand()
    {
        foreach ($this->allActions as $action) {
            if ($this->isButtonPressed($action)) {
                switch ($action)
                {
                    case 'generate':
                        return GenerateDto::class;
                    case 'grant':
                        return GrantDto::class;
                    case 'approve':
                        return ApproveDto::class;
                    case 'refuse':
                        return RefuseDto::class;
                    case 'withdraw':
                        return WithdrawDto::class;
                    case 'cns':
                        return CnsDto::class;
                }
            }
        }

        return UpdateDto::class;
    }

    /**
     * Edit action - determines a varying command prior to execution of parent
     * @return array|ViewModel
     */
    public function editAction()
    {
        $command = $this->determineCommand();

        return parent::edit(
            $this->formClass,
            $this->itemDto,
            new GenericItem($this->itemParams),
            $command,
            $this->mapperClass,
            $this->editViewTemplate,
            $this->editSuccessMessage,
            $this->editContentTitle
        );
    }

    public function resetAction()
    {
        return $this->confirmCommand(
            new ConfirmItem($this->itemParams, false),
            ResetDto::class,
            'Reset',
            'Are you sure you want to reset the selected record(s)',
            'Record reset'
        );
    }

    /**
     * Details action not used
     * @return array
     */
    public function detailsAction()
    {
        return $this->notFoundAction();
    }

    /**
     * Delete action not used
     * @return array
     */
    public function deleteAction()
    {
        return $this->notFoundAction();
    }

    /**
     * Method to alter the form based on status
     *
     * @param $form
     * @param $formData
     * @return mixed
     */
    protected function alterFormForEdit($form, $formData)
    {
        $form = $this->setReadonlyFields($form, $formData);
        $form = $this->setActionButtons($form, $formData);

        // reset status html
        $form->get('fields')->get('statusHtml')->setValue($formData['fields']['statusDescription']);

        return $form;
    }

    /**
     * Method to alter the form based on status
     *
     * @param $form
     * @param $formData
     * @return mixed
     */
    protected function alterFormForAdd($form, $formData)
    {
        foreach ($this->allActions as $action) {
            $form->get('form-actions')->remove($action);
        }

        return $form;
    }

    /**
     * Removes action buttons not possible from the form on GET only
     *
     * @param ZendForm $form
     * @param $formData
     * @return ZendForm
     */
    private function setActionButtons(ZendForm $form, $formData)
    {
        if ($this->params('action') !== 'add') {
            foreach ($this->allActions as $action) {
                $form = $this->determineFormButton($form, $formData, $action);
            }
        }

        return $form;
    }

    /**
     * Removes buttons if action cannot be performed on the entity
     *
     * @param ZendForm $form
     * @param $formData
     * @param $action
     * @return ZendForm
     */
    private function determineFormButton(ZendForm $form, $formData, $action)
    {
        $actionToFlag = [
            'generate' => 'isGeneratable',
            'grant' => 'isGrantable',
            'approve' => 'isApprovable',
            'refuse' => 'isRefusable',
            'withdraw' => 'isWithdrawable',
            'cns' => 'isCnsable',
        ];

        if (empty($actionToFlag[$action])
            || empty($formData['fields'][$actionToFlag[$action]])
            || ((bool) $formData['fields'][$actionToFlag[$action]] !== true)
        ) {
            $form->get('form-actions')->remove($action);
        }

        return $form;
    }

    /**
     * Depending on the status, we disable certain fields from editing, add a hidden field with the same value to
     * ensure data is not lost during validation.
     *
     * @param ZendForm $form
     * @param $formData
     * @return ZendForm
     */
    private function setReadonlyFields(ZendForm $form, $formData)
    {
        $readonlyFields = [];

        switch($formData['fields']['status']) {
            case RefData::IRFO_PSV_AUTH_STATUS_PENDING:
                $readonlyFields = ['irfoPsvAuthType', 'isFeeExemptApplication'];
                break;
            case RefData::IRFO_PSV_AUTH_STATUS_RENEW:
                $readonlyFields = ['irfoPsvAuthType'];
                break;
            case RefData::IRFO_PSV_AUTH_STATUS_GRANTED:
            case RefData::IRFO_PSV_AUTH_STATUS_APPROVED:
                $readonlyFields = ['irfoPsvAuthType', 'validityPeriod', 'isFeeExemptApplication',
                    'isFeeExemptAnnual', 'exemptionDetails', 'copiesRequired'];
                break;
            default:
                $form->setOption('readonly', true);
        }

        foreach ($readonlyFields as $readonlyField) {
            // get the field, set hidden field with same value and disable
            $field = $form->get('fields')->get($readonlyField);
            $field->setValue($formData['fields'][$readonlyField]);

            $hidden = new Hidden($field->getName());
            $hidden->setValue($formData['fields'][$readonlyField]);

            $form->get('fields')->get($readonlyField)->setAttribute('disabled', 'disabled');
            $form->get('fields')->add($hidden);
        }

        return $form;
    }
}
