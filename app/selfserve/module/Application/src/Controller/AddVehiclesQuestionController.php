<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Exception\BailOutException;
use Common\Exception\ResourceNotFoundException;
use Common\Form\FormValidator;
use Common\RefData;
use Common\Service\Cqrs\Exception\BadCommandResponseException;
use Common\Service\Cqrs\Exception\BadQueryResponseException;
use Dvsa\Olcs\Application\Controller\Factory\AddVehiclesQuestionControllerFactory;
use Dvsa\Olcs\Application\Form\AddVehiclesQuestionForm;
use Dvsa\Olcs\Application\View\Model\JourneyProgressDescriptionViewModel;
use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicles;
use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQuery;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Router\RouteMatch;
use Laminas\View\Model\ViewModel;

/**
 * A controller for the first step of the vehicle application journey which asks a user whether they would like to
 * submit vehicle details, for their application, at the current time.
 *
 * @see AddVehiclesQuestionControllerFactory
 * @see AddVehiclesQuestionControllerTest
 */
class AddVehiclesQuestionController
{
    protected const SECTION = 'vehicles';
    protected const FLASH_MESSAGE_INPUT_NAMESPACE = 'add-vehicles-question-controller-input';
    protected const LICENCE_CATEGORY = RefData::LICENCE_CATEGORY_GOODS_VEHICLE;
    protected const SUPPORTED_LICENCE_TYPES = [
        RefData::LICENCE_TYPE_RESTRICTED,
        RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
        RefData::LICENCE_TYPE_STANDARD_NATIONAL,
    ];

    /**
     * @var Url
     */
    protected $urlHelper;

    /**
     * @var Redirect
     */
    protected $redirectHelper;

    /**
     * @var HandleQuery
     */
    protected $queryHandler;

    /**
     * @var FlashMessenger
     */
    protected $flashMessenger;

    /**
     * @var FormValidator
     */
    protected $formValidator;

    /**
     * @var HandleCommand
     */
    protected $commandHandler;

    /**
     * @param Url $urlHelper
     * @param Redirect $redirectHelper
     * @param HandleQuery $queryHandler
     * @param FlashMessenger $flashMessenger
     * @param FormValidator $formValidator
     * @param HandleCommand $commandHandler
     */
    public function __construct(
        Url $urlHelper,
        Redirect $redirectHelper,
        HandleQuery $queryHandler,
        FlashMessenger $flashMessenger,
        FormValidator $formValidator,
        HandleCommand $commandHandler
    ) {
        $this->urlHelper = $urlHelper;
        $this->redirectHelper = $redirectHelper;
        $this->queryHandler = $queryHandler;
        $this->flashMessenger = $flashMessenger;
        $this->formValidator = $formValidator;
        $this->commandHandler = $commandHandler;
    }

    /**
     * Handles a requests from a user to view and submit to the add vehicle details question page.
     *
     * This handles posts instead of having a second action in order to satisfy backwards compatibility with a legacy
     * implementation.
     *
     * @param Request $request
     * @param RouteMatch $routeMatch
     * @return Response|ViewModel
     * @throws BadCommandResponseException
     * @throws BadQueryResponseException
     * @throws BailOutException
     * @throws ResourceNotFoundException
     */
    public function indexAction(Request $request, RouteMatch $routeMatch)
    {
        $applicationId = (int) $routeMatch->getParam('application');
        $applicationData = $this->getApplicationData($applicationId);

        if ($applicationData['status']['id'] !== RefData::APPLICATION_STATUS_NOT_SUBMITTED) {
            return $this->redirectHelper->toRoute('lva-application/submission-summary', ['application' => $applicationId]);
        }

        if ($request->isPost()) {
            return $this->storeAction($request, $routeMatch);
        }

        $flashedInput = null;
        if ($this->flashMessenger->hasMessages(static::FLASH_MESSAGE_INPUT_NAMESPACE)) {
            $flashedInput = json_decode(array_values($this->flashMessenger->getMessages(static::FLASH_MESSAGE_INPUT_NAMESPACE))[0], true);
        }

        // Build form
        $form = $this->newForm($flashedInput);
        $form->setApplicationVersion($applicationData['version']);
        if ($this->applicationHasBeenCompletedWithoutVehicles($applicationData)) {
            $form->selectNo();
        }

        // Build view
        $view = new ViewModel();
        $view->setTemplate('application/pages/ask-vehicles-question');
        $view->setVariables([
            'title' => 'application.vehicle.add-details.title',
            'form' => $form,
            'backUrl' => $this->urlHelper->fromRoute('lva-application', ['application' => $applicationId]),
            'pageSubTitle' => new JourneyProgressDescriptionViewModel(static::SECTION, $applicationData['sections']),
        ]);
        return $view;
    }

