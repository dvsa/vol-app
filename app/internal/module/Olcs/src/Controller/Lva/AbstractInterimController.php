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
use Common\Service\Entity\CommunityLicEntityService;

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
        $form = $this->getForm('interim');
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
        if ($formName == 'interim') {
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
                    return $this->processInterimGranting();
                }
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
        $translator = $this->getServiceLocator()->get('translator');
        $response = $this->confirm(
            $translator->translate('internal.interim.form.grant_confirm'),
            true,
            'grant'
        );
        if ($response instanceof ViewModel) {
            return $response;
        }

        if (!$this->isButtonPressed('cancel')) {
            $interimData = $this->getInterimData();

            // set interim status to in-force
            $dataToSave = [
                'id' => $interimData['id'],
                'version' => $interimData['version'],
                'interimStatus' => ApplicationEntityService::INTERIM_STATUS_INFORCE
            ];
            $this->getServiceLocator()->get('Entity\Application')->save($dataToSave);

            // get all vehicles for the given application and
            // set licence_vehicle.specified_date = current date/time
            $licenceVehciles = [];
            $activeDiscs = [];
            $newDiscs = [];
            foreach ($interimData['licenceVehicles'] as $licenceVehicle) {
                $lv = [
                    'id' => $licenceVehicle['id'],
                    'version' => $licenceVehicle['version'],
                    'specifiedDate' => $this->getServiceLocator()->get('Helper\Date')->getDate('Y-m-d H:i:s')
                ];
                $licenceVehciles[] = $lv;

                // saving all active discs to void it later
                foreach ($licenceVehicle['goodsDiscs'] as $disc) {
                    if (!$disc['ceasedDate']) {
                        $activeDiscs[] = $disc;
                    }
                }

                // preparing to create new pending disc
                $newDiscs[] = [
                    'licenceVehicle' => $licenceVehicle['id'],
                    'isInterim' => 'Y'
                ];
            }
            if ($licenceVehciles) {
                $this->getServiceLocator()->get('Entity\LicenceVehicle')->multiUpdate($licenceVehciles);
            }

            // all active discs, set goods_disc.ceased_date = current date/time wherever goods_disc.ceased_date is null
            $discsToVoid = [];
            foreach ($activeDiscs as $disc) {
                $dsc = [
                    'id' => $disc['id'],
                    'version' => $disc['version'],
                    'ceasedDate' => $this->getServiceLocator()->get('Helper\Date')->getDate('Y-m-d H:i:s')
                ];
                $discsToVoid[] = $dsc;
            }
            if ($discsToVoid) {
                $this->getServiceLocator()->get('Entity\GoodsDisc')->multiUpdate($discsToVoid);
            }

            // create a new pending discs record, Set the is_interim flag to 1
            if ($newDiscs) {
                $newDiscs['_OPTIONS_'] = [
                    'multiple' => true
                ];
                $this->getServiceLocator()->get('Entity\GoodsDisc')->save($newDiscs);
            }

            // activate community licence, set status to active and
            // set specified date to the current one where status = pending and licence id is current.
            $commLicsToActivate = [];
            $comLicsIds = [];
            foreach ($interimData['licence']['communityLics'] as $commLic) {
                if (isset($commLic['status']['id']) &&
                    $commLic['status']['id'] == CommunityLicEntityService::STATUS_PENDING) {
                    $cl = [
                        'id' => $commLic['id'],
                        'version' => $commLic['version'],
                        'status' => CommunityLicEntityService::STATUS_ACTIVE,
                        'specifiedDate' => $this->getServiceLocator()->get('Helper\Date')->getDate('Y-m-d H:i:s')
                    ];
                    $commLicsToActivate[] = $cl;

                    // saving community licences ids to document generation
                    $comLicsIds[] = $commLic['id'];
                }
            }
            if ($commLicsToActivate) {
                $this->getServiceLocator()->get('Entity\CommunityLic')->multiUpdate($commLicsToActivate);
            }

            // generate and print any pending community licences (take form communityLicService)
            $this->getServiceLocator()
                ->get('Helper\CommunityLicenceDocument')
                ->generateBatch($interimData['licence']['id'], $comLicsIds);

            $this->addSuccessMessage('internal.interim.form.interim_granted');
        }

        return $this->redirectToOverview();
    }
}
