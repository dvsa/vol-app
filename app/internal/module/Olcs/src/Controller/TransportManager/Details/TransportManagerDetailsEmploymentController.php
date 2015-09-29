<?php

namespace Olcs\Controller\TransportManager\Details;

use Dvsa\Olcs\Transfer\Command\TmEmployment\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\TmEmployment\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Command\TmEmployment\DeleteList as DeleteDto;
use Dvsa\Olcs\Transfer\Query\TmEmployment\GetSingle as ItemDto;
use Dvsa\Olcs\Transfer\Query\TmEmployment\GetList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Data\Mapper\TransportManager\EmploymentHistory as Mapper;
//use Admin\Form\Model\Form\Partner as Form;
use Olcs\Form\Model\Form\TmEmployment as Form;

/**
 * Transport Manager Details Employment Controller
 */
class TransportManagerDetailsEmploymentController extends AbstractInternalController implements
    PageLayoutProvider,
    \Olcs\Controller\Interfaces\TransportManagerControllerInterface
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

    protected $defaultData = [
        'transportManager' => \Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData::FROM_ROUTE
    ];

    public function getPageLayout()
    {
        return 'layout/transport-manager-section-migrated';
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

    /**
     *
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->placeholder()->setPlaceholder('section', 'details-employment');

        parent::onDispatch($e);
    }

    /**
     * Override
     *
     * @param \Zend\Form\Form $form
     * @param type $data
     */
    protected function alterFormForEdit(\Zend\Form\Form $form, $data)
    {
        // remove addAnother button
        $this->getServiceLocator()->get('Helper\Form')->remove($form, 'form-actions->addAnother');

        return $form;
    }

    protected function alterTable($table, $data)
    {
        /* @var $table \Common\Service\Table\TableBuilder */

        $disableTable = !is_null($data['extra']['transportManager']['removedDate']);
        if ($disableTable == true) {
            $table->setDisabled(true);

            // remove hyperlink from table
            $column = $table->getColumn('employerName');
            unset($column['type']);
            $table->setColumn('employerName', $column);
        }

        return $table;
    }

    /**
     *
     * @return type
     */
    public function detailsAction()
    {
        return $this->notFoundAction();
    }
}
