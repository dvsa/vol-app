<?php

namespace Olcs\Controller\TransportManager\Details;

use Dvsa\Olcs\Transfer\Command\TmEmployment\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\TmEmployment\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Command\TmEmployment\DeleteList as DeleteDto;
use Dvsa\Olcs\Transfer\Query\TmEmployment\GetSingle as ItemDto;
use Dvsa\Olcs\Transfer\Query\TmEmployment\GetList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\TransportManager\EmploymentHistory as Mapper;
use Olcs\Form\Model\Form\TmEmployment as Form;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;
use Zend\View\Model\ViewModel;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;

/**
 * Transport Manager Details Employment Controller
 */
class TransportManagerDetailsEmploymentController extends AbstractInternalController implements
    TransportManagerControllerInterface,
    LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'transport_manager_details_employment';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['forms/crud-table-handler', 'tm-int-other-employment']
    ];

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableName = 'tm.int_employments';
    protected $listDto = ListDto::class;
    protected $listVars = ['transportManager'];

    protected $defaultData = ['transportManager' => AddFormDefaultData::FROM_ROUTE];

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/transport-manager/partials/details-left');

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
    protected $addContentTitle = 'Add employment';
    protected $editContentTitle = 'Edit employment';

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $createCommand = CreateDto::class;

    /**
     * Variables for controlling the delete action.
     * Command is required, as are itemParams from above
     */
    protected $deleteParams = ['ids' => 'id'];
    protected $deleteCommand = DeleteDto::class;
    protected $hasMultiDelete = true;

    protected function alterFormForEdit(\Zend\Form\Form $form, $data)
    {
        $this->getServiceLocator()->get('Helper\Form')->remove($form, 'form-actions->addAnother');

        return $form;
    }

    /**
     * @param \Common\Service\Table\TableBuilder $table
     * @param array $data
     * @return \Common\Service\Table\TableBuilder
     */
    protected function alterTable($table, $data)
    {
        $disableTable = !empty($data['extra']['transportManager']['removedDate']);
        if ($disableTable === true) {
            $table->setDisabled(true);

            $column = $table->getColumn('employerName');
            unset($column['type']);
            $table->setColumn('employerName', $column);
        }

        return $table;
    }

    public function detailsAction()
    {
        return $this->notFoundAction();
    }
}
