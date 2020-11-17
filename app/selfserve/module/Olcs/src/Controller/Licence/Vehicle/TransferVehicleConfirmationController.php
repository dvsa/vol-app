<?php

namespace Olcs\Controller\Licence\Vehicle;

use Common\Service\Cqrs\Exception\AccessDeniedException;
use Common\Service\Cqrs\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Command\Licence\TransferVehicles;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\LicenceVehicle\LicenceVehiclesById;
use Olcs\DTO\Licence\LicenceDTO;
use Olcs\DTO\Licence\Vehicle\LicenceVehicleDTO;
use Olcs\Exception\Licence\Vehicle\LicenceNotFoundException;
use Olcs\Form\Model\Form\Vehicle\Fieldset\YesNo;
use Olcs\Form\Model\Form\Vehicle\RemoveVehicleConfirmation as RemoveVehicleConfirmationForm;
use Zend\Mvc\MvcEvent;
use Olcs\Exception\Licence\Vehicle\VehicleSelectionEmptyException;
use Zend\View\Model\ViewModel;
use Zend\Http\Response;
use Exception;
use Olcs\Exception\Licence\Vehicle\DestinationLicenceNotSetException;
use Olcs\Exception\Licence\Vehicle\DestinationLicenceNotFoundException;
use Olcs\Exception\Licence\Vehicle\VehicleNotFoundException;

class TransferVehicleConfirmationController extends AbstractVehicleController
{
    protected const ROUTE_TRANSFER_INDEX = 'licence/vehicle/transfer/GET';

    /**
     * @var array
     */
    protected $formConfig = [
        'default' => [
            'confirmationForm' => [
                'formClass' => RemoveVehicleConfirmationForm::class,
            ]
        ]
    ];

