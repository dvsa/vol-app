<?php
declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

class ViewVehicleController extends AbstractVehicleController
{
    public function indexAction()
    {
        $view = $this->genericView();
        $view->setVariables($this->getViewVariables());

        return $view;
    }

    /**
     * @inheritdoc
     */
    protected function getViewVariables(): array
    {
        return [
            'title' => 'Edit a vehicle',
            'licNo' => $this->data['licence']['licNo'],
            'content' => '',
            'form' => $this->form,
            'backLink' => $this->getLink('licence/vehicle/GET')
        ];
    }
}
