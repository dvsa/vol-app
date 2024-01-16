<?php

/**
 * Abstract Interim Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\Data\Mapper\Lva\Interim as Mapper;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Application\GrantInterim;
use Dvsa\Olcs\Transfer\Command\Application\PrintInterimDocument;
use Dvsa\Olcs\Transfer\Command\Application\RefuseInterim;
use Dvsa\Olcs\Transfer\Query\Application\Interim;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Interim Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractInterimController extends AbstractController
{
    protected const ACTION_GRANTED = 'granted';
    protected const ACTION_IN_FORCE = 'in_force';
    protected const ACTION_FEE_REQUEST = 'fee_request';

    protected $updateInterimCommand;

    protected FlashMessengerHelperService $flashMessengerHelper;
    protected FormHelperService $formHelper;
    protected ScriptFactory $scriptFactory;
    protected TableFactory $tableFactory;

    /**
     * @param NiTextTranslation           $niTextTranslationUtil
     * @param AuthorizationService        $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormHelperService           $formHelper
     * @param ScriptFactory               $scriptFactory
     * @param TableFactory                $tableFactory
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        FormHelperService $formHelper,
        ScriptFactory $scriptFactory,
        TableFactory $tableFactory
    ) {
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->formHelper = $formHelper;
        $this->scriptFactory = $scriptFactory;
        $this->tableFactory = $tableFactory;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Index Action
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function indexAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToOverview();
        }

        if ($this->isButtonPressed('reprint')) {
            return $this->printInterim();
        }

        $interimData = $this->getInterimData();

        $form = $this->getInterimForm($interimData);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $requestData = (array)$request->getPost();
            $form->setData($requestData);
        } else {
            $form->setData(Mapper::mapFromResult($interimData));
        }

        if ($request->isPost() && $form->isValid()) {
            $formData = $form->getData();

            $dtoData = Mapper::mapFromForm($formData);
            $dtoData['id'] = $this->getIdentifier();
            $dtoData['action'] = $this->determinePostSaveAction();

            $command = call_user_func($this->updateInterimCommand . '::create', $dtoData);
            $response = $this->handleCommand($command);

            if ($response->isOk()) {
                $this->maybeDisplayCreateFeeMessage($response->getResult());
                return $this->postSaveRedirect();
            }

            $fm = $this->flashMessengerHelper;
            Mapper::mapFormErrors($form, $response->getResult()['messages'], $fm);
        }

        $this->scriptFactory->loadFiles(['forms/interim']);

        return $this->render('interim', $form);
    }

    /**
     * Optionally display create fee message
     *
     * @param array $result result
     *
     * @return void
     */
    protected function maybeDisplayCreateFeeMessage($result)
    {
        if (isset($result['messages'])) {
            foreach ($result['messages'] as $message) {
                if (is_array($message) && array_key_exists(RefData::ERROR_FEE_NOT_CREATED, $message)) {
                    $fm = $this->flashMessengerHelper;
                    $fm->addWarningMessage($message[RefData::ERROR_FEE_NOT_CREATED]);
                    break;
                }
            }
        }
    }

    /**
     * grantAction
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function grantAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $request = $this->getRequest();
        $formHelper = $this->formHelper;
        $form = $formHelper->createFormWithRequest('GenericConfirmation', $request);

        // override default label on confirm action button
        $form->get('messages')->get('message')->setValue('internal.interim.form.grant_confirm');

        if ($request->isPost()) {
            $response = $this->handleCommand(GrantInterim::create(['id' => $this->getIdentifier()]));

            $fm = $this->flashMessengerHelper;

            if ($response->isOk()) {
                $messageMap = [
                    self::ACTION_FEE_REQUEST => 'internal.interim.interim_granted_fee_requested',
                    self::ACTION_IN_FORCE => 'internal.interim.form.interim_in_force',
                    self::ACTION_GRANTED => 'internal.interim.interim_granted',
                ];
                $action = $response->getResult()['id']['action'];
                if (array_key_exists($action, $messageMap)) {
                    $fm->addSuccessMessage($messageMap[$action]);
                }
                return $this->redirectToOverview();
            } else {
                $fm->addUnknownError();
                return $this->redirectToIndex();
            }
        }

        return $this->render('grant.interim', $form);
    }

    /**
     * Process interim refusing
     *
     * @return array
     */
    public function refuseAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $request = $this->getRequest();
        $formHelper = $this->formHelper;
        $form = $formHelper->createFormWithRequest('GenericConfirmation', $request);

        // override default label on confirm action button
        $form->get('messages')->get('message')->setValue('internal.interim.form.refuse_confirm');

        if ($request->isPost()) {
            $response = $this->handleCommand(RefuseInterim::create(['id' => $this->getIdentifier()]));

            $fm = $this->flashMessengerHelper;

            if ($response->isOk()) {
                $fm->addSuccessMessage('internal.interim.form.interim_refused');
                return $this->redirectToOverview();
            } else {
                $fm->addUnknownError();
                return $this->redirectToIndex();
            }
        }

        return $this->render('refuse.interim', $form);
    }

    /**
     * Get table
     *
     * @param string $tableName tableName
     * @param array  $data      data
     *
     * @return TableBuilder
     */
    protected function getTable($tableName, $data)
    {
        return $this->tableFactory->prepareTable($tableName, $data);
    }

    /**
     * Get interim form
     *
     * @param array $interimData interimData
     *
     * @return \Laminas\Form\Form
     */
    protected function getInterimForm($interimData)
    {
        $formHelper = $this->formHelper;
        $form = $formHelper->createForm('Interim');

        $formHelper->populateFormTable(
            $form->get('operatingCentres'),
            $this->getTable('interim.operatingcentres', $interimData['operatingCentres']),
            'operatingCentres'
        );

        $formHelper->populateFormTable(
            $form->get('vehicles'),
            $this->getTable('interim.vehicles', $interimData['licenceVehicles']),
            'vehicles'
        );

        return $this->alterInterimForm($form, $interimData);
    }

    /**
     * Get interim data
     *
     * @return array
     */
    protected function getInterimData()
    {
        $response = $this->handleQuery(Interim::create(['id' => $this->getIdentifier()]));

        return $response->getResult();
    }

    /**
     * Alter form
     *
     * @param \Laminas\Form\Form $form        form
     * @param array              $application application
     *
     * @return \Laminas\Form\Form
     */
    protected function alterInterimForm($form, $application)
    {
        $formHelper = $this->formHelper;

        if (!$application['isInterimRequested']) {
            $formHelper->remove($form, 'form-actions->grant');
            $formHelper->remove($form, 'form-actions->refuse');
        }

        if (!$application['canSetStatus']) {
            $formHelper->remove($form, 'interimStatus->status');
        }

        if (!$application['canUpdateInterim']) {
            $formHelper->disableElement($form, 'data->interimReason');
            $formHelper->disableElement($form, 'data->interimStart');
            $formHelper->disableElement($form, 'data->interimEnd');
            $formHelper->disableElement($form, 'data->interimAuthHgvVehicles');
            $formHelper->disableElement($form, 'data->interimAuthLgvVehicles');
            $formHelper->disableElement($form, 'data->interimAuthTrailers');
            $formHelper->disableElement($form, 'requested->interimRequested');

            $form->get('operatingCentres')->get('table')->getTable()->removeColumn('listed');
            $form->get('vehicles')->get('table')->getTable()->removeColumn('listed');
        }

        $disableVehicleClassifications = false;

        switch ($application['vehicleType']['id']) {
            case RefData::APP_VEHICLE_TYPE_LGV:
                // remove HGV related fields
                $formHelper->remove($form, 'data->interimAuthHgvVehicles');
                $formHelper->remove($form, 'data->interimAuthTrailers');
                break;
            case RefData::APP_VEHICLE_TYPE_HGV:
            case RefData::APP_VEHICLE_TYPE_PSV:
                // disable vehicle classifications
                $disableVehicleClassifications = true;
                break;
            case RefData::APP_VEHICLE_TYPE_MIXED:
            default:
                if ($application['totAuthLgvVehicles'] === null) {
                    // disable vehicle classifications
                    $disableVehicleClassifications = true;
                }
                break;
        }

        if ($disableVehicleClassifications) {
            // disable vehicle classifications
            $form->get('data')->get('interimAuthHgvVehicles')->setLabel('internal.interim.form.interim_auth_vehicles');
            $formHelper->remove($form, 'data->interimAuthLgvVehicles');
        }

        if ($application['isInterimInforce']) {
            $formHelper->disableElement($form, 'requested->interimRequested');
        }

        if (!$application['isInterimInforce']) {
            $formHelper->remove($form, 'form-actions->reprint');
        }

        if ($this->isButtonPressed('grant')) {
            $form->getInputFilter()->get('data')->get('interimStart')->setRequired(true);
            $form->getInputFilter()->get('data')->get('interimEnd')->setRequired(true);
        }
        return $form;
    }

    /**
     * determinePostSaveAction
     *
     * @return null|string
     */
    protected function determinePostSaveAction()
    {
        if ($this->isButtonPressed('grant')) {
            return 'grant';
        }

        if ($this->isButtonPressed('refuse')) {
            return 'refuse';
        }

        return null;
    }

    /**
     * Redirect To Overview
     *
     * @return \Laminas\Http\Response
     */
    protected function redirectToOverview()
    {
        $routeParams = ['application' => $this->getIdentifier(), 'action' => null];

        return $this->redirect()->toRouteAjax('lva-' . $this->lva, $routeParams);
    }

    /**
     * redirect To index
     *
     * @return \Laminas\Http\Response
     */
    protected function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(null, ['action' => null], [], true);
    }

    /**
     * post Save Redirect
     *
     * @return \Laminas\Http\Response
     */
    protected function postSaveRedirect()
    {
        if ($this->isButtonPressed('grant')) {
            return $this->redirect()->toRoute(null, ['action' => 'grant'], [], true);
        }

        if ($this->isButtonPressed('refuse')) {
            return $this->redirect()->toRoute(null, ['action' => 'refuse'], [], true);
        }

        $this->flashMessengerHelper
            ->addSuccessMessage('internal.interim.interim_details_saved');

        return $this->redirectToOverview();
    }

    /**
     * printInterim
     *
     * @return \Laminas\Http\Response
     */
    protected function printInterim()
    {
        $flashMessenger = $this->flashMessengerHelper;

        $response = $this->handleCommand(PrintInterimDocument::create(['id' => $this->params('application')]));

        if ($response->isOk()) {
            $flashMessenger->addSuccessMessage('internal.interim.generation_success');
        } else {
            $flashMessenger->addUnknownError();
        }

        return $this->redirectToOverview();
    }
}
