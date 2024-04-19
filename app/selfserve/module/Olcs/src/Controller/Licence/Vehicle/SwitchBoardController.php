<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Form\FormValidator;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\View\Helper\Panel;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Router\RouteMatch;
use Laminas\Stdlib\ResponseInterface;
use Laminas\View\Model\ViewModel;
use Olcs\Form\Model\Form\Vehicle\SwitchBoard as SwitchBoardForm;
use Olcs\Session\LicenceVehicleManagement;

/**
 * @See SwitchBoardControllerFactory
 */
class SwitchBoardController
{
    public const ROUTE_LICENCE_VEHICLE_ADD = 'licence/vehicle/add/GET';
    public const ROUTE_LICENCE_VEHICLE_REMOVE = 'licence/vehicle/remove/GET';
    public const ROUTE_LICENCE_VEHICLE_REPRINT = 'licence/vehicle/reprint/GET';
    public const ROUTE_LICENCE_VEHICLE_TRANSFER = 'licence/vehicle/transfer/GET';
    public const ROUTE_LICENCE_VEHICLE_LIST = 'licence/vehicle/list/GET';
    public const ROUTE_LICENCE_OVERVIEW = 'lva-licence';

    public const PANEL_FLASH_MESSENGER_NAMESPACE = 'panel';
    protected const FLASH_MESSAGE_INPUT_NAMESPACE = 'switchboard-input';

    public function __construct(private FlashMessenger $flashMessenger, private FormHelperService $formHelper, private HandleQuery $queryHandler, private Redirect $redirectHelper, private ResponseHelperService $responseHelper, private LicenceVehicleManagement $session, private Url $urlHelper, private FormValidator $formValidator)
    {
    }


    /**
     * Handles a request from a user to view the switchboard for a licence.
     *
     * @return ViewModel|ResponseInterface
     */
    public function indexAction(Request $request, RouteMatch $routeMatch)
    {
        // Maintains backwards compatibility with OLD LVA Controller routing for /licence/{id}/vehicles
        if ($request->isPost()) {
            return $this->decisionAction($request, $routeMatch);
        }

        $licenceId = (int) $routeMatch->getParam('licence');
        $this->session->getManager()->getStorage()->clear(LicenceVehicleManagement::SESSION_NAME);

        $licence = $this->getLicence($licenceId);

        $flashedInput = null;
        if ($this->flashMessenger->hasMessages(static::FLASH_MESSAGE_INPUT_NAMESPACE)) {
            $flashedInput = json_decode(array_values($this->flashMessenger->getMessages(static::FLASH_MESSAGE_INPUT_NAMESPACE))[0], true);
        }

        $viewVariables = [
            'title' => 'licence.vehicle.switchboard.header',
            'subTitle' => $licence['licNo'],
            'form' => $this->createSwitchBoardForm($licence, $flashedInput),
            'backLink' => $this->urlHelper->fromRoute(static::ROUTE_LICENCE_OVERVIEW, [], [], true)
        ];

        $panel = $this->flashMessenger->getMessages(static::PANEL_FLASH_MESSENGER_NAMESPACE);
        if (!empty($panel)) {
            $viewVariables['title'] = 'licence.vehicle.switchboard.header.after-journey';
            $viewVariables['panel'] = [
                'title' => $panel[0],
                'theme' => Panel::TYPE_SUCCESS,
            ];

            if (isset($panel[1])) {
                $viewVariables['panel']['body'] = $panel[1];
            }
        }

        $view = new ViewModel();
        $view->setTemplate('pages/licence/vehicle/switchboard');
        $view->setVariables($viewVariables);

        return $view;
    }

    /**
     * @return Response|ResponseInterface|ViewModel
     * @throws \Exception
     */
    protected function decisionAction(Request $request, RouteMatch $routeMatch)
    {
        $licenceId = (int) $routeMatch->getParam('licence');
        $licence = $this->getLicence($licenceId);
        $formData = $request->getPost()->toArray();
        $form = $this->createSwitchBoardForm($licence, $formData);

        if (! $this->formValidator->isValid($form)) {
            $this->flashMessenger->addMessage(json_encode($form->getData()), static::FLASH_MESSAGE_INPUT_NAMESPACE);
            return $this->redirectHelper->toRoute('lva-licence/vehicles', [], [], true);
        }

        $selectedOption = $formData[SwitchBoardForm::FIELD_OPTIONS_FIELDSET_NAME]
            [SwitchBoardForm::FIELD_OPTIONS_NAME]
            ?? '';

        return match ($selectedOption) {
            SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD => $this->redirectHelper->toRoute(static::ROUTE_LICENCE_VEHICLE_ADD, [], [], true),
            SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REMOVE => $this->redirectHelper->toRoute(static::ROUTE_LICENCE_VEHICLE_REMOVE, [], [], true),
            SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REPRINT => $this->redirectHelper->toRoute(static::ROUTE_LICENCE_VEHICLE_REPRINT, [], [], true),
            SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER => $this->redirectHelper->toRoute(static::ROUTE_LICENCE_VEHICLE_TRANSFER, [], [], true),
            SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW => $this->redirectHelper->toRoute(static::ROUTE_LICENCE_VEHICLE_LIST, [], [], true),
            SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW_REMOVED => $this->redirectHelper->toRoute(
                static::ROUTE_LICENCE_VEHICLE_LIST,
                [],
                [
                    'query' => [
                        ListVehicleController::QUERY_KEY_INCLUDE_REMOVED => ''
                    ],
                    'fragment' => ListVehicleController::REMOVE_TABLE_WRAPPER_ID
                ],
                true
            ),
            default => throw new \Exception('Unexpected value'),
        };
    }

    /**
     * Create the switchboard form and alter it based on licence vehicle status
     *
     * @return Form
     */
    protected function createSwitchBoardForm(array $licence, array $formData = null): Form
    {
        $form = $this->formHelper->createForm(SwitchBoardForm::class);

        $radioFieldOptions = $form
            ->get(SwitchBoardForm::FIELD_OPTIONS_FIELDSET_NAME)
            ->get(SwitchBoardForm::FIELD_OPTIONS_NAME);

        if (!$licence['isMlh']) {
            $radioFieldOptions->unsetValueOption(SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER);
        }

        if ($licence['activeVehicleCount'] === 0) {
            $radioFieldOptions->unsetValueOption(SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REMOVE);
            $radioFieldOptions->unsetValueOption(SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REPRINT);
            $radioFieldOptions->unsetValueOption(SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER);
            $radioFieldOptions->unsetValueOption(SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW);
            if ($licence['totalVehicleCount'] === 0) {
                $radioFieldOptions->unsetValueOption(SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW_REMOVED);
            }
        } else {
            $radioFieldOptions->unsetValueOption(SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW_REMOVED);
        }

        if (null !== $formData) {
            $form->setData($formData);
            $this->formValidator->isValid($form);
        }

        return $form;
    }

    /**
     * Fetch a licence based on licence id
     *
     * @return array|mixed
     */
    protected function getLicence(int $licenceId): array
    {
        return $this->queryHandler->__invoke(Licence::create(['id' => $licenceId]))->getResult();
    }
}
