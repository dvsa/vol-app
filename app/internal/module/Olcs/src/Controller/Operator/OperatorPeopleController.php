<?php

namespace Olcs\Controller\Operator;

use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\OrganisationPerson\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\OrganisationPerson\DeleteList as DeleteDto;
use Dvsa\Olcs\Transfer\Command\OrganisationPerson\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\OrganisationPerson\GetSingle as ItemDto;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\OperatorControllerInterface;
use Olcs\Data\Mapper\OperatorPeople as Mapper;

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

    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelperService,
        FlashMessengerHelperService $flashMessenger,
        Navigation $navigation
    ) {
        parent::__construct($translationHelper, $formHelperService, $flashMessenger, $navigation);
    }
    /**
     * Get Left View
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/operator/partials/left');

        return $view;
    }

    /**
     * Handle action: Index
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $data = $this->loadOrganisationData();

        if ($data['isSoleTrader'] === true) {
            return $this->soleTrader($data);
        }

        return $this->notSoleTrader($data);
    }

    /**
     * Handle sole trader view of index
     *
     * @param array $data Organisation Data
     *
     * @return \Laminas\View\Model\ViewModel
     */
    private function soleTrader($data)
    {
        if (count($data['organisationPersons']) > 0) {
            $this->getEvent()->getRouteMatch()->setParam('id', $data['organisationPersons'][0]['id']);

            return $this->editAction();
        }

        return $this->addAction();
    }

    /**
     * Handle all Organisation types except sole trader view of index
     *
     * @param array $data Organisation Data
     *
     * @return \Laminas\View\Model\ViewModel
     */
    private function notSoleTrader($data)
    {
        $this->placeholder()->setPlaceholder(
            'table',
            $this->getNotSoleTraderTable($data)
                ->render()
        );

        return $this->viewBuilder()->buildViewFromTemplate('pages/table');
    }

    /**
     * Build and alter table for Not SoleTraders organisations
     *
     * @param array $data Organisation Data
     *
     * @return \Common\Service\Table\TableBuilder
     */
    private function getNotSoleTraderTable(array $data)
    {
        //  prepare table data
        $rows = [];
        foreach ($data['organisationPersons'] as $op) {
            $rows[] =
                [
                    // but set the id to be the OrganisationPerson ID as that is what we are editing
                    'personId' => $op['person']['id'],
                    'id' => $op['id'],
                    'position' => $op['position'],
                ] +
                $op['person'];
        }

        $tableData = [
            'results' => $rows,
        ];

        //  alter table
        $type = $data['type']['id'] ?? null;

        $table = $this->table()->buildTable('operator-people', $tableData, []);

        // remove column for all except organisation type : other
        if ($type !== RefData::ORG_TYPE_OTHER) {
            $table->removeColumn('position');
        }

        //  set empty message in depend of Organisation type
        if ($type === RefData::ORG_TYPE_REGISTERED_COMPANY) {
            $table->setEmptyMessage('selfserve-app-subSection-your-business-people-ltd.table.empty-message');
        }

        return $table;
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
                $this->$this->flashMessengerHelperService->addErrorMessage('unknown-error');
            }

            $this->organisationData = $response->getResult();
        }

        return $this->organisationData;
    }

    /**
     * Alter the Add form
     *
     * @param \Laminas\Form\Form $form Form
     *
     * @return \Laminas\Form\Form
     */
    protected function alterFormForAdd($form)
    {
        return $this->alterForm($form, true);
    }

    /**
     * Alter the Edit form
     *
     * @param \Laminas\Form\Form $form Form
     *
     * @return \Laminas\Form\Form
     */
    protected function alterFormForEdit($form)
    {
        return $this->alterForm($form);
    }

    /**
     * Alter the Edit form, when called from IndexAction ie if org is a sole trader
     *
     * @param \Laminas\Form\Form $form Form
     *
     * @return \Laminas\Form\Form
     */
    protected function alterFormForIndex($form)
    {
        return $this->alterForm($form);
    }

    /**
     * Alter the add/edit form
     *
     * @param \Laminas\Form\Form $form                 Form
     * @param bool               $showAddAnotherButton is Show Add Another button
     *
     * @return \Laminas\Form\Form
     */
    protected function alterForm($form, $showAddAnotherButton = false)
    {
        $data = $this->loadOrganisationData();
        $formHelperService = $this->formHelperService;
        // if org type is not Other, then remove position element
        if ($data['type']['id'] !== \Common\RefData::ORG_TYPE_OTHER) {
            $formHelperService->remove($form, 'data->position');
        }
        // if not a sole trader OR no person OR already disqualified then hide the disqualify button
        if (
            $data['type']['id'] !== \Common\RefData::ORG_TYPE_SOLE_TRADER
            || !isset($data['organisationPersons'][0]['person']['id'])
            || $data['organisationPersons'][0]['person']['disqualificationStatus'] !== 'None'
        ) {
            $formHelperService->remove($form, 'form-actions->disqualify');
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
            $formHelperService->remove($form, 'form-actions->addAnother');
        }
        if ($data['isUnlicensed']) {
            $form->getInputFilter()->get('data')->get('birthDate')->setRequired(false);
        }

        return $form;
    }
}
