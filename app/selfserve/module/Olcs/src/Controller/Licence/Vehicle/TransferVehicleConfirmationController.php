<?php

namespace Olcs\Controller\Licence\Vehicle;

use Common\Exception\BadRequestException;
use Common\Service\Cqrs\Exception\AccessDeniedException;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Licence\TransferVehicles;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\LicenceVehicle\LicenceVehiclesById;
use Exception;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ViewModel;
use Olcs\DTO\Licence\LicenceDTO;
use Olcs\DTO\Licence\Vehicle\LicenceVehicleDTO;
use Olcs\Exception\Licence\LicenceNotFoundWithIdException;
use Olcs\Exception\Licence\LicenceVehicleLimitReachedException;
use Olcs\Exception\Licence\Vehicle\DestinationLicenceNotFoundWithIdException;
use Olcs\Exception\Licence\Vehicle\DestinationLicenceNotSetException;
use Olcs\Exception\Licence\Vehicle\LicenceAlreadyAssignedVehicleException;
use Olcs\Exception\Licence\Vehicle\VehicleSelectionEmptyException;
use Olcs\Exception\Licence\Vehicle\VehiclesNotFoundWithIdsException;
use Olcs\Form\Model\Form\Vehicle\Fieldset\YesNo;
use Olcs\Form\Model\Form\Vehicle\VehicleConfirmationForm;
use Permits\Data\Mapper\MapperManager;

class TransferVehicleConfirmationController extends AbstractVehicleController
{
    protected const ROUTE_TRANSFER_INDEX = 'licence/vehicle/transfer/GET';

    /**
     * @var array
     */
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
     * @inheritDoc
     */
    #[\Override]
    public function onDispatch(MvcEvent $e)
    {
        try {
            return parent::onDispatch($e);
        } catch (VehicleSelectionEmptyException) {
            $this->flashMessenger->addErrorMessage('licence.vehicle.transfer.confirm.error.no-vehicles');
        } catch (VehiclesNotFoundWithIdsException) {
            $this->flashMessenger->addErrorMessage('licence.vehicle.transfer.confirm.error.invalid-vehicles');
        } catch (DestinationLicenceNotSetException) {
            $this->flashMessenger->addErrorMessage('licence.vehicle.transfer.confirm.error.no-destination-licence');
        } catch (DestinationLicenceNotFoundWithIdException) {
            $this->flashMessenger->addErrorMessage('licence.vehicle.transfer.confirm.error.invalid-destination');
        } catch (LicenceNotFoundWithIdException) {
            $this->flashMessenger->addErrorMessage('licence.vehicle.transfer.confirm.error.invalid-licence');
        } catch (LicenceVehicleLimitReachedException $exception) {
            $this->flashMessenger->addErrorMessage($this->translationHelper->translateReplace(
                'licence.vehicles_transfer.form.message_exceed',
                [$exception->getLicenceNumber()]
            ));
        } catch (LicenceAlreadyAssignedVehicleException $exception) {
            $vehicleVrms = $exception->getVehicleVrms();
            if (count($vehicleVrms) === 1) {
                $message = 'licence.vehicles_transfer.form.message_already_on_licence_singular';
                $data = [array_values($vehicleVrms)[0], $exception->getLicenceNumber()];
            } else {
                $message = 'licence.vehicles_transfer.form.message_already_on_licence';
                $data = [implode(', ', $vehicleVrms), $exception->getLicenceNumber()];
            }
            $this->flashMessenger->addErrorMessage($this->translationHelper->translateReplace($message, $data));
        }
        return $this->redirectToTransferIndex();
    }

    /**
     * Creates a response to redirect to the transfer vehicles index page.
     *
     * @return Response
     */
    protected function redirectToTransferIndex()
    {
        return $this->nextStep(static::ROUTE_TRANSFER_INDEX);
    }

