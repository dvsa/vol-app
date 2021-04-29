<?php

namespace Olcs\Controller\Licence\Vehicle;

use Olcs\Form\Model\Form\Vehicle\AddDuplicateVehicleConfirmation as AddDuplicateVehicleConfirmationForm;

class AddDuplicateVehicleController extends AbstractVehicleController
{
    use AddVehicleTrait;

    const PAGE_HEADER = "licence.vehicle.add.duplicate.header";

    protected $formConfig = [
        'default' => [
            'confirmationForm' => [
                'formClass' => AddDuplicateVehicleConfirmationForm::class,
            ]
        ]
    ];

    /**
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function indexAction()
    {
        // Redirect to add action if VRM is not in session.
        if (!$this->session->hasVehicleData()) {
            $this->flashMessenger->addErrorMessage('LicenceVehicleManagement does not contain vehicleData');
            return $this->nextStep('licence/vehicle/add/GET');
        }

        $params = $this->getViewVariables();
        $params['note'] = $this->translator->translateReplace(
            'licence.vehicle.add.duplicate.note',
            [
                $this->session->getVehicleData()['registrationNumber']
            ]
        );

        return $this->renderView($params);
    }

    /**
     * @return \Laminas\Http\Response
     */
    public function postAction()
    {
        // Redirect to add action if VRM is not in session.
        if (!$this->session->hasVehicleData()) {
            $this->flashMessenger->addErrorMessage('LicenceVehicleManagement does not contain vehicleData');
            return $this->nextStep('licence/vehicle/add/GET');
        }

        $vehicleData = $this->session->getVehicleData();

        $formData = (array)$this->getRequest()->getPost();
        $this->form->setData($formData);

        if (!$this->form->isValid()) {
            return $this->indexAction();
        }

        $selectedOption = $formData[AddDuplicateVehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME]
            [AddDuplicateVehicleConfirmationForm::FIELD_OPTIONS_NAME]
            ?? '';

        if (empty($selectedOption)) {
            $this->form
                ->get(AddDuplicateVehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME)
                ->get(AddDuplicateVehicleConfirmationForm::FIELD_OPTIONS_NAME)
                ->setMessages([
                    'licence.vehicle.add.duplicate.validation.select-an-option'
                ]);
            return $this->indexAction();
        }

        // Has the user selected no?
        if ($selectedOption != 'yes') {
            return $this->nextStep('licence/vehicle/add/POST');
        }

        // User selected yes
        $response = $this->handleCommand(
            $this->generateCreateVehicleCommand(
                $vehicleData['registrationNumber'],
                $vehicleData['make'],
                true,
                $vehicleData['revenueWeight'] ?? 0
            )
        );

        if ($response->isOk()) {
            $panelMessage = $this->translator->translateReplace('licence.vehicle.add.success', [$vehicleData['registrationNumber']]);
            $this->flashMessenger->addMessage($panelMessage, SwitchBoardController::PANEL_FLASH_MESSENGER_NAMESPACE);
            return $this->nextStep('licence/vehicle/GET');
        }
    }

    /**
     * @inheritDoc
     */
    protected function getViewVariables(): array
    {
        return [
            'title' => static::PAGE_HEADER,
            'licNo' => $this->data['licence']['licNo'],
            'content' => '',
            'form' => $this->form,
            'backLink' => $this->getLink('licence/vehicle/GET'),
        ];
    }
}