    /**
     * @inheritDoc
     */
    public function onDispatch(MvcEvent $e)
    {
        try {
            return parent::onDispatch($e);
        } catch (VehicleSelectionEmptyException $exception) {
            $this->hlpFlashMsgr->addErrorMessage('licence.vehicle.transfer.confirm.error.no-vehicles');
        } catch (VehicleNotFoundException $exception) {
            $this->hlpFlashMsgr->addErrorMessage('licence.vehicle.transfer.confirm.error.invalid-vehicles');
        } catch (DestinationLicenceNotSetException $exception) {
            $this->hlpFlashMsgr->addErrorMessage('licence.vehicle.transfer.confirm.error.no-destination-licence');
        } catch (DestinationLicenceNotFoundException $exception) {
            $this->hlpFlashMsgr->addErrorMessage('licence.vehicle.transfer.confirm.error.invalid-destination');
        } catch (LicenceNotFoundException $exception) {
            $this->hlpFlashMsgr->addErrorMessage('licence.vehicle.transfer.confirm.error.invalid-licence');
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
     * @throws DestinationLicenceNotFoundException
     * @throws DestinationLicenceNotSetException
     * @throws LicenceNotFoundException
     * @throws VehicleNotFoundException
     * @throws VehicleSelectionEmptyException
     */
    public function indexAction()
    {
        $destinationLicenceId = $this->resolveDestinationLicenceIdFromSession();
        $destinationLicence = $this->getLicenceById($destinationLicenceId);
        $destinationLicenceNumber = $destinationLicence->getLicenceNumber();
        $licenceVehicles = $this->getLicenceVehiclesByVehicleId($this->resolveVehicleIdsFromSession());
        $viewData = [
            'licNo' => $this->data['licence']['licNo'],
            'form' => $this->form,
            'backLink' => $this->getLink(static::ROUTE_TRANSFER_INDEX),
            'bottomContent' => $this->getChooseDifferentActionMarkup(),
            'destinationLicenceId' => $destinationLicenceId,
            'vrmList' => array_map(function (LicenceVehicleDTO  $licenceVehicle) {
                return $licenceVehicle->getVehicle()->getVrm();
            }, $licenceVehicles),
        ];
        if (count($licenceVehicles) !== 1) {
            $confirmHeaderKey = 'licence.vehicle.transfer.confirm.header.plural';
            $viewData['vrmListInfoText'] = 'licence.vehicle.transfer.confirm.list.hint.plural';
        } else {
            $confirmHeaderKey = 'licence.vehicle.transfer.confirm.header.singular';
            $viewData['vrmListInfoText'] = 'licence.vehicle.transfer.confirm.list.hint.singular';
        }
        $viewData['title'] = $this->translator->translateReplace($confirmHeaderKey, [$destinationLicenceNumber]);
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
        $destinationLicenceId = $this->resolveDestinationLicenceIdFromSession();
        $destinationLicence = $this->getLicenceById($destinationLicenceId);
        $formData = (array) $this->getRequest()->getPost();
        $this->form->setData($formData);
        if (! $this->form->isValid()) {
            return $this->indexAction();
        }

        $requestedAction = $formData[RemoveVehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME][RemoveVehicleConfirmationForm::FIELD_OPTIONS_NAME] ?? null;
        if (empty($requestedAction)) {
            $confirmationField = $this->form
                ->get(RemoveVehicleConfirmationForm::FIELD_OPTIONS_FIELDSET_NAME)
                ->get(RemoveVehicleConfirmationForm::FIELD_OPTIONS_NAME);
            $confirmationField->setMessages(['licence.vehicle.transfer.confirm.validation.select-an-option']);
            return $this->indexAction();
        }

        if ($requestedAction !== YesNo::OPTION_YES) {
            return $this->redirectToTransferIndex();
        }

        $this->transferVehicles($this->licenceId, $vehicleIds, $destinationLicenceId);
        $this->flashTransferOfVehiclesCompleted($destinationLicence, $vehicleIds);
        $this->flashIfLicenceHasNoVehicles($this->licenceId);
        return $this->nextStep('licence/vehicle/GET');
    }

    /**
     * Flashes a message to the user when a licence with a given id has no vehicles.
     *
     * @param int $licenceId
     * @throws Exception
     */
    protected function flashIfLicenceHasNoVehicles(int $licenceId)
    {
        $licence = $this->getLicenceById($licenceId);
        if ($licence->getActiveVehicleCount() < 1) {
            $message = $this->translator->translate('licence.vehicle.transfer.confirm.success.last-vehicle-transferred');
            $this->hlpFlashMsgr->addSuccessMessage($message);
        }
    }

    /**
     * Flashes a success message to signal that vehicles with the given ids have been transferred to a destination
     * licence.
     *
     * @param LicenceDTO $destinationLicence
     * @param array $vehicleIds
     */
    protected function flashTransferOfVehiclesCompleted(LicenceDTO $destinationLicence, array $vehicleIds)
    {
        if (count($vehicleIds) === 1) {
            $message = $this->translator->translateReplace(
                'licence.vehicle.transfer.confirm.success.singular',
                [$destinationLicence->getLicenceNumber()]
            );
        } else {
            $message = $this->translator->translateReplace(
                'licence.vehicle.transfer.confirm.success.plural',
                [count($vehicleIds), $destinationLicence->getLicenceNumber()]
            );
        }
        $this->hlpFlashMsgr->addSuccessMessage($message);
    }

    /**
     * Transfers one or more vehicles to a destination licence.
     *
     * @param int $currentLicenceId
     * @param array $vehicleIds
     * @param int $destinationLicenceId
     * @throws Exception
     */
    protected function transferVehicles(int $currentLicenceId, array $vehicleIds, int $destinationLicenceId)
    {
        $this->handleCommand(TransferVehicles::create([
            'id' => $currentLicenceId,
            'target' => $destinationLicenceId,
            'licenceVehicles' => $vehicleIds,
        ]));
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
     * @return int
     * @throws DestinationLicenceNotSetException
     */
    protected function resolveDestinationLicenceIdFromSession()
    {
        $destinationLicenceId = $this->session->getDestinationLicenceId();
        if (null === $destinationLicenceId) {
            throw new DestinationLicenceNotSetException();
        }
        return $destinationLicenceId;
    }

    /**
     * Gets the licence number for a licence from a given licence id.
     *
     * @param int $licenceId
     * @return LicenceDTO
     * @throws DestinationLicenceNotFoundException
     * @throws LicenceNotFoundException
     */
    protected function getLicenceById(int $licenceId): LicenceDTO
    {
        $query = Licence::create(['id' => $licenceId]);
        try {
            $queryResult = $this->handleQuery($query);
        } catch (NotFoundException|AccessDeniedException $exception) {
            throw LicenceNotFoundException::withId($licenceId);
        }
        $licence = new LicenceDTO($queryResult->getResult());
        if ($licence === null) {
            throw new DestinationLicenceNotFoundException();
        }
        return $licence;
    }

    /**
     * @param array<int> $vehicleIds
     * @return array<LicenceVehicleDTO>
     * @throws VehicleNotFoundException
     */
    protected function getLicenceVehiclesByVehicleId(array $vehicleIds): array
    {
        if (empty($vehicleIds)) {
            return [];
        }
        $query = LicenceVehiclesById::create(['ids' => $vehicleIds]);
        try {
            $queryResult = $this->handleQuery($query);
        } catch (NotFoundException|AccessDeniedException $exception) {
            throw VehicleNotFoundException::withIds($vehicleIds);
        }
        $licenceVehicles = $queryResult->getResult()['results'] ?? [];
        return array_map(function ($licenceVehicle) {
            return new LicenceVehicleDTO($licenceVehicle);
        }, $licenceVehicles);
    }
}