    /**
     * Handles a request from a user to view the confirmation page for transferring one or more vehicles to a license.
     *
     * @return ViewModel
     * @throws DestinationLicenceNotFoundWithIdException
     * @throws DestinationLicenceNotSetException
     * @throws LicenceNotFoundWithIdException
     * @throws VehiclesNotFoundWithIdsException
     * @throws VehicleSelectionEmptyException
     */
    #[\Override]
    public function indexAction()
    {
        $destinationLicence = $this->resolveDestinationLicence();
        $destinationLicenceNumber = $destinationLicence->getLicenceNumber();
        $licenceVehicles = $this->getLicenceVehiclesByVehicleId($this->resolveVehicleIdsFromSession());
        $viewData = [
            'licNo' => $this->data['licence']['licNo'],
            'form' => $this->form,
            'backLink' => $this->getLink(static::ROUTE_TRANSFER_INDEX),
            'destinationLicenceId' => $destinationLicence->getId(),
            'vrmList' => array_map(fn(LicenceVehicleDTO $licenceVehicle) => $licenceVehicle->getVehicle()->getVrm(), $licenceVehicles),
        ];
        if (count($licenceVehicles) !== 1) {
            $confirmHeaderKey = 'licence.vehicle.transfer.confirm.header.plural';
            $viewData['vrmListInfoText'] = 'licence.vehicle.transfer.confirm.list.hint.plural';
        } else {
            $confirmHeaderKey = 'licence.vehicle.transfer.confirm.header.singular';
            $viewData['vrmListInfoText'] = 'licence.vehicle.transfer.confirm.list.hint.singular';
        }
        $viewData['title'] = $this->translationHelper->translateReplace($confirmHeaderKey, [$destinationLicenceNumber]);
        return $this->renderView($viewData);
    }

    /**
     * Handles a form submission from the confirmation page for transferring vehicles to a licence.
     *
     * @return Response|ViewModel
     * @throws Exception
     */
    public function postAction()
    {
        $vehicleIds = $this->resolveVehicleIdsFromSession();
        $destinationLicence = $this->resolveDestinationLicence();
        $formData = (array) $this->getRequest()->getPost();
        $this->form->setData($formData);
        if (! $this->form->isValid()) {
            return $this->indexAction();
        }

        $requestedAction = $formData[VehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME][VehicleConfirmationForm::FIELD_OPTIONS_NAME] ?? null;
        if (empty($requestedAction)) {
            $confirmationField = $this->form
                ->get(VehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME)
                ->get(VehicleConfirmationForm::FIELD_OPTIONS_NAME);
            $confirmationField->setMessages(['licence.vehicle.transfer.confirm.validation.select-an-option']);
            return $this->indexAction();
        }

        if ($requestedAction !== YesNo::OPTION_YES) {
            return $this->redirectToTransferIndex();
        }

        $this->transferVehicles($this->licenceId, $vehicleIds, $destinationLicence);
        $this->flashTransferOfVehiclesCompleted($destinationLicence, $vehicleIds);
        $this->flashIfLicenceHasNoVehicles($this->licenceId);
        return $this->nextStep('lva-licence/vehicles');
    }

    /**
     * Flashes a message to the user when a licence with a given id has no vehicles.
     *
     *
     * @throws Exception
     */
    protected function flashIfLicenceHasNoVehicles(int $licenceId): void
    {
        $licence = $this->getLicenceById($licenceId);
        $activeVehicleCount = $licence->getActiveVehicleCount();
        if (null !== $activeVehicleCount && $activeVehicleCount < 1) {
            $message = $this->translationHelper->translate('licence.vehicle.transfer.confirm.success.last-vehicle-transferred');
            $this->flashMessenger->addMessage($message, SwitchBoardController::PANEL_FLASH_MESSENGER_NAMESPACE);
        }
    }

    /**
     * Flashes a success message to signal that vehicles with the given ids have been transferred to a destination
     * licence.
     */
    protected function flashTransferOfVehiclesCompleted(LicenceDTO $destinationLicence, array $vehicleIds): void
    {
        if (count($vehicleIds) === 1) {
            $message = $this->translationHelper->translateReplace(
                'licence.vehicle.transfer.confirm.success.singular',
                [$destinationLicence->getLicenceNumber()]
            );
        } else {
            $message = $this->translationHelper->translateReplace(
                'licence.vehicle.transfer.confirm.success.plural',
                [count($vehicleIds), $destinationLicence->getLicenceNumber()]
            );
        }
        $this->flashMessenger->addMessage($message, SwitchBoardController::PANEL_FLASH_MESSENGER_NAMESPACE);
    }

