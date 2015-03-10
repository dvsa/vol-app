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
            
    protected $pageLayout = 'application-section';
    
    /**
     * Index action
     */
    public function indexAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToOverview();
        }
        $form = $this->getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData((array) $request->getPost());
            $response = $this->processForm($form);
            if ($response instanceof \Zend\Http\PhpEnvironment\Response) {
                return $response;
            }
        } else {
            $this->populateForm($form);
        }
        
        $view = new ViewModel(['form' => $form, 'title' => 'internal.interim.form.interim_application']);
        $view->setTemplate('partials/form');
        $this->getServiceLocator()->get('Script')->loadFiles(['forms/interim']);

        return $this->render($view);
    }

    /**
     * Get interim
     *
     * @return Zend\Form\Form
     */
    protected function getForm()
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createForm('interim');

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
            }
        }
        return;
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
     * Redirect to overview page
     *
     * @param bool $success
     * @return Redirect
     */
    public function redirectToOverview($success = false)
    {
        if ($success) {
            $this->addSuccessMessage('internal.interim.interim_details_saved');
        }
        $routeParams = [$this->getIdentifierIndex() => $this->getIdentifier(), 'action' => ''];
        return $this->redirect()->toRoute('lva-' . $this->lva, $routeParams);
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
        $applicationId = $this->getIdentifier();
        $applicationProcessingService = $this->getServiceLocator()->get('Processing\Application');
        // get current fee type
        $feeTypeData = $applicationProcessingService->getFeeTypeForApplication(
            $applicationId,
            FeeTypeDataService::FEE_TYPE_GRANTINT
        );
        
        // check if fees already exists
        $feeService = $this->getServiceLocator()->get('Entity\Fee');
        $statuses = [FeeEntityService::STATUS_OUTSTANDING, FeeEntityService::STATUS_WAIVE_RECOMMENDED];
        $fees = $feeService->getFeeByTypeStatusesAndApplicationId($feeTypeData['id'], $statuses ,$applicationId);
        
        // create fee if not exist
        if (!$fees) {
            $interimData = $this->getInterimData();
            $applicationProcessingService->createFee(
                $applicationId,
                $interimData['licence']['id'],
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
        $applicationId = $this->getIdentifier();
        $applicationProcessingService = $this->getServiceLocator()->get('Processing\Application');

        // get current fee type
        $feeTypeData = $applicationProcessingService->getFeeTypeForApplication(
            $applicationId,
            FeeTypeDataService::FEE_TYPE_GRANTINT
        );
        
        // get fees if exists
        $feeService = $this->getServiceLocator()->get('Entity\Fee');
        $statuses = [FeeEntityService::STATUS_OUTSTANDING, FeeEntityService::STATUS_WAIVE_RECOMMENDED];
        $fees = $feeService->getFeeByTypeStatusesAndApplicationId($feeTypeData['id'], $statuses ,$applicationId);
        $ids = [];
        foreach ($fees as $fee) {
            $ids[] = $fee['id'];
        }

        // cancel fees if exists
        if ($ids) {
            $feeService->cancelByIds($ids);
        }
    }
}
