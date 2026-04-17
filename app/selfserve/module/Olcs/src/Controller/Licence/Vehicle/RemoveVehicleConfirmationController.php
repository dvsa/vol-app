<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Vehicle\DeleteLicenceVehicle;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\LicenceVehicle\LicenceVehiclesById;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Olcs\Form\Model\Form\Vehicle\VehicleConfirmationForm;
use Olcs\Logging\Log\Logger;
use Permits\Data\Mapper\MapperManager;

class RemoveVehicleConfirmationController extends AbstractVehicleController
{
    protected $formConfig = [
        'default' => [
            'confirmationForm' => [
                'formClass' => VehicleConfirmationForm::class,
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
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        // Redirect to add action if VRMs are not in session.
        if (!$this->session->hasVrms()) {
            $this->flashMessenger->addErrorMessage('licence.vehicle.remove.confirm.error.no-vehicles');
            return $this->nextStep('licence/vehicle/remove/GET');
        }

        $vehicleIds = $this->session->getVrms();
        $vrms = $this->getVrmsForVehicleIds($vehicleIds);

        return $this->renderView(
            $this->createViewParametersForConfirmation($vrms)
        );
    }

    /**
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     *
     * @throws \Exception
     */
    public function postAction()
    {
        // Redirect to remove action if VRMs are not in session.
        if (!$this->session->hasVrms()) {
            $this->flashMessenger->addErrorMessage('licence.vehicle.remove.confirm.error.no-vehicles');
            return $this->nextStep('licence/vehicle/remove/GET');
        }

        $formData = (array)$this->getRequest()->getPost();
        $this->form->setData($formData);

        if (!$this->form->isValid()) {
            return $this->indexAction();
        }

        $selectedOption = $formData[VehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME]
            [VehicleConfirmationForm::FIELD_OPTIONS_NAME]
            ?? '';

        if (empty($selectedOption)) {
            $this->form
                ->get(VehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME)
                ->get(VehicleConfirmationForm::FIELD_OPTIONS_NAME)
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

        $panelMessage = $this->translationHelper->translateReplace($successMessageKey, [count($vehicleIds)]);
        $this->flashMessenger->addMessage($panelMessage, SwitchBoardController::PANEL_FLASH_MESSENGER_NAMESPACE);

        if ($licence->getResult()['activeVehicleCount'] == 0) {
            $this->flashMessenger->addMessage(
                $this->translationHelper->translate(
                    'licence.vehicle.remove.confirm.success.last-vehicle-removed'
                ),
                SwitchBoardController::PANEL_FLASH_MESSENGER_NAMESPACE
            );
        }

        return $this->nextStep('lva-licence/vehicles');
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
