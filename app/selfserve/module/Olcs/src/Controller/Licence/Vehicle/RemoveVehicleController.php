<?php
declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Olcs\Form\Model\Form\Vehicle\Vehicles as VehiclesForm;

class RemoveVehicleController extends AbstractVehicleController
{
    public const VEHICLE_REMOVE_LIMIT = 20;
    public const VEHICLE_WARNING_LIMIT = 10;

    protected $formConfig = [
        'default' => [
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
            'title' => 'licence.vehicle.remove.header',
            'licNo' => $this->data['licence']['licNo'],
            'content' => '',
            'form' => $this->form,
            'backLink' => $this->getLink('licence/vehicle/GET'),
            'bottomContent' => $this->getChooseDifferentActionMarkup()
        ];
    }

    public function alterForm($form)
    {
        $form->get('formActions')
            ->get('action')
            ->setLabel('licence.vehicle.remove.button');

        return $form;
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    protected function createView(): \Zend\View\Model\ViewModel
    {
        $vehicleTable = $this->createVehicleTable();
        $tableFieldset = $this->form->get('table');
        $tableFieldset->get('table')->setTable($vehicleTable);
        $tableFieldset->get('rows')->setValue(count($vehicleTable->getRows()));

        $view = $this->genericView();
        $view->setVariables($this->getViewVariables());

        if ($vehicleTable->getTotal() > static::VEHICLE_WARNING_LIMIT) {
            $view->setVariable('note', $this->translator->translate('licence.vehicle.remove.note'));
        }

        return $view;
    }
}