    /**
     * @param Request $request
     * @param RouteMatch $routeMatch
     * @return Response
     * @throws ResourceNotFoundException
     * @throws BadCommandResponseException
     * @throws BailOutException
     */
    protected function storeAction(Request $request, RouteMatch $routeMatch): Response
    {
        $applicationId = (int) $routeMatch->getParam('application');
        $form = $this->newForm($request->getPost()->toArray());

        if (! $this->formValidator->isValid($form)) {
            $this->flashMessenger->addMessage(json_encode($form->getData()), static::FLASH_MESSAGE_INPUT_NAMESPACE);
            return $this->redirectHelper->toRoute('lva-application/vehicles', ['application' => $applicationId]);
        }

        if ($form->userHasOptedToSubmitVehicleDetails()) {
            $this->updateVehicleSectionStatusToIncomplete($applicationId, $form->getApplicationVersionInput()->getValue());
            if ($form->userHasOptedToContinueToTheNextStep()) {
                throw new ResourceNotFoundException('This path still needs to be implemented');
            }
        }

        if ($form->userHasOptedNotToSubmitVehicleDetails()) {
            $this->updateVehicleSectionStatusToComplete($applicationId, $form->getApplicationVersionInput()->getValue());
            if ($form->userHasOptedToContinueToTheNextStep()) {
                return $this->redirectHelper->toRoute('lva-application/safety', ['application' => $applicationId]);
            }
        }

        return $this->redirectHelper->toRoute('lva-application', ['application' => $applicationId]);
    }

    /**
     * @param array|null $input
     * @return AddVehiclesQuestionForm
     */
    protected function newForm(array $input = null): AddVehiclesQuestionForm
    {
        $instance = new AddVehiclesQuestionForm('add_vehicles_question_form');
        if (null !== $input) {
            $instance->setData($input);
            $instance->isValid();
        }
        return $instance;
    }

    /**
     * @param int $applicationId
     * @return array
     * @throws ResourceNotFoundException
     * @throws BadQueryResponseException
     */
    protected function getApplicationData(int $applicationId): array
    {
        $query = ApplicationQuery::create(['id' => $applicationId]);
        $response = $this->queryHandler->__invoke($query);

        if ($response->getStatusCode() >= Response::STATUS_CODE_300 || $response->getStatusCode() < Response::STATUS_CODE_200) {
            $message = sprintf('Unexpected response status: "%s"', $response->getStatusCode());
            throw new BadQueryResponseException($message, $query, $response);
        }

        $applicationData = $response->getResult();

        if ($applicationData['isVariation']) {
            throw new ResourceNotFoundException('Entity with the id provided is a variation; expected an application');
        }

        if ($this->applicationHasVehicles($applicationData)) {
            throw new ResourceNotFoundException('This path still needs to be implemented');
        }

        if ($applicationData['goodsOrPsv']['id'] !== static::LICENCE_CATEGORY) {
            throw new ResourceNotFoundException('Request not supported for the licence category of this application');
        }

        if (! in_array($applicationData['licenceType']['id'], static::SUPPORTED_LICENCE_TYPES, true)) {
            throw new ResourceNotFoundException('Request not supported for the licence type of this application');
        }

        return $applicationData;
    }

    /**
     * @param int $applicationId
     * @param mixed $applicationVersion
     * @throws BadCommandResponseException
     * @throws BailOutException
     */
    protected function updateVehicleSectionStatusToComplete(int $applicationId, $applicationVersion)
    {
        return $this->updateVehicleSectionStatus($applicationId, $applicationVersion, false);
    }

    /**
     * @param int $applicationId
     * @param mixed $applicationVersion
     * @throws BadCommandResponseException
     * @throws BailOutException
     */
    protected function updateVehicleSectionStatusToIncomplete(int $applicationId, $applicationVersion)
    {
        return $this->updateVehicleSectionStatus($applicationId, $applicationVersion, true);
    }

    /**
     * @param int $applicationId
     * @param mixed $applicationVersion
     * @param bool $intendingToEnterVehicleDetails
     * @throws BadCommandResponseException
     * @throws BailOutException
     */
    protected function updateVehicleSectionStatus(int $applicationId, $applicationVersion, bool $intendingToEnterVehicleDetails)
    {
        $command = UpdateVehicles::create([
            'id' => $applicationId,
            'hasEnteredReg' => $intendingToEnterVehicleDetails ? 'Y' : 'N',
            'partial' => true,
            'version' => $applicationVersion,
        ]);
        $response = $this->commandHandler->__invoke($command);
        if ($response->getStatusCode() >= Response::STATUS_CODE_300 || $response->getStatusCode() < Response::STATUS_CODE_200) {
            $message = sprintf('Unexpected response status: "%s"', $response->getStatusCode());
            throw new BadCommandResponseException($message, $command, $response);
        }
    }

    /**
     * @param array $applicationData
     * @return bool
     */
    protected function applicationHasBeenCompletedWithoutVehicles(array $applicationData): bool
    {
        $currentSectionStatus = $applicationData['applicationCompletion']['vehiclesStatus'] ?? null;

        if ($currentSectionStatus !== RefData::APPLICATION_COMPLETION_STATUS_COMPLETE) {
            return false;
        }

        if ($this->applicationHasVehicles($applicationData)) {
            return false;
        }

        return true;
    }

    /**
     * @param array $applicationData
     * @return bool
     */
    protected function applicationHasVehicles(array $applicationData): bool
    {
        return $applicationData['hasEnteredReg'] === 'Y';
    }
}
