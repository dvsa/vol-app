<?php

namespace Olcs\Controller\Licence\Vehicle;

use Olcs\Form\Model\Form\Vehicle\AddDuplicateVehicleConfirmation;

class AddDuplicateVehicleController extends AbstractVehicleController
{
    use AddVehicleTrait;

    const PAGE_HEADER = "licence.vehicle.add.duplicate.header";

    protected $formConfig = [
        'default' => [
            'confirmationForm' => [
                'formClass' => AddDuplicateVehicleConfirmation::class,
            ]
        ]
    ];

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        // Redirect to add action if VRM is not in session.
        if (!$this->session->hasVehicleData()) {
            $this->hlpFlashMsgr->addErrorMessage('LicenceVehicleManagement does not contain vehicleData');
            return $this->redirect()->toRoute('licence/vehicle/add/GET', [], [], true);
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
     * @return \Zend\Http\Response
     */
    public function postAction()
    {
        // Redirect to add action if VRM is not in session.
        if (!$this->session->hasVehicleData()) {
            $this->hlpFlashMsgr->addErrorMessage('LicenceVehicleManagement does not contain vehicleData');
            return $this->redirect()->toRoute('licence/vehicle/add/GET', [], [], true);
        }

        $vehicleData = $this->session->getVehicleData();

        $formData = (array)$this->getRequest()->getPost();
        $selectedOption = $formData[AddDuplicateVehicleConfirmation::FIELD_OPTIONS_FIELDSET_NAME]
            [AddDuplicateVehicleConfirmation::FIELD_OPTIONS_NAME]
            ?? '';

        // Has the user selected no?
        if ($selectedOption != 'yes') {
            return $this->redirect()->toRoute('licence/vehicle/add/POST', [], [], true);
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
            $this->hlpFlashMsgr->addSuccessMessage(
                $this->translator->translateReplace(
                    'licence.vehicle.add.success',
                    [$vehicleData['registrationNumber']]
                )
            );
            return $this->redirect()->toRoute('licence/vehicle/GET', [], [], true);
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
            'backLink' => $this->url()->fromRoute('licence/vehicle/GET', [], [], true),
            'bottomContent' => $this->translator->translateReplace(
                'licence.vehicle.generic.choose-different-action',
                [
                    $this->url()->fromRoute('licence/vehicle/GET', [], [], true)
                ]
            )
        ];
    }
}
