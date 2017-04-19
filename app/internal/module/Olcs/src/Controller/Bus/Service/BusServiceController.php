<?php

namespace Olcs\Controller\Bus\Service;

use Dvsa\Olcs\Transfer\Command\Bus\UpdateServiceRegister as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Bus\BusReg as ItemDto;
use Dvsa\Olcs\Transfer\Query\ConditionUndertaking\GetList as ConditionUndertakingListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Data\Mapper\BusRegisterService as Mapper;
use Olcs\Form\Model\Form\BusRegisterService as Form;

/**
 * Bus Service Controller
 */
class BusServiceController extends AbstractInternalController implements BusRegControllerInterface
{
    use ControllerTraits\BusControllerTrait;

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
     * Get form
     *
     * @param string $name Form name
     *
     * @return \Common\Form\Form
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
     *
     * @return \Common\Service\Table\TableBuilder
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
     * @param \Common\Form\Form $form     Form
     * @param array             $formData Form data
     *
     * @return \Common\Form\Form
     */
    public function alterFormForEdit($form, $formData)
    {
        $busReg = $this->getBusReg();

        if ($busReg['isReadOnly']) {
            $form->setOption('readonly', true);
        }

        if ($busReg['isCancelled'] || $busReg['isCancellation']) {
            /** @var \Zend\Form\Fieldset $timetable */
            $timetable = $form->get('timetable');
            $timetable->remove('timetableAcceptable');
            $timetable->remove('mapSupplied');
        }

        if ($busReg['isShortNotice'] === 'N') {
            $form->get('fields')->remove('laShortNote');
        }

        // opNotifiedLaPte is only needed for scottish rules, if the field is null
        // the mapper will default this data to 'N' when it is posted to the backend
        if (!($busReg['isScottishRules'])) {
            $form->get('fields')->remove('opNotifiedLaPte');
        }

        return $form;
    }
}
