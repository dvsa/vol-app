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
        if ($formName == 'Interim') {
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

        return $this->alterForm($form, $application);
    }

    /**
     * Alter form
     *
     * @param Zend\Form\Form $form
     * @return Zend\Form\Form
     */
    protected function alterForm($form, $application)
    {
        if ($application['interimStatus']['id'] !== ApplicationEntityService::INTERIM_STATUS_REQUESTED) {
            $formHelper = $this->getServiceLocator()->get('Helper\Form');
            $formHelper->remove($form, 'form-actions->grant');
            $formHelper->remove($form, 'form-actions->refuse');
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
        $status = $form->get('data')->get('interimStatus')->getValue();
        $requested = $form->get('requested')->get('interimRequested')->getValue();

        // if this form is Confirm need to find out which action we are currently processing
        $post = $this->params()->fromPost();
        $custom = isset($post['custom']) ? $post['custom'] : '';

        if ($this->isButtonPressed('confirm') && $custom == 'grant') {
            return $this->processInterimGranting();
        }
        if ($this->isButtonPressed('confirm') && $custom == 'refuse') {
            return $this->processInterimRefusing();
        }
        $applicationService = $this->getServiceLocator()->get('Entity\Application');

        if (!$status || $status == ApplicationEntityService::INTERIM_STATUS_REQUESTED) {
            if ($this->isButtonPressed('save')) {
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
            } elseif (($this->isButtonPressed('grant') && $requested == 'Y' &&
                $status == ApplicationEntityService::INTERIM_STATUS_REQUESTED)) {
                if ($this->getExistingFees()) {
                    $this->addErrorMessage('internal.interim.form.grant_not_allowed');
                    return $this->redirect()->refreshAjax();
                } elseif ($form->isValid()) {
                    // save interim data
                    $applicationService->saveInterimData($form->getData(), true);
                    return $this->processInterimGranting();
                }
            } elseif (($this->isButtonPressed('refuse') && $requested == 'Y' &&
                $status == ApplicationEntityService::INTERIM_STATUS_REQUESTED) && $form->isValid()) {
                    // save interim data
                    $applicationService->saveInterimData($form->getData(), true);
                    return $this->processInterimRefusing();
            }
        }
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
                'interimReason' => $application['interimReason'],
                'interimStart' => $application['interimStart'],
                'interimEnd' => $application['interimEnd'],
                'interimAuthVehicles' => $application['interimAuthVehicles'],
                'interimAuthTrailers' => $application['interimAuthTrailers'],
                'interimStatus' => isset($application['interimStatus']['id']) ?
                    $application['interimStatus']['id'] : ''
            ],
            'requested' => [
                'interimRequested' => (!isset($application['interimStatus']['id']) ||
                    !$application['interimStatus']['id']) ? 'N' : 'Y'
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
            return $this->redirect()->toRouteAjax(null);
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

        $this->getServiceLocator()->get('Helper\Interim')->grantInterim($this->getIdentifier());
        $this->addSuccessMessage('internal.interim.form.interim_granted');
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
            return $this->redirect()->toRouteAjax(null);
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
