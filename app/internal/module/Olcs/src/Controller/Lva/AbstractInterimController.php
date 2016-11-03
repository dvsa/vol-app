<?php

/**
 * Abstract Interim Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\Service\Table\TableBuilder;
use Dvsa\Olcs\Transfer\Command\Application\GrantInterim;
use Dvsa\Olcs\Transfer\Command\Application\PrintInterimDocument;
use Dvsa\Olcs\Transfer\Command\Application\RefuseInterim;
use Dvsa\Olcs\Transfer\Command\Application\UpdateInterim;
use Dvsa\Olcs\Transfer\Query\Application\Interim;
use Zend\View\Model\ViewModel;
use Common\Data\Mapper\Lva\Interim as Mapper;
use Common\RefData;

/**
 * Abstract Interim Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractInterimController extends AbstractController
{
    const ACTION_GRANTED = 'granted';
    const ACTION_IN_FORCE = 'in_force';
    const ACTION_FEE_REQUEST = 'fee_request';

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
            $form->setData((array)$request->getPost());
        } else {
            $form->setData(Mapper::mapFromResult($interimData));
        }

        if ($request->isPost() && $form->isValid()) {

            $dtoData = Mapper::mapFromForm($form->getData());

            $dtoData['id'] = $this->getIdentifier();
            $dtoData['action'] = $this->determinePostSaveAction();

            $response = $this->handleCommand(UpdateInterim::create($dtoData));

            if ($response->isOk()) {
                $this->maybeDisplayCreateFeeMessage($response->getResult());
                return $this->postSaveRedirect();
            }

            $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');
            Mapper::mapFormErrors($form, $response->getResult()['messages'], $fm);
        }

        $this->getServiceLocator()->get('Script')->loadFiles(['forms/interim']);

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
                    $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');
                    $fm->addWarningMessage($message[RefData::ERROR_FEE_NOT_CREATED]);
                    break;
                }
            }
        }
    }

    public function grantAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $request  = $this->getRequest();
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createFormWithRequest('GenericConfirmation', $request);

        // override default label on confirm action button
        $form->get('messages')->get('message')->setValue('internal.interim.form.grant_confirm');

        if ($request->isPost()) {

            $response = $this->handleCommand(GrantInterim::create(['id' => $this->getIdentifier()]));

            $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');

            if ($response->isOk()) {

                $messageMap = [
                    self::ACTION_FEE_REQUEST => 'internal.interim.interim_granted_fee_requested',
                    self::ACTION_IN_FORCE    => 'internal.interim.form.interim_in_force',
                    self::ACTION_GRANTED     => 'internal.interim.interim_granted',
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

        $request  = $this->getRequest();
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createFormWithRequest('GenericConfirmation', $request);

        // override default label on confirm action button
        $form->get('messages')->get('message')->setValue('internal.interim.form.refuse_confirm');

        if ($request->isPost()) {

            $response = $this->handleCommand(RefuseInterim::create(['id' => $this->getIdentifier()]));

            $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');

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
     * @param string $tableName
     * @param array $data
     * @return TableBuilder
     */
    protected function getTable($tableName, $data)
    {
        return $this->getServiceLocator()->get('Table')->prepareTable($tableName, $data);
    }

    /**
     * Get interim form
     *
     * @return \Zend\Form\Form
     */
    protected function getInterimForm($interimData)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
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
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    protected function alterInterimForm($form, $application)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

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
            $formHelper->disableElement($form, 'data->interimAuthVehicles');
            $formHelper->disableElement($form, 'data->interimAuthTrailers');
            $formHelper->disableElement($form, 'requested->interimRequested');

            $form->get('operatingCentres')->get('table')->getTable()->removeColumn('listed');
            $form->get('vehicles')->get('table')->getTable()->removeColumn('listed');
        }

        if ($application['isInterimInforce']) {
            $formHelper->disableElement($form, 'requested->interimRequested');
        }

        if (!$application['isInterimInforce']) {
            $formHelper->remove($form, 'form-actions->reprint');
        }

        return $form;
    }

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

    protected function redirectToOverview()
    {
        $routeParams = ['application' => $this->getIdentifier(), 'action' => null];

        return $this->redirect()->toRouteAjax('lva-' . $this->lva, $routeParams);
    }

    protected function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(null, ['action' => null], [], true);
    }

    protected function postSaveRedirect()
    {
        if ($this->isButtonPressed('grant')) {
            return $this->redirect()->toRoute(null, ['action' => 'grant'], [], true);
        }

        if ($this->isButtonPressed('refuse')) {
            return $this->redirect()->toRoute(null, ['action' => 'refuse'], [], true);
        }

        $this->getServiceLocator()->get('Helper\FlashMessenger')
            ->addSuccessMessage('internal.interim.interim_details_saved');

        return $this->redirectToOverview();
    }

    protected function printInterim()
    {
        $flashMessenger = $this->getServiceLocator()->get('Helper\FlashMessenger');

        $response = $this->handleCommand(PrintInterimDocument::create(['id' => $this->params('application')]));

        if ($response->isOk()) {
            $flashMessenger->addSuccessMessage('internal.interim.generation_success');
        } else {
            $flashMessenger->addUnknownError();
        }

        return $this->redirectToOverview();
    }
}
