<?php

/**
 * Bus Service Controller
 */
namespace Olcs\Controller\Bus\Service;

use Dvsa\Olcs\Transfer\Command\Bus\UpdateServiceRegister as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Bus\BusReg as ItemDto;
use Dvsa\Olcs\Transfer\Query\ConditionUndertaking\GetList as ConditionUndertakingListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Data\Mapper\BusRegisterService as Mapper;
use Olcs\Form\Model\Form\BusRegisterService as Form;
use Common\RefData;

/**
 * Bus Service Controller
 */
class BusServiceController extends AbstractInternalController implements BusRegControllerInterface
{
    const CONDITION_TYPE_CONDITION = 'cdt_con';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'licence_bus_register_service';

    protected $redirectConfig = [
        'edit' => [
            'action' => 'edit'
        ]
    ];

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $itemDto = ItemDto::class;
    protected $itemParams = ['id' => 'busRegId'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;

    protected $editContentTitle = 'Register service';

    /**
     * @param $name
     * @return mixed
     */
    public function getForm($name)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm($name);
        $formHelper->setFormActionFromRequest($form, $this->getRequest());
        $formHelper->populateFormTable($form->get('conditions')->get('table'), $this->getConditionsTable());

        return $form;
    }

    /**
     * Get conditions table
     */
    protected function getConditionsTable()
    {
        return $this->getServiceLocator()->get('Table')->prepareTable('Bus/conditions', $this->getTableData());
    }

    /**
     * Get table data
     *
     * @return array
     */
    protected function getTableData()
    {
        $query = ConditionUndertakingListDto::class;
        $data = [
            'licence' => $this->params()->fromRoute('licence')
        ];

        $response = $this->handleQuery($query::create($data));

        if ($response->isServerError() || $response->isClientError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            return $response->getResult();
        }

        return [];
    }

    /**
     * Alter Form for edit
     *
     * @param \Common\Controller\Form $form
     * @param array $formData
     * @return \Common\Controller\Form
     */
    public function alterFormForEdit($form, $formData)
    {
        $busReg = $this->getBusReg();

        if ($busReg['isReadOnly']) {
            $form->setOption('readonly', true);
        }

        if ($busReg['status']['id'] == RefData::BUSREG_STATUS_CANCELLED) {
            $form->remove('timetable');
        }

        // opNotifiedLaPte is only needed for scottish short notice registrations,
        // the mapper will default this data to 'N' when it is posted to the backend
        if (!($busReg['isScottishRules'] && $busReg['isShortNotice'] === 'Y')) {
            $form->get('fields')->remove('opNotifiedLaPte');
        }

        return $form;
    }

    /**
     * Gets a Bus Reg - we'll have this query cached, and if it previously failed we'll have returned a 404 already
     *
     * @return array|mixed
     */
    private function getBusReg()
    {
        $params = ['id' => $this->params()->fromRoute('busRegId')];
        $response = $this->handleQuery(ItemDto::create($params));

        return $response->getResult();
    }

    public function indexAction()
    {
        return $this->notFoundAction();
    }

    public function detailsAction()
    {
        return $this->notFoundAction();
    }

    public function addAction()
    {
        return $this->notFoundAction();
    }

    public function deleteAction()
    {
        return $this->notFoundAction();
    }
}
