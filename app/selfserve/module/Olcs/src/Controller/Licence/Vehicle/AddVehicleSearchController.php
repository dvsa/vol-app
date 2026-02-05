<?php

namespace Olcs\Controller\Licence\Vehicle;

use Common\Form\Elements\Types\AbstractInputSearch;
use Common\Form\Form;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Exception;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Olcs\Form\Model\Form\Vehicle\AddVehicleSearch;
use Olcs\Form\Model\Form\Vehicle\ConfirmVehicle;
use Permits\Data\Mapper\MapperManager;

class AddVehicleSearchController extends AbstractVehicleController
{
    use AddVehicleTrait;

    protected $formConfig = [
        'default' => [
            'confirmationForm' => [
                'formClass' => ConfirmVehicle::class,
            ],
            'vehicleSearchForm' => [
                'formClass' => AddVehicleSearch::class,
            ]
        ]
    ];

    protected $pageTemplate = 'pages/licence/vehicle/add';

    public const SEARCH_TITLE = 'licence.vehicle.add.search.title';

    public const RESULTS_TITLE = 'licence.vehicle.add.result.title';

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
     * @return \Laminas\View\Model\ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        $vehicleData = $this->session->getVehicleData();

        if ($vehicleData) {
            $this->form->setData([
                'vehicle-search' => [
                    'search-value' => $vehicleData['registrationNumber']
                ]
            ]);
            $this->alterConfirmationForm();
        }

        return $this->renderView(
            $this->createViewParametersForConfirmation($vehicleData)
        );
    }

    /**
     * @return \Laminas\View\Model\ViewModel
     */
    public function postAction()
    {
        $formData = (array)$this->getRequest()->getPost();
        $this->form->setData($formData);

        $vrm = $formData['vehicle-search'][AbstractInputSearch::ELEMENT_INPUT_NAME];

        if ($this->form->isValid()) {
            $vehicleData = $this->getVehicleData($vrm);
            $this->alterConfirmationForm();

            if (!empty($vehicleData)) {
                $this->session->setVehicleData($vehicleData);
            }
        }

        return $this->renderView(
            $this->createViewParametersForConfirmation($vehicleData, $vrm)
        );
    }

    /**
     * @return \Laminas\Http\Response
     */
    public function clearAction()
    {
        $this->session->destroy();
        return $this->nextStep('licence/vehicle/add/GET');
    }

    /**
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    #[\Override]
    public function confirmationAction()
    {
        // Redirect to add action if vehicleData is not in session.
        if (!$this->session->hasVehicleData()) {
            $this->flashMessenger->addErrorMessage('LicenceVehicleManagement does not contain vehicleData');
            return $this->nextStep('licence/vehicle/add/GET');
        }

        $vehicleData = $this->session->getVehicleData();

        if (empty($vehicleData)) {
            $this->flashMessenger->addErrorMessage("licence.vehicle.add.unable-to-add");
            return $this->nextStep('licence/vehicle/add/GET');
        }

        $response = $this->handleCommand(
            $this->generateCreateVehicleCommand(
                $vehicleData['registrationNumber'],
                $vehicleData['make'],
                false,
                $vehicleData['revenueWeight'] ?? 0
            )
        );

        if ($response->isOk()) {
            $panelMessage = $this->translationHelper->translateReplace('licence.vehicle.add.success', [$vehicleData['registrationNumber']]);
            $this->flashMessenger->addMessage($panelMessage, SwitchBoardController::PANEL_FLASH_MESSENGER_NAMESPACE);
            return $this->nextStep('lva-licence/vehicles');
        }

        // Is the VRM already defined on a licence?
        if (isset($response->getResult()['messages']['VE-VRM-2'])) {
            return $this->nextStep('licence/vehicle/add/duplicate-confirmation/GET');
        }

        $message = array_values($response->getResult()['messages']['vrm'])[0];
        $this->flashMessenger->addErrorMessage($message);

        return $this->nextStep('licence/vehicle/add/GET');
    }

    /**
     * @inheritDoc
     */
    protected function getViewVariables(): array
    {
        return [
            'title' => static::SEARCH_TITLE,
            'licNo' => $this->data['licence']['licNo'],
            'content' => '',
            'form' => $this->form,
            'backLink' => $this->getLink('lva-licence/vehicles'),
            'bottomLink' => $this->getLink('licence/vehicle/add/clear'),
            'bottomText' => 'licence.vehicle.clear-search'
        ];
    }

    /**
     * Alter the confirmation route to add the form action and set the vrm
     */
    private function alterConfirmationForm(): void
    {
        /** @var Form $form */
        $form = $this->forms['confirmationForm'];

        $form->setAttribute(
            'action',
            $this->getLink('licence/vehicle/add/confirmation')
        );
    }

    /**
     * @param null $searchedVrm
     * @return array
     */
    private function createViewParametersForConfirmation(?array $vehicleData, $searchedVrm = null): array
    {
        return array_merge(
            $this->getViewVariables(),
            [
                'vehicleData' => $vehicleData ?? null,
                'vrm' => $searchedVrm,
                'title' => $vehicleData ? static::RESULTS_TITLE : static::SEARCH_TITLE,
                'confirmationForm' => $this->forms['confirmationForm']
            ]
        );
    }

    protected function setFormErrorMessage(string $message, string $type): void
    {
        $this->form->get('vehicle-search')->setMessages([
            AbstractInputSearch::ELEMENT_INPUT_NAME => [
                $type => $this->translationHelper->translate($message)
            ]
        ]);
    }

    /**
     * @param $vrm
     * @return array|null
     */
    protected function getVehicleData($vrm): ?array
    {
        try {
            $vehicleData = $this->fetchDvlaVehicleData($vrm);
        } catch (NotFoundException) {
            $this->setFormErrorMessage('licence.vehicle.add.search.vrm-not-found', 'vrm_not_found');
        } catch (Exception) {
            $this->flashMessenger->addErrorMessage($this->translationHelper->translate('licence.vehicle.add.search.query-error'));
        }
        return $vehicleData ?? null;
    }
}
