<?php
declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Form\Form;
use Olcs\Form\Model\Form\Vehicle\ListVehicleSearch;
use Laminas\View\Model\ViewModel;

class ListVehicleController extends AbstractVehicleController
{
    protected const VEHICLE_SEARCH_FORM_THRESHOLD = 10;
    protected const LICENCE_VEHICLE_REMOVE_HEADER = 'licence.vehicle.list.header';
    protected const LICENCE_VEHICLE_REMOVE_SEARCH_HEADER = 'licence.vehicle.list.search.header';
    
    protected $formConfig = [
        'default' => [
            'searchForm' => [
                'formClass' => ListVehicleSearch::class
            ]
        ]
    ];

    public function indexAction()
    {
        return $this->createView();
    }

    /**
     * @inheritDoc
     */
    protected function getViewVariables(): array
    {
        $vehicleTable = $this->createVehicleTable();
        $vehicleTable->removeColumn('action');

        $data = [
            'title' => $this->isSearchResultsPage() ? static::LICENCE_VEHICLE_REMOVE_SEARCH_HEADER : static::LICENCE_VEHICLE_REMOVE_HEADER,
            'licNo' => $this->data['licence']['licNo'],
            'content' => '',
            'clearUrl' => $this->getLink('licence/vehicle/list/GET'),
            'table' => $vehicleTable,
            'backLink' => $this->getLink('licence/vehicle/GET'),
            'bottomContent' => $this->getChooseDifferentActionMarkup()
        ];

        if ($vehicleTable->getTotal() > static::VEHICLE_SEARCH_FORM_THRESHOLD || $this->isSearchResultsPage()) {
            $this->alterSearchForm();
            $data['searchForm'] = $this->forms['searchForm'];
        }
        return $data;
    }

    protected function alterSearchForm()
    {
        /** @var Form $form */
        $form = $this->forms['searchForm'];
        $form->get('vehicleSearch')
            ->setOption('legend', 'licence.vehicle.table.search.list.legend');

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
        $view = $this->genericView();
        $view->setVariable('form', null);
        $view->setVariables($this->getViewVariables());
        return $view;
    }
}
