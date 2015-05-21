<?php

/**
 * Interim Abstract Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Zend\View\Model\ViewModel;
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Data\FeeTypeDataService;
use Common\Service\Entity\FeeEntityService;

/**
 * Interim Abstract Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractInterimController extends AbstractController
{
    /**
     * Index action
     */
    public function indexAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToOverview();
        }

        if ($this->isButtonPressed('reprint')) {
            $this->getServiceLocator()->get('Helper\Interim')
                ->printInterimDocument($this->getApplicationId());

            $this->flashMessenger()->addSuccessMessage('internal.interim.generation_success');

            return $this->redirectToOverview();
        }

        $form = $this->getForm('Interim');
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData((array) $request->getPost());
            $response = $this->processForm($form);
            if ($response) {
                return $response;
            }
        } else {
            $this->populateForm($form);
        }

        $this->getServiceLocator()->get('Script')->loadFiles(['forms/interim']);

        return $this->render('interim', $form);
    }

    /**
     * Get form
     *
     * @param string $formName
     * @return Zend\Form\Form
     */
    public function getForm($formName)
    {
        if ($formName === 'Interim') {
            return $this->getInterimForm();
        } else {
            return $this->getServiceLocator()->get('Helper\Form')->createForm($formName);
        }
    }

    /**
     * Get interim form
     *
     * @return Zend\Form\Form
     */
    protected function getInterimForm()
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createForm('Interim');

        $application = $this->getInterimData();

        $formHelper->populateFormTable(
            $form->get('operatingCentres'),
            $this->getTable('interim.operatingcentres', $application['operatingCentres']),
            'operatingCentres'
        );

        $formHelper->populateFormTable(
            $form->get('vehicles'),
            $this->getTable('interim.vehicles', $application['licenceVehicles']),
            'vehicles'
        );

        return $this->alterInterimForm($form, $application);
    }

    /**
     * Alter form
     *
     * @param Zend\Form\Form $form
     * @return Zend\Form\Form
     */
    protected function alterInterimForm($form, $application)
    {
        $statusesToEnabledListbox = [
            ApplicationEntityService::INTERIM_STATUS_INFORCE,
            ApplicationEntityService::INTERIM_STATUS_REFUSED,
            ApplicationEntityService::INTERIM_STATUS_REVOKED
        ];
        $statusesToDisableFields = [
            ApplicationEntityService::INTERIM_STATUS_REFUSED,
            ApplicationEntityService::INTERIM_STATUS_REVOKED
        ];
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        if ($application['interimStatus']['id'] !== ApplicationEntityService::INTERIM_STATUS_REQUESTED) {
            $formHelper->remove($form, 'form-actions->grant');
            $formHelper->remove($form, 'form-actions->refuse');
        }
        if (!in_array($application['interimStatus']['id'], $statusesToEnabledListbox)) {
            $formHelper->remove($form, 'interimStatus->status');
        }

        if (in_array($application['interimStatus']['id'], $statusesToDisableFields)) {
            $formHelper->disableElement($form, 'data->interimReason');
            $formHelper->disableElement($form, 'data->interimStart');
            $formHelper->disableElement($form, 'data->interimEnd');
            $formHelper->disableElement($form, 'data->interimAuthVehicles');
            $formHelper->disableElement($form, 'data->interimAuthTrailers');
            $formHelper->disableElement($form, 'requested->interimRequested');
            $form->get('operatingCentres')->get('table')->getTable()->removeColumn('listed');
            $form->get('vehicles')->get('table')->getTable()->removeColumn('listed');
        }
        if ($application['interimStatus']['id'] == ApplicationEntityService::INTERIM_STATUS_INFORCE) {
            $formHelper->disableElement($form, 'requested->interimRequested');
        }
        if ($application['interimStatus']['id'] !== ApplicationEntityService::INTERIM_STATUS_INFORCE) {
            $formHelper->remove($form, 'form-actions->reprint');
        }
        return $form;
    }

    /**
     * Get table
     *
     * @param string $tableName
     * @param array $data
     * @return Table
     */
    protected function getTable($tableName, $data)
    {
        return $this->getServiceLocator()
            ->get('Table')
            ->buildTable($tableName, $data, ['url' => $this->getPluginManager()->get('url')], false);
    }

    /**
     * Process form depending of action
     *
     * @param Zend\Form\Form $form
     * @return mixed
     */
    protected function processForm($form)
    {
        // can't use $form->getData() here as a form not yet validated
        $currentStatus = $form->get('data')->get('interimCurrentStatus')->getValue();
        $requested = $form->get('requested')->get('interimRequested')->getValue();

        // if this form is Confirm need to find out which action we are currently processing
        $post = $this->params()->fromPost();
        $custom = isset($post['custom']) ? $post['custom'] : '';
        $applicationService = $this->getServiceLocator()->get('Entity\Application');

        if ($this->isButtonPressed('confirm') && $custom == 'grant') {
            return $this->processInterimGranting();
        }
        if ($this->isButtonPressed('confirm') && $custom == 'refuse') {
            return $this->processInterimRefusing();
        }

        if (!$currentStatus || $currentStatus == ApplicationEntityService::INTERIM_STATUS_REQUESTED) {

            return $this->processStatusRequested($currentStatus, $requested, $applicationService, $form);

        }
        if ($currentStatus == ApplicationEntityService::INTERIM_STATUS_REFUSED ||
            $currentStatus == ApplicationEntityService::INTERIM_STATUS_REVOKED) {

            return $this->processStatusRefusedRevoked($form, $applicationService);

        }
        if ($currentStatus == ApplicationEntityService::INTERIM_STATUS_INFORCE && $form->isValid()) {

            return $this->processStatusInforce($form, $applicationService);

        }
    }

    /**
     * Process requested status
     *
     * @param string $currentStatus
     * @param string $requested
     * @param Common\Service\Entity\Application $applicationService
     * @param Zend\Form\Form
     * @return mixed
     */
    protected function processStatusRequested($currentStatus, $requested, $applicationService, $form)
    {
        if ($this->isButtonPressed('save')) {

            return  $this->processSaveButton($requested, $form, $applicationService);

        }
        if (($this->isButtonPressed('grant') && $requested == 'Y' &&
            $currentStatus == ApplicationEntityService::INTERIM_STATUS_REQUESTED)) {

            return $this->processGrantButtonWhenRequested($form, $applicationService);

        }
        if (($this->isButtonPressed('refuse') && $requested == 'Y' &&
            $currentStatus == ApplicationEntityService::INTERIM_STATUS_REQUESTED) && $form->isValid()) {

            return $this->processRefuseButtonWhenRequested($form, $applicationService);

        }
    }

    /**
     * Process save button
     *
     * @param string $requested
     * @param Zend\Form\Form
     * @param Common\Service\Entity\Application $applicationService
     * @return Zend\Http\Redirect
     */
    protected function processSaveButton($requested, $form, $applicationService)
    {
        if ($requested == 'Y') {
            // validate form and save interim details if valid
            if ($form->isValid()) {
                // set up new interim data
                $applicationService->saveInterimData($form->getData(), true);

                // create interim fee if not exists
                $this->maybeCreateInterimFee();

                return $this->redirectToOverview(true);
            }
        } else {
            // remove interim details without validating form
            $formData = [
                'data' => [
                    'id' => $form->get('data')->get('id')->getValue(),
                    'version' => $form->get('data')->get('version')->getValue()
                ]
            ];
            // clear interim data
            $applicationService->saveInterimData($formData, false);

            // cancel interim fee if exists
            $this->maybeCancelInterimFee();

            return $this->redirectToOverview(true);
        }
    }

    /**
     * Process grant button when interim requested
     *
     * @param Zend\Form\Form
     * @param Common\Service\Entity\Application $applicationService
     * @return mixed
     */
    protected function processGrantButtonWhenRequested($form, $applicationService)
    {
        if ($form->isValid()) {
            // save interim data
            $applicationService->saveInterimData($form->getData(), true);
            return $this->processInterimGranting();
        }
    }

    /**
     * Process refuse button when interim requested
     *
     * @param Zend\Form\Form
     * @param Common\Service\Entity\Application $applicationService
     * @return mixed
     */
    protected function processRefuseButtonWhenRequested($form, $applicationService)
    {
        // save interim data
        $applicationService->saveInterimData($form->getData(), true);
        return $this->processInterimRefusing();
    }

    /**
     * Process refused or revoked status
     *
     * @param Zend\Form\Form
     * @param Common\Service\Entity\Application $applicationService
     * @return Zend\Http\Redirect
     */
    protected function processStatusRefusedRevoked($form, $applicationService)
    {
        // can't use $form->getData() because form is not validated
        $dataToSave = [
            'id' => $form->get('data')->get('id')->getValue(),
            'version' => $form->get('data')->get('version')->getValue(),
            'interimStatus' => $form->get('interimStatus')->get('status')->getValue()
        ];
        $applicationService->save($dataToSave);
        $this->addSuccessMessage('internal.interim.interim_updated');
        return $this->redirectToOverview();
    }

    /**
     * Process inforced status
     *
     * @param Zend\Form\Form
     * @param Common\Service\Entity\Application $applicationService
     * @return Zend\Http\Redirect
     */
    protected function processStatusInforce($form, $applicationService)
    {
        $applicationService->saveInterimData($form->getData(), true);
        $this->addSuccessMessage('internal.interim.interim_updated');
        return $this->redirectToOverview();
    }

    /**
     * Populate form with initial values
     *
     * @param Zend\Form\Form $form
     * @return Zend\Form\Form $form
     */
    protected function populateForm($form)
    {
        $application = $this->getInterimData();

        $data = [
            'data' => [
                'id' => $application['id'],
                'version' => $application['version'],
                // this is current interim status
                'interimCurrentStatus' => $application['interimStatus']['id'],
                'interimReason' => $application['interimReason'],
                'interimStart' => $application['interimStart'],
                'interimEnd' => $application['interimEnd'],
                'interimAuthVehicles' => $application['interimAuthVehicles'],
                'interimAuthTrailers' => $application['interimAuthTrailers']
            ],
            'requested' => [
                'interimRequested' => (!isset($application['interimStatus']['id']) ||
                    !$application['interimStatus']['id']) ? 'N' : 'Y'
            ],
            'interimStatus' => [
                // this status can be changed
                'status' => $application['interimStatus']['id']
            ]
        ];

        $form->setData($data);
        return $form;
    }

    /**
     * Redirect to overview interim page
     *
     * @param bool $success
     * @param string $action
     * @return Redirect
     */
    public function redirectToOverview($success = false, $action = '')
    {
        if ($success) {
            $this->addSuccessMessage('internal.interim.interim_details_saved');
        }
        $routeParams = [$this->getIdentifierIndex() => $this->getIdentifier(), 'action' => $action];
        return $this->redirect()->toRouteAjax('lva-' . $this->lva, $routeParams);
    }

    /**
     * Get interim data
     *
     * @return array
     */
    protected function getInterimData()
    {
        return $this->getServiceLocator()
            ->get('Entity\Application')
            ->getDataForInterim($this->getIdentifier());
    }

    /**
     * Create interim fee if needed
     *
     * @return array
     */
    protected function maybeCreateInterimFee()
    {
        // create fee if not exist
        if (!$this->getExistingFees()) {
            $this->getServiceLocator()->get('Processing\Application')->createFee(
                $this->getIdentifier(),
                $this->getInterimData()['licence']['id'],
                FeeTypeDataService::FEE_TYPE_GRANTINT
            );
        }
    }

    /**
     * Cancel interim fee if needed
     *
     * @return array
     */
    protected function maybeCancelInterimFee()
    {
        // get fees if exists
        $fees = $this->getExistingFees();

        $ids = [];
        foreach ($fees as $fee) {
            $ids[] = $fee['id'];
        }

        // cancel fees if exists
        if ($ids) {
            $this->getServiceLocator()->get('Entity\Fee')->cancelByIds($ids);
        }
    }

    /**
     * Get existing grant fees
     *
     * @return array
     */
    protected function getExistingFees()
    {
        $applicationId = $this->getIdentifier();

        // get current fee type
        $feeTypeData = $this->getServiceLocator()->get('Processing\Application')->getFeeTypeForApplication(
            $applicationId,
            FeeTypeDataService::FEE_TYPE_GRANTINT
        );

        return $this->getServiceLocator()->get('Entity\Fee')->getFeeByTypeStatusesAndApplicationId(
            $feeTypeData['id'],
            [FeeEntityService::STATUS_OUTSTANDING, FeeEntityService::STATUS_WAIVE_RECOMMENDED],
            $applicationId
        );
    }

    /**
     * Process interim granting
     *
     * @return array
     */
    protected function processInterimGranting()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirect()->refreshAjax();
        }
        $translator = $this->getServiceLocator()->get('translator');
        $response = $this->confirm(
            $translator->translate('internal.interim.form.grant_confirm'),
            true,
            'grant'
        );
        if ($response instanceof ViewModel) {
            return $response;
        }

        $existingFees = $this->getExistingFees();
        if (count($existingFees)) {
            $this->getServiceLocator()
                ->get('Helper\Interim')
                ->generateInterimFeeRequestDocument($this->getIdentifier(), $existingFees[0]['id']);
            $this->addSuccessMessage('internal.interim.interim_granted_fee_requested');
            $this->getServiceLocator()->get('Entity\Application')->forceUpdate(
                $this->getIdentifier(),
                ['interimStatus' => ApplicationEntityService::INTERIM_STATUS_GRANTED]
            );
        } else {
            // do in-force processing
            $this->getServiceLocator()->get('Helper\Interim')->grantInterim($this->getIdentifier());
            $this->addSuccessMessage('internal.interim.form.interim_in_force');
        }
        return $this->redirectToOverview();
    }

    /**
     * Process interim refusing
     *
     * @return array
     */
    protected function processInterimRefusing()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirect()->refreshAjax();
        }
        $translator = $this->getServiceLocator()->get('translator');
        $response = $this->confirm(
            $translator->translate('internal.interim.form.refuse_confirm'),
            true,
            'refuse'
        );
        if ($response instanceof ViewModel) {
            return $response;
        }

        $this->getServiceLocator()->get('Helper\Interim')->refuseInterim($this->getIdentifier());
        $this->addSuccessMessage('internal.interim.form.interim_refused');

        return $this->redirectToOverview();
    }
}
