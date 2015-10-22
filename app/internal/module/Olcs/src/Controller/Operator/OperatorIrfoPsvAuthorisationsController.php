<?php

/**
 * Operator Irfo Psv Authorisations Controller
 */
namespace Olcs\Controller\Operator;

use Common\Form\Elements\Types\Html;
use Dvsa\Olcs\Transfer\Command\Irfo\CreateIrfoPsvAuth as CreateDto;
use Dvsa\Olcs\Transfer\Command\Irfo\UpdateIrfoPsvAuth as UpdateDto;
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
use Common\Form\Elements\InputFilters\ActionButton;

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
        'addAction' => ['forms/irfo-psv-auth-numbers', 'forms/irfo-psv-auth-copies'],
        'editAction' => ['forms/irfo-psv-auth-numbers', 'forms/irfo-psv-auth-copies'],
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

    public function detailsAction()
    {
        return $this->notFoundAction();
    }

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
        $form = $this->makeGeneralFormChanges($form, $formData);
        $form = $this->addPossibleActions($form);

        return $form;
    }

    /**
     * Adds possible action buttons to the bottom of the page.
     * NB: Cancel removed and then readded to maintain correct order
     *
     * @param ZendForm $form
     * @return ZendForm
     */
    private function addPossibleActions(ZendForm $form)
    {
        $form->get('form-actions')->remove('cancel');
        $form->get('form-actions')->add($this->generateActionButton('grant'));
        $form->get('form-actions')->add($this->generateActionButton('approve'));
        $form->get('form-actions')->add($this->generateActionButton('generateDocument', 'Generate document'));
        $form->get('form-actions')->add($this->generateActionButton('cns', 'CNS'));
        $form->get('form-actions')->add($this->generateActionButton('withdraw'));
        $form->get('form-actions')->add($this->generateActionButton('refuse'));
        $form->get('form-actions')->add($this->generateActionButton('refuse'));
        $form->get('form-actions')->add($this->generateActionButton('reset'));
        $form->get('form-actions')->add($this->generateActionButton('cancel'));

        return $form;
    }

    /**
     * Generates and returns an action button.
     *
     * @param $action
     * @param null $label
     * @return ActionButton
     */
    private function generateActionButton($action, $label = null)
    {
        $button = new ActionButton(strtolower($action));
        $button->setAttributes(['id' => strtolower($action), 'type' => 'submit', 'class' => 'action--secondary large']);
        $button->setOptions(
            [
                'label' => !empty($label) ? $label : ucfirst($action),
                'label_attributes' => [
                    'class' => 'col-sm-2'
                ],
                'column-size' => 'sm-10'
            ]
        );

        return $button;
    }

    /**
     * Make neccessary adjustments to the form. Other adjustments may also be in the data mapper
     *
     * @param ZendForm $form
     * @param $formData
     * @return ZendForm
     */
    private function makeGeneralFormChanges(ZendForm $form, $formData)
    {
        $form = $this->alterStatusField($form, $formData);

        $form->get('fields')->get('renewalDateHtml')->setAttribute('class', '');

        return $form;
    }

    /**
     * Replace the disabled select for a simple label element
     * 
     * @param ZendForm $form
     * @param $formData
     * @return ZendForm
     */
    private function alterStatusField(ZendForm $form, $formData)
    {
        $statusOptions = $form->get('fields')->get('status')->getValueOptions();

        if (isset($statusOptions[$formData['fields']['status']])) {
            $form->get('fields')->get('statusHtml')->setValue($statusOptions[$formData['fields']['status']]);
            $form->get('fields')->get('statusHtml')->setAttribute('class', '');
            $form->get('fields')->get('status')->setAttribute('class', 'visually-hidden');
        }

        return $form;
    }
}
