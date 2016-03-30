<?php

namespace Olcs\Controller\Operator;

use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\OperatorControllerInterface;
use Olcs\Data\Mapper\OperatorPeople as Mapper;
use Dvsa\Olcs\Transfer\Query\OrganisationPerson\GetSingle as ItemDto;
use Dvsa\Olcs\Transfer\Command\OrganisationPerson\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\OrganisationPerson\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Command\OrganisationPerson\DeleteList as DeleteDto;
use Zend\View\Model\ViewModel;

/**
 * OperatorPeopleController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OperatorPeopleController extends AbstractInternalController implements
    OperatorControllerInterface,
    LeftViewProvider
{
    /**
     * Organisation data
     *
     * @var array
     */
    private $organisationData;

    protected $inlineScripts = [
        'indexAction' => ['table-actions', 'crud']
    ];

    protected $mapperClass = Mapper::class;

    protected $formClass = \Common\Form\Model\Form\Lva\Person::class;
    protected $addContentTitle = 'Add person';
    protected $editContentTitle = 'Edit person';

    protected $itemDto = ItemDto::class;
    protected $itemParams = ['id'];

    protected $createCommand = CreateDto::class;
    protected $defaultData = ['organisation' => \Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData::FROM_ROUTE];

    protected $updateCommand = UpdateDto::class;

    protected $deleteParams = ['ids' => 'id'];
    protected $deleteCommand = DeleteDto::class;
    protected $hasMultiDelete = true;

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/operator/partials/left');

        return $view;
    }

    public function indexAction()
    {
        $data = $this->loadOrganisationData();

        if ($data['isSoleTrader'] === true) {
            return $this->soleTrader($data);
        } else {
            return $this->notSoleTrader($data);
        }
    }

    /**
     * Handle sole trader view of index
     *
     * @param array $data
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function soleTrader($data)
    {
        if (count($data['organisationPersons']) > 0) {
            $this->getEvent()->getRouteMatch()->setParam('id', $data['organisationPersons'][0]['id']);

            return $this->editAction();
        } else {
            return $this->addAction();
        }
    }

    /**
     * Handle all Organisation types except sole trader view of index
     *
     * @param array $data
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function notSoleTrader($data)
    {
        $tableData = [];
        foreach ($data['organisationPersons'] as $op) {
            // set the row as the person data
            $row = $op['person'];
            // but set the id to be the OrganisationPerson ID as that is what we are editing
            $row['personId'] = $row['id'];
            $row['id'] = $op['id'];
            $row['position'] = $op['position'];
            $tableData['results'][] = $row;
        }
        $table = $this->table()->buildTable('operator-people', $tableData, []);
        // remove column for all except organisation type : other
        if ($data['type']['id'] !== 'org_t_pa') {
            $table->removeColumn('position');
        }

        $this->placeholder()->setPlaceholder('table', $table->render());

        return $this->viewBuilder()->buildViewFromTemplate('pages/table');
    }

    /**
     * Load Organisation data
     *
     * @return array
     */
    private function loadOrganisationData()
    {
        if ($this->organisationData === null) {
            $listParams = ['id' => $this->params()->fromRoute('organisation')];
            $response = $this->handleQuery(\Dvsa\Olcs\Transfer\Query\Organisation\People::create($listParams));

            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            $this->organisationData = $response->getResult();
        }

        return $this->organisationData;
    }

    /**
     * Alter the Add form
     *
     * @param \Zend\Form\Form $form
     *
     * @return \Zend\Form\Form
     */
    protected function alterFormForAdd($form)
    {
        return $this->alterForm($form, true);
    }

    /**
     * Alter the Edit form
     *
     * @param \Zend\Form\Form $form
     *
     * @return \Zend\Form\Form
     */
    protected function alterFormForEdit($form)
    {
        return $this->alterForm($form);
    }

    /**
     * Alter the Edit form, when called from IndexAction ie if org is a sole trader
     *
     * @param \Zend\Form\Form $form
     *
     * @return \Zend\Form\Form
     */
    protected function alterFormForIndex($form)
    {
        return $this->alterForm($form);
    }

    /**
     * Alter the add/edit form
     *
     * @param \Zend\Form\Form $form
     * @param bool            $showAddAnotherButton
     *
     * @return \Zend\Form\Form
     */
    protected function alterForm($form, $showAddAnotherButton = false)
    {
        $data = $this->loadOrganisationData();
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        // if org type is not Other, then remove position element
        if ($data['type']['id'] !== \Common\RefData::ORG_TYPE_OTHER) {
            $formHelper->remove($form, 'data->position');
        }
        // if not a sole trader OR no person OR already disqualified then hide the disqualify button
        if ($data['type']['id'] !== \Common\RefData::ORG_TYPE_SOLE_TRADER ||
            !isset($data['organisationPersons'][0]['person']['id']) ||
            $data['organisationPersons'][0]['person']['disqualificationStatus'] !== 'None'
        ) {
            $formHelper->remove($form, 'form-actions->disqualify');
        } else {
            // put the correct link onto the form disqualify button
            $personId = $data['organisationPersons'][0]['person']['id'];
            $form->get('form-actions')->get('disqualify')->setValue(
                $this->url()->fromRoute(
                    'operator/disqualify_person',
                    ['organisation' => $data['id'], 'person' => $personId]
                )
            );
        }

        if (!$showAddAnotherButton) {
            $formHelper->remove($form, 'form-actions->addAnother');
        }
        if ($data['isUnlicensed']) {
            $form->getInputFilter()->get("data")->get('birthDate')->setRequired(false);
        }

        return $form;
    }
}
