<?php

namespace Olcs\Controller\Licence\Vehicle;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Olcs\Form\Model\Form\Vehicle\AddDuplicateVehicleConfirmation as AddDuplicateVehicleConfirmationForm;
use Permits\Data\Mapper\MapperManager;

class AddDuplicateVehicleController extends AbstractVehicleController
{
    use AddVehicleTrait;

    public const PAGE_HEADER = "licence.vehicle.add.duplicate.header";

    protected $formConfig = [
        'default' => [
            'confirmationForm' => [
                'formClass' => AddDuplicateVehicleConfirmationForm::class,
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
        // Redirect to add action if VRM is not in session.
        if (!$this->session->hasVehicleData()) {
            $this->flashMessenger->addErrorMessage('LicenceVehicleManagement does not contain vehicleData');
            return $this->nextStep('licence/vehicle/add/GET');
        }

        $params = $this->getViewVariables();
        $params['note'] = $this->translationHelper->translateReplace(
            'licence.vehicle.add.duplicate.note',
            [
                $this->session->getVehicleData()['registrationNumber']
            ]
        );

        return $this->renderView($params);
    }

    /**
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel|null
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
            $panelMessage = $this->translationHelper->translateReplace('licence.vehicle.add.success', [$vehicleData['registrationNumber']]);
            $this->flashMessenger->addMessage($panelMessage, SwitchBoardController::PANEL_FLASH_MESSENGER_NAMESPACE);
            return $this->nextStep('lva-licence/vehicles');
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
            'backLink' => $this->getLink('lva-licence/vehicles'),
        ];
    }
}
