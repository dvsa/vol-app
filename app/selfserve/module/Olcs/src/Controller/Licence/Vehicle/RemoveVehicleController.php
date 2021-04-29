<?php
declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Form\Form;
use Olcs\Form\Model\Form\Vehicle\ListVehicleSearch;
use Olcs\Form\Model\Form\Vehicle\Vehicles as VehiclesForm;
use Laminas\View\Model\ViewModel;

class RemoveVehicleController extends AbstractVehicleController
{
    public const VEHICLE_REMOVE_LIMIT = 20;
    public const VEHICLE_WARNING_LIMIT = 10;
    public const VEHICLE_SEARCH_FORM_THRESHOLD = 10;

    protected const LICENCE_VEHICLE_REMOVE_HEADER = 'licence.vehicle.remove.header';
    protected const LICENCE_VEHICLE_REMOVE_SEARCH_HEADER = 'licence.vehicle.remove.search.header';

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

    public function indexAction()
    {
        return $this->createView();
    }

    public function postAction()
    {
        $action = array_keys($this->getRequest()->getPost('formActions'))[0];

        if ($action !== 'action') {
            return $this->nextStep('licence/vehicle/GET');
        }

        $selectedVehicles = $this->getRequest()->getPost('table')['id'] ?? null;

        if (empty($selectedVehicles)) {
            $this->form->get('formActions')->setMessages(['licence.vehicle.remove.error.none-selected']);
            return $this->createView();
        }

        if (count($selectedVehicles) > static::VEHICLE_REMOVE_LIMIT) {
            $this->form->get('formActions')->get('action')->setMessages(['licence.vehicle.remove.error.too-many-selected']);
            return $this->createView();
        }

        $this->session->setVrms($selectedVehicles);
        return $this->nextStep('licence/vehicle/remove/confirm/GET');
    }

    /**
     * @inheritDoc
     */
    protected function getViewVariables(): array
    {
        return [
            'title' => $this->isSearchResultsPage() ? static::LICENCE_VEHICLE_REMOVE_SEARCH_HEADER : static::LICENCE_VEHICLE_REMOVE_HEADER,
            'licNo' => $this->data['licence']['licNo'],
            'content' => '',
            'clearUrl' => $this->getLink('licence/vehicle/remove/GET'),
            'form' => $this->form,
            'backLink' => $this->getLink('licence/vehicle/GET'),
        ];
    }

    protected function alterVehicleForm()
    {
        $this->form->get('formActions')
            ->get('action')
            ->setLabel('licence.vehicle.remove.button.action.label')
            ->setAttribute('title', 'licence.vehicle.remove.button.action.title');
        $this->form->get('formActions')
            ->get('cancel')
            ->setAttribute('title', 'licence.vehicle.remove.button.cancel.title');
    }

    protected function alterSearchForm()
    {
        /** @var Form $form */
        $form = $this->forms['searchForm'];
        $form->get('vehicleSearch')
            ->setOption('legend', 'licence.vehicle.table.search.remove.legend');

        $formData = $this->getRequest()->getQuery();
        $form->setData($formData);

        if (array_key_exists('vehicleSearch', $formData)) {
            $form->isValid();
        }

        $form->remove('security');
    }

    /**
     * @return ViewModel
     */
    protected function createView(): ViewModel
    {
        $this->alterVehicleForm();

        $vehicleTable = $this->createVehicleTable();
        $tableFieldset = $this->form->get('table');
        $tableFieldset->get('table')->setTable($vehicleTable);
        $tableFieldset->get('rows')->setValue(count($vehicleTable->getRows()));

        $view = $this->genericView();
        $view->setVariables($this->getViewVariables());

        if ($vehicleTable->getTotal() > static::VEHICLE_WARNING_LIMIT) {
            $view->setVariable('note', $this->translator->translate('licence.vehicle.remove.note'));
        }

        if ($vehicleTable->getTotal() > static::VEHICLE_SEARCH_FORM_THRESHOLD || $this->isSearchResultsPage()) {
            $this->alterSearchForm();
            $view->setVariable('searchForm', $this->forms['searchForm']);
        }

        return $view;
    }
}
