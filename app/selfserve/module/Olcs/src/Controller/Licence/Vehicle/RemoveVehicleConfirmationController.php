<?php
declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Dvsa\Olcs\Transfer\Command\Vehicle\DeleteLicenceVehicle;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\LicenceVehicle\LicenceVehiclesById;
use Olcs\Form\Model\Form\Vehicle\RemoveVehicleConfirmation as RemoveVehicleConfirmationForm;
use Olcs\Logging\Log\Logger;

class RemoveVehicleConfirmationController extends AbstractVehicleController
{

    protected $formConfig = [
        'default' => [
            'confirmationForm' => [
                'formClass' => RemoveVehicleConfirmationForm::class,
            ]
        ]
    ];

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        // Redirect to add action if VRMs are not in session.
        if (!$this->session->hasVrms()) {
            $this->hlpFlashMsgr->addErrorMessage('licence.vehicle.remove.confirm.error.no-vehicles');
            return $this->nextStep('licence/vehicle/remove/GET');
        }

        $vehicleIds = $this->session->getVrms();
        $vrms = $this->getVrmsForVehicleIds($vehicleIds);

        return $this->renderView(
            $this->createViewParametersForConfirmation($vrms)
        );
    }

    /**
     * @return \Zend\Http\Response
     * @throws \Exception
     */
    public function postAction()
    {
        // Redirect to remove action if VRMs are not in session.
        if (!$this->session->hasVrms()) {
            $this->hlpFlashMsgr->addErrorMessage('licence.vehicle.remove.confirm.error.no-vehicles');
            return $this->nextStep('licence/vehicle/remove/GET');
        }

        $formData = (array)$this->getRequest()->getPost();
        $this->form->setData($formData);

        if (!$this->form->isValid()) {
            return $this->indexAction();
        }

        $selectedOption = $formData[RemoveVehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME]
            [RemoveVehicleConfirmationForm::FIELD_OPTIONS_NAME]
            ?? '';

        if (empty($selectedOption)) {
            $this->form
                ->get(RemoveVehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME)
                ->get(RemoveVehicleConfirmationForm::FIELD_OPTIONS_NAME)
                ->setMessages([
                    'licence.vehicle.remove.confirm.validation.select-an-option'
                ]);
            return $this->indexAction();
        }

        // Has the user selected no?
        if ($selectedOption != 'yes') {
            return $this->nextStep('licence/vehicle/remove/GET');
        }

        $vehicleIds = $this->session->getVrms();
        $response = $this->handleCommand(DeleteLicenceVehicle::create([
            'ids' => $vehicleIds
        ]));

        if (!$response->isOk()) {
            Logger::err(
                "There was an error executing command DeleteLicenceVehicle",
                [
                    'vehicleIds' => $vehicleIds,
                    'response' => $response
                ]
            );
            throw new \Exception(
                'There was an error executing command DeleteLicenceVehicle',
                $response->getStatusCode()
            );
        }

        $licence = $this->handleQuery(Licence::create([
            'id' => $this->licenceId
        ]));

        $successMessageKey = 'licence.vehicle.remove.confirm.success.singular';
        if (count($vehicleIds) > 1) {
            $successMessageKey = 'licence.vehicle.remove.confirm.success.plural';
        }

        $this->hlpFlashMsgr->addSuccessMessage(
            $this->translator->translateReplace(
                $successMessageKey,
                [count($vehicleIds)]
            )
        );

        if ($licence->getResult()['activeVehicleCount'] == 0) {
            $this->hlpFlashMsgr->addSuccessMessage(
                $this->translator->translate(
                    'licence.vehicle.remove.confirm.success.last-vehicle-removed'
                )
            );
        }

        return $this->nextStep('licence/vehicle/GET');
    }

    /**
     * @inheritDoc
     */
    protected function getViewVariables(): array
    {
        return [
            'title' => 'licence.vehicle.remove.confirm.header.singular',
            'licNo' => $this->data['licence']['licNo'],
            'content' => '',
            'form' => $this->form,
            'backLink' => $this->getLink('licence/vehicle/remove/GET'),
            'bottomContent' => $this->getChooseDifferentActionMarkup()
        ];
    }

    /**
     * @param $vrms
     * @return array
     */
    private function createViewParametersForConfirmation(array $vrms): array
    {
        $viewParams = array_merge(
            $this->getViewVariables(),
            [
                'vrmList' => $vrms,
                'vrmListInfoText' => 'licence.vehicle.remove.confirm.list.hint.singular'
            ]
        );

        if (count($vrms) > 1) {
            $viewParams['title'] = 'licence.vehicle.remove.confirm.header.plural';
            $viewParams['vrmListInfoText'] = 'licence.vehicle.remove.confirm.list.hint.plural';
        }

        return $viewParams;
    }

    /**
     * @param array $vehicleIds
     * @return array
     * @throws \Exception
     */
    private function getVrmsForVehicleIds(array $vehicleIds): array
    {
        if (empty($vehicleIds)) {
            return [];
        }

        $queryResult = $this->handleQuery(LicenceVehiclesById::create([
            'ids' => $vehicleIds
        ]));

        if (!$queryResult->isOk()) {
            Logger::err(
                "There was an error when querying LicenceVehicleById",
                [
                    'vehicleIds' => $vehicleIds,
                    'queryResult' => $queryResult
                ]
            );
            throw new \Exception(
                "There was an error when querying LicenceVehiclesById",
                $queryResult->getStatusCode()
            );
        }

        $licenceVehicles = $queryResult->getResult();

        $result = [];
        foreach ($licenceVehicles['results'] as $licenceVehicle) {
            $result[] = $licenceVehicle['vehicle']['vrm'];
        }

        return $result;
    }
}
