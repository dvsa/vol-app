<?php

namespace Olcs\Controller\Licence\Vehicle\Reprint;

use Common\Form\Form;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Licence\Vehicle\AbstractVehicleController;
use Olcs\Form\Model\Form\Vehicle\ListVehicleSearch;
use Olcs\Form\Model\Form\Vehicle\Vehicles as VehiclesForm;
use Permits\Data\Mapper\MapperManager;

/**
 * @see ReprintVehicleLicenceControllerFactory
 */
class ReprintLicenceVehicleDiscController extends AbstractVehicleController
{
    protected const MAX_ACTION_BATCH_SIZE = 20;

    /**
     * @inheritDoc
     */
    protected $formConfig = [
        'default' => [
            'searchForm' => [
                'formClass' => ListVehicleSearch::class
            ],
            'goodsVehicleForm' => [
                'formClass' => VehiclesForm::class,
            ]
        ]
    ];

    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     * @param FlashMessenger $flashMessenger
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager,
        FlashMessenger $flashMessenger
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager, $flashMessenger);
    }

    /**
     * Handles a request from a user to show the form to reprint one or more of the licences that they have access to.
     *
     * @return ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        $request = $this->getRequest();
        $form = $this->form;
        $this->configureFormActionsForIndex($form);

        $vehicleTable = $this->createVehicleTable();
        $tableFieldset = $form->get('table');
        $tableFieldset->get('table')->setTable($vehicleTable);
        $tableFieldset->get('rows')->setValue(count($vehicleTable->getRows()));

        $view = $this->genericView();
        $view->setVariables([
            'title' => $this->isSearchResultsPage() ? 'licence.vehicle.reprint.search.header' : 'licence.vehicle.reprint.header',
            'licNo' => $this->data['licence']['licNo'],
            'content' => '',
            'clearUrl' => $this->getLink('licence/vehicle/reprint/GET'),
            'form' => $form,
            'backLink' => $this->getLink('lva-licence/vehicles'),
        ]);

        if ($vehicleTable->getTotal() > static::DEFAULT_TABLE_ROW_LIMIT) {
            $view->setVariable('note', $this->translationHelper->translateReplace('licence.vehicle.reprint.note', [static::MAX_ACTION_BATCH_SIZE]));
        }

        if ($vehicleTable->getTotal() > static::DEFAULT_TABLE_ROW_LIMIT || $this->isSearchResultsPage()) {
            $searchForm = $this->forms['searchForm'];
            $this->configureSearchFormForIndex($searchForm, $request);
            $view->setVariable('searchForm', $searchForm);
        }

        return $view;
    }

    /**
     * Handles a request from a user to reprint one or more of the licences that they can access.
     *
     * @return Response|ViewModel
     */
    public function postAction()
    {
        $action = array_keys($this->getRequest()->getPost('formActions'))[0];

        if ($action !== 'action') {
            return $this->nextStep('lva-licence/vehicles');
        }

        $selectedVehicles = $this->getRequest()->getPost('table')['id'] ?? null;

        if (empty($selectedVehicles)) {
            $this->form->get('formActions')->setMessages(['licence.vehicle.reprint.error.none-selected']);
            return $this->indexAction();
        }

        if (count($selectedVehicles) > static::MAX_ACTION_BATCH_SIZE) {
            $message = $this->translationHelper->translateReplace('licence.vehicle.reprint.error.too-many-selected', [static::MAX_ACTION_BATCH_SIZE]);
            $this->form->get('formActions')->get('action')->setMessages([$message]);
            return $this->indexAction();
        }

        $this->session->setVrms($selectedVehicles);
        return $this->nextStep('licence/vehicle/reprint/confirm/GET');
    }

    protected function configureFormActionsForIndex(Form $form): void
    {
        $form->get('formActions')
            ->get('action')
            ->setLabel('licence.vehicle.reprint.button.action.label')
            ->setAttribute('title', 'licence.vehicle.reprint.button.action.title');
        $form->get('formActions')
            ->get('cancel')
            ->setAttribute('title', 'licence.vehicle.reprint.button.cancel.title');
    }

    protected function configureSearchFormForIndex(Form $form, Request $request): void
    {
        $form->get('vehicleSearch')
            ->setOption('legend', 'licence.vehicle.table.search.reprint.legend');

        $formData = $request->getQuery();
        $form->setData($formData);

        if (array_key_exists('vehicleSearch', $formData->toArray())) {
            $form->isValid();
        }

        $form->remove('security');
    }
}
