<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\View\Helper\Panel;
use Dvsa\Olcs\Transfer\Query\Licence\Licence;
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\Plugin\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Router\RouteMatch;
use Laminas\Stdlib\ResponseInterface;
use Laminas\View\Model\ViewModel;
use Olcs\Form\Model\Form\Vehicle\SwitchBoard as SwitchBoardForm;
use Olcs\Session\LicenceVehicleManagement;

/**
 * @See SwitchBoardControllerFactory
 */
class SwitchBoardController
{
    const ROUTE_LICENCE_VEHICLE_ADD = 'licence/vehicle/add/GET';
    const ROUTE_LICENCE_VEHICLE_REMOVE = 'licence/vehicle/remove/GET';
    const ROUTE_LICENCE_VEHICLE_REPRINT = 'licence/vehicle/reprint/GET';
    const ROUTE_LICENCE_VEHICLE_TRANSFER = 'licence/vehicle/transfer/GET';
    const ROUTE_LICENCE_VEHICLE_LIST = 'licence/vehicle/list/GET';
    const ROUTE_LICENCE_OVERVIEW = 'lva-licence';

    const PANEL_FLASH_MESSENGER_NAMESPACE = 'panel';
    /**
     * @var FlashMessenger
     */
    private $flashMessenger;

    /**
     * @var FormHelperService
     */
    private $formHelper;

    /**
     * @var HandleQuery
     */
    private $queryHandler;

    /**
     * @var Redirect
     */
    private $redirectHelper;

    /**
     * @var ResponseHelperService
     */
    private $responseHelper;

    /**
     * @var LicenceVehicleManagement
     */
    private $session;

    /**
     * @var Url
     */
    private $urlHelper;

    public function __construct(
        FlashMessenger $flashMessenger,
        FormHelperService $formHelper,
        HandleQuery $queryHandler,
        Redirect $redirectHelper,
        ResponseHelperService $responseHelper,
        LicenceVehicleManagement $session,
        Url $urlHelper
    ) {
        $this->flashMessenger = $flashMessenger;
        $this->formHelper = $formHelper;
        $this->queryHandler = $queryHandler;
        $this->redirectHelper = $redirectHelper;
        $this->responseHelper = $responseHelper;
        $this->session = $session;
        $this->urlHelper = $urlHelper;
    }


    /**
     * Handles a request from a user to view the switchboard for a licence.
     *
     * @param Request $request
     * @param RouteMatch $routeMatch
     * @return ViewModel|ResponseInterface
     */
    public function indexAction(Request $request, RouteMatch $routeMatch)
    {
        $licenceId = (int) $routeMatch->getParam('licence');
        $this->session->getManager()->getStorage()->clear(LicenceVehicleManagement::SESSION_NAME);

        $licence = $this->getLicence($licenceId);

        $viewVariables = [
            'title' => 'licence.vehicle.switchboard.header',
            'subTitle' => $licence['licNo'],
            'form' => $this->createSwitchBoardForm($licence),
            'backLink' => $this->urlHelper->fromRoute(static::ROUTE_LICENCE_OVERVIEW, [], [], true)
        ];

        $panel = $this->flashMessenger->getMessages(static::PANEL_FLASH_MESSENGER_NAMESPACE);
        if (!empty($panel)) {
            $viewVariables['title'] = 'licence.vehicle.switchboard.header.after-journey';
            $viewVariables['panel'] = [
                'title' => $panel[0],
                'theme' => Panel::TYPE_SUCCESS,
            ];
        }

        $view = new ViewModel();
        $view->setTemplate('pages/licence/vehicle/switchboard');
        $view->setVariables($viewVariables);

        return $view;
    }

    /**
     * @param Request $request
     * @param RouteMatch $routeMatch
     * @return Response|ResponseInterface|ViewModel
     * @throws \Exception
     */
    public function decisionAction(Request $request, RouteMatch $routeMatch)
    {
        $licenceId = (int) $routeMatch->getParam('licence');
        $licence = $this->getLicence($licenceId);
        $formData = $request->getPost()->toArray();
        $form = $this->createSwitchBoardForm($licence, $formData);

        if (!$form->isValid()) {
            return $this->indexAction($request, $routeMatch);
        }

        $selectedOption = $formData[SwitchBoardForm::FIELD_OPTIONS_FIELDSET_NAME]
            [SwitchBoardForm::FIELD_OPTIONS_NAME]
            ?? '';

        switch ($selectedOption) {
            case SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_ADD:
                return $this->redirectHelper->toRoute(static::ROUTE_LICENCE_VEHICLE_ADD, [], [], true);
            case SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REMOVE:
                return $this->redirectHelper->toRoute(static::ROUTE_LICENCE_VEHICLE_REMOVE, [], [], true);
            case SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_REPRINT:
                return $this->redirectHelper->toRoute(static::ROUTE_LICENCE_VEHICLE_REPRINT, [], [], true);
            case SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_TRANSFER:
                return $this->redirectHelper->toRoute(static::ROUTE_LICENCE_VEHICLE_TRANSFER, [], [], true);
            case SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW:
                return $this->redirectHelper->toRoute(static::ROUTE_LICENCE_VEHICLE_LIST, [], [], true);
            default:
                throw new \Exception('Unexpected value');
        }
    }

    /**
     * Create the switchboard form and alter it based on licence vehicle status
     *
     * @param array $licence
     * @param array $formData
     * @return Form
     */
    protected function createSwitchBoardForm(array $licence, array $formData = []): Form
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

            if ($licence['totalVehicleCount'] === 0) {
                $radioFieldOptions->unsetValueOption(SwitchBoardForm::FIELD_OPTIONS_VALUE_LICENCE_VEHICLE_VIEW);
            } else {
                $valueOptions = $radioFieldOptions->getValueOptions();
                $valueOptions['view']['label'] = "licence.vehicle.switchboard.form.view.label-removed";
                $radioFieldOptions->setValueOptions($valueOptions);
            }
        }

        $form->setData($formData);

        return $form;
    }

    /**
     * Fetch a licence based on licence id
     *
     * @param int $licenceId
     * @return array|mixed
     */
    protected function getLicence(int $licenceId): array
    {
        return $this->queryHandler->__invoke(Licence::create(['id' => $licenceId]))->getResult();
    }
}