    /**
     * Transfers one or more vehicles to a destination licence.
     *
     *
     * @throws Exception
     *
     * @return void
     */
    protected function transferVehicles(int $currentLicenceId, array $vehicleIds, LicenceDTO $destinationLicence)
    {
        $response = $this->handleCommand(TransferVehicles::create([
            'id' => $currentLicenceId,
            'target' => $destinationLicence->getId(),
            'licenceVehicles' => $vehicleIds,
        ]));
        $errors = $response->getResult()['messages'] ?? null;
        $errors = is_array($errors) ? $errors : [];
        if ($response->isClientError() && count($errors) > 0) {
            if (isset($errors['LIC_TRAN_1'])) {
                throw new LicenceVehicleLimitReachedException(
                    $destinationLicence->getId(),
                    $destinationLicence->getLicenceNumber()
                );
            }
            if (isset($errors['LIC_TRAN_2']) || isset($errors['LIC_TRAN_3'])) {
                $invalidVehiclesJson = $errors['LIC_TRAN_2'] ?? $errors['LIC_TRAN_3'];
                $invalidVehicleVrms = json_decode((string) $invalidVehiclesJson, true);
                throw new LicenceAlreadyAssignedVehicleException(
                    $destinationLicence->getId(),
                    $destinationLicence->getLicenceNumber(),
                    $invalidVehicleVrms
                );
            }
            throw new BadRequestException('Unexpected error when executing a command');
        }
        if (! $response->isOk()) {
            throw new BadRequestException('Unexpected response status received when executing a command');
        }
    }

    /**
     * Resolves any selected vehicle ids from a user's session.
     *
     * @return array<int>
     * @throws VehicleSelectionEmptyException
     */
    protected function resolveVehicleIdsFromSession()
    {
        $vehicleIds = $this->session->getVrms();
        if (empty($vehicleIds)) {
            throw new VehicleSelectionEmptyException();
        }
        $parsedVehicleIds = [];
        foreach ($vehicleIds as $vehicleId) {
            $parsedVehicleIds[] = (int) $vehicleId;
        }
        return $parsedVehicleIds;
    }

    /**
     * Resolves the id of the requested destination licence for any vehicles that are to be transferred.
     *
     * @return LicenceDTO
     * @throws DestinationLicenceNotFoundWithIdException
     * @throws DestinationLicenceNotSetException
     */
    protected function resolveDestinationLicence(): LicenceDTO
    {
        $destinationLicenceId = $this->session->getDestinationLicenceId();
        if (null === $destinationLicenceId) {
            throw new DestinationLicenceNotSetException();
        }
        try {
            $destinationLicence = $this->getLicenceById($destinationLicenceId);
        } catch (LicenceNotFoundWithIdException) {
            throw new DestinationLicenceNotFoundWithIdException($destinationLicenceId);
        }
        return $destinationLicence;
    }

    /**
     * Gets the licence number for a licence from a given licence id.
     *
     * @return LicenceDTO
     * @throws LicenceNotFoundWithIdException
     */
    protected function getLicenceById(int $licenceId): LicenceDTO
    {
        $query = Licence::create(['id' => $licenceId]);
        try {
            $queryResult = $this->handleQuery($query);
        } catch (NotFoundException | AccessDeniedException) {
            throw new LicenceNotFoundWithIdException($licenceId);
        }
        return new LicenceDTO($queryResult->getResult());
    }

    /**
     * @param array<int> $vehicleIds
     * @return array<LicenceVehicleDTO>
     * @throws VehiclesNotFoundWithIdsException
     */
    protected function getLicenceVehiclesByVehicleId(array $vehicleIds): array
    {
        if (empty($vehicleIds)) {
            return [];
        }
        $query = LicenceVehiclesById::create(['ids' => $vehicleIds]);
        try {
            $queryResult = $this->handleQuery($query);
        } catch (NotFoundException | AccessDeniedException) {
            throw new VehiclesNotFoundWithIdsException($vehicleIds);
        }
        $licenceVehicles = $queryResult->getResult()['results'] ?? [];
        return array_map(fn($licenceVehicle) => new LicenceVehicleDTO($licenceVehicle), $licenceVehicles);
    }
}
