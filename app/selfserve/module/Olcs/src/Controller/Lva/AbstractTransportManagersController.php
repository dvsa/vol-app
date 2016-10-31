<?php

/**
 * Abstract Transport Managers Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractTransportManagersController as CommonAbstractTmController;
use Common\Controller\Traits\GenericUpload;
use Common\RefData;
use Common\Service\Entity\TransportManagerApplicationEntityService;
use Common\Service\Entity\ApplicationEntityService;
use Dvsa\Olcs\Transfer\Command;

/**
 * Abstract Transport Managers Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractTransportManagersController extends CommonAbstractTmController
{
    use GenericUpload;

    const TYPE_OTHER_LICENCE = 'OtherLicences';
    const TYPE_PREVIOUS_CONVICTION = 'PreviousConvictions';
    const TYPE_PREVIOUS_LICENCE = 'PreviousLicences';
    const TYPE_OTHER_EMPLOYMENT = 'OtherEmployments';

    /**
     * Store the Transport Manager Application data
     */
    protected $tma;

    protected $deleteWhich;

    protected $formMap = [
        self::TYPE_OTHER_LICENCE => 'Lva\TmOtherLicence',
        self::TYPE_PREVIOUS_CONVICTION => 'TmConvictionsAndPenalties',
        self::TYPE_PREVIOUS_LICENCE => 'TmPreviousLicences',
        self::TYPE_OTHER_EMPLOYMENT => 'TmEmployment',
    ];

    protected $deleteCommandMap = [
        self::TYPE_OTHER_LICENCE => Command\OtherLicence\DeleteOtherLicence::class,
        self::TYPE_PREVIOUS_CONVICTION => Command\PreviousConviction\DeletePreviousConviction::class,
        self::TYPE_PREVIOUS_LICENCE => Command\OtherLicence\DeleteOtherLicence::class,
        self::TYPE_OTHER_EMPLOYMENT => Command\TmEmployment\DeleteList::class,
    ];

    /**
     * Revert to editing the form
     */
    public function editAction()
    {
        // move status back to incomplete
        $tmaId = (int) $this->params('child_id');
        if ($this->updateTmaStatus($tmaId, TransportManagerApplicationEntityService::STATUS_INCOMPLETE)) {
            return $this->redirect()->toRouteAjax("lva-{$this->lva}/transport_manager_details", [], [], true);
        } else {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }
    }

    /**
     * Display details of the Transport Manager Application process
     */
    public function detailsAction()
    {
        $tmaId = (int) $this->params('child_id');
        $tma = $this->getTmaDetails($tmaId);
        $isUserTm = $tma['isTmLoggedInUser'];

        switch ($tma['tmApplicationStatus']['id']) {
            case TransportManagerApplicationEntityService::STATUS_POSTAL_APPLICATION:
                return $this->pagePostal($tma);
                // no break
            case TransportManagerApplicationEntityService::STATUS_INCOMPLETE:
                $submitted = $this->getRequest()->getQuery('submitted') !== null;
                if ($isUserTm) {
                    if (!$submitted) {
                        return $this->page1Point1($tma);
                    } else {
                        return $this->page1Point2($tma);
                    }
                } else {
                    return $this->page1Point3($tma);
                }
                // no break
            case TransportManagerApplicationEntityService::STATUS_TM_SIGNED:
                if ($isUserTm) {
                    return $this->page2Point1($tma);
                } else {
                    return $this->page2Point2($tma);
                }
                // no break
            case TransportManagerApplicationEntityService::STATUS_OPERATOR_SIGNED:
                return $this->page3($tma, $isUserTm);
                // no break
            case TransportManagerApplicationEntityService::STATUS_RECEIVED:
                return $this->page4($tma, $isUserTm);
                // no break
        }
    }

    /**
     * Details page, the big form for TM to input all details
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected function page1Point1($transportManagerApplicationData)
    {
        $request = $this->getRequest();

        $postData = (array)$request->getPost();
        $formData = $this->formatFormData($transportManagerApplicationData, $postData);

        $form = $this->getDetailsForm($transportManagerApplicationData)->setData($formData);

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $hasProcessedAddressLookup = $formHelper->processAddressLookupForm($form, $request);

        $hasProcessedCertificateFiles = $this->processFiles(
            $form,
            'details->certificate',
            array($this, 'processCertificateUpload'),
            array($this, 'deleteFile'),
            array($this, 'getCertificates')
        );

        $hasProcessedResponsibilitiesFiles = $this->processFiles(
            $form,
            'responsibilities->file',
            array($this, 'processResponsibilityFileUpload'),
            array($this, 'deleteFile'),
            array($this, 'getResponsibilityFiles')
        );

        $hasProcessedFiles = ($hasProcessedCertificateFiles || $hasProcessedResponsibilitiesFiles);

        if (!$hasProcessedAddressLookup && !$hasProcessedFiles && $request->isPost()) {

            $submit = true;

            $crudAction = $this->getCrudAction($this->getFormTables($postData));

            // If we are saving, but not submitting
            if ($crudAction || $this->isButtonPressed('save')) {
                $submit = false;
                $formHelper->disableValidation($form->getInputFilter());
            }

            if ($form->isValid()) {
                $data = $form->getData();
                $hoursOfWeek = $data['responsibilities']['hoursOfWeek'];
                $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')->createCommand(
                    Command\TransportManagerApplication\UpdateDetails::create(
                        [
                            'id' => $transportManagerApplicationData['id'],
                            'version' => $transportManagerApplicationData['version'],
                            'email' => $data['details']['emailAddress'],
                            'placeOfBirth' => $data['details']['birthPlace'],
                            'homeAddress' => $data['homeAddress'],
                            'workAddress' => $data['workAddress'],
                            'operatingCentres' => $data['responsibilities']['operatingCentres'],
                            'tmType' => $data['responsibilities']['tmType'],
                            'isOwner' => $data['responsibilities']['isOwner'],
                            'hoursMon' => (float) $hoursOfWeek['hoursPerWeekContent']['hoursMon'],
                            'hoursTue' => (float) $hoursOfWeek['hoursPerWeekContent']['hoursTue'],
                            'hoursWed' => (float) $hoursOfWeek['hoursPerWeekContent']['hoursWed'],
                            'hoursThu' => (float) $hoursOfWeek['hoursPerWeekContent']['hoursThu'],
                            'hoursFri' => (float) $hoursOfWeek['hoursPerWeekContent']['hoursFri'],
                            'hoursSat' => (float) $hoursOfWeek['hoursPerWeekContent']['hoursSat'],
                            'hoursSun' => (float) $hoursOfWeek['hoursPerWeekContent']['hoursSun'],
                            'additionalInfo' => $data['responsibilities']['additionalInformation'],
                            'submit' => ($submit) ? 'Y' : 'N',
                            'dob' => $data['details']['birthDate']
                        ]
                    )
                );
                /* @var $response \Common\Service\Cqrs\Response */
                $response = $this->getServiceLocator()->get('CommandService')->send($command);
                if (!$response->isOk()) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
                    return $this->redirect()->refresh();
                }
                if ($crudAction !== null) {
                    return $this->handleCrudAction(
                        $crudAction,
                        [
                            'add-other-licence-applications',
                            'add-previous-conviction',
                            'add-previous-licence',
                            'add-employment'
                        ],
                        'grand_child_id',
                        'lva-' . $this->lva . '/transport_manager_details/action'
                    );
                }

                // save and return later
                if (!$submit) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')
                        ->addSuccessMessage('lva-tm-details-save-success');

                    return $this->redirectTmToHome();
                }

                return $this->redirect()->toRoute(null, [], ['query' => ['submitted' => 1]], true);
            }
        }

        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');

        $tmHeaderData = $transportManagerApplicationData['application'];
        $params = [
            'subTitle' => $translationHelper
                ->translateReplace(
                    'markup-tm-details-sub-title',
                    [
                        $tmHeaderData['goodsOrPsv']['description'],
                        $tmHeaderData['licence']['licNo'],
                        $tmHeaderData['id']
                    ]
                )
        ];

        $this->getServiceLocator()->get('Script')
            ->loadFiles(['lva-crud', 'tm-previous-history', 'tm-other-employment', 'tm-details']);

        $layout = $this->render('transport_managers-details', $form, $params);

        $content = $layout->getChildrenByCaptureTo('content')[0];
        $content->setTemplate('pages/lva-tm-details');

        return $layout;
    }

    public function addOtherLicenceApplicationsAction()
    {
        return $this->addOrEdit(self::TYPE_OTHER_LICENCE, 'add');
    }

    public function editOtherLicenceApplicationsAction()
    {
        $id = $this->params('grand_child_id');

        return $this->addOrEdit(self::TYPE_OTHER_LICENCE, 'edit', $id);
    }

    public function addPreviousConvictionAction()
    {
        return $this->addOrEdit(self::TYPE_PREVIOUS_CONVICTION, 'add');
    }

    public function editPreviousConvictionAction()
    {
        $id = $this->params('grand_child_id');

        return $this->addOrEdit(self::TYPE_PREVIOUS_CONVICTION, 'edit', $id);
    }

    public function addPreviousLicenceAction()
    {
        return $this->addOrEdit(self::TYPE_PREVIOUS_LICENCE, 'add');
    }

    public function editPreviousLicenceAction()
    {
        $id = $this->params('grand_child_id');

        return $this->addOrEdit(self::TYPE_PREVIOUS_LICENCE, 'edit', $id);
    }

    public function addEmploymentAction()
    {
        return $this->addOrEdit(self::TYPE_OTHER_EMPLOYMENT, 'add');
    }

    public function editEmploymentAction()
    {
        $id = $this->params('grand_child_id');

        return $this->addOrEdit(self::TYPE_OTHER_EMPLOYMENT, 'edit', $id);
    }

    public function deleteOtherLicenceApplicationsAction()
    {
        return $this->genericDelete(self::TYPE_OTHER_LICENCE);
    }

    public function deletePreviousConvictionAction()
    {
        return $this->genericDelete(self::TYPE_PREVIOUS_CONVICTION);
    }

    public function deletePreviousLicenceAction()
    {
        return $this->genericDelete(self::TYPE_PREVIOUS_LICENCE);
    }

    public function deleteEmploymentAction()
    {
        return $this->genericDelete(self::TYPE_OTHER_EMPLOYMENT);
    }

    /**
     * Delete confirmation and processing for each sub-section of TM
     *
     * @param string $type (Contant used to lookup services)
     * @return mixed
     */
    public function genericDelete($type = null)
    {
        $request = $this->getRequest();

        if ($request->isPost()) {

            $ids = explode(',', $this->params('grand_child_id'));

            $commandClass = $this->deleteCommandMap[$type];
            $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')
                ->createCommand($commandClass::create(['ids' => $ids]));
            /* @var $response \Common\Service\Cqrs\Response */
            $response = $this->getServiceLocator()->get('CommandService')->send($command);
            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage(
                    'transport_managers-details-' . $type . '-delete-success'
                );
            } else {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            return $this->backToDetails();
        }

        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest('GenericDeleteConfirmation', $request);

        $params = ['sectionText' => 'delete.confirmation.text'];

        return $this->render('delete', $form, $params);
    }

    protected function addOrEdit($type, $mode, $id = null)
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->backToDetails();
        }

        $request = $this->getRequest();

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createFormWithRequest($this->formMap[$type], $request);

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());
        } elseif ($mode === 'edit') {
            $form->setData($this->{'get' . $type . 'Data'}($id));
        }

        if ($mode !== 'add') {
            $formHelper->remove($form, 'form-actions->addAnother');
        }

        $hasProcessedAddressLookup = false;
        if ($this->isAddressForm($type)) {
            $hasProcessedAddressLookup = $formHelper->processAddressLookupForm($form, $request);
        }

        if (!$hasProcessedAddressLookup && $request->isPost() && $form->isValid()) {

            $data = $form->getData();
            if ($mode == 'add') {
                $command = $this->{'get' . $type . 'CreateCommand'}($data);
            } else {
                $command = $this->{'get' . $type . 'UpdateCommand'}($data);
            }

            $commandContainer = $this->getServiceLocator()->get('TransferAnnotationBuilder')
                ->createCommand($command);
            /* @var $response \Common\Service\Cqrs\Response */
            $response = $this->getServiceLocator()->get('CommandService')->send($commandContainer);

            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addSuccessMessage('lva.section.title.transport_managers-details-' . $type . '-success');
            } else {
                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addErrorMessage('unknown-error');
                return $this->render('transport_managers-details-' . $mode . '-' . $type, $form);
            }

            if ($this->isButtonPressed('addAnother')) {
                return $this->redirect()->refresh();
            }

            return $this->backToDetails($type);
        }

        return $this->render('transport_managers-details-' . $mode . '-' . $type, $form);
    }

    protected function isAddressForm($type)
    {
        if ($type === self::TYPE_OTHER_EMPLOYMENT) {
            return true;
        }
    }

    protected function getOtherLicencesCreateCommand($data)
    {
        $command = Command\OtherLicence\CreateForTma::create(
            [
                'tmaId' => $this->params('child_id'),
                'licNo' => $data['data']['licNo'],
                'role' => $data['data']['role'],
                'operatingCentres' => $data['data']['operatingCentres'],
                'totalAuthVehicles' => $data['data']['totalAuthVehicles'],
                'hoursPerWeek' => $data['data']['hoursPerWeek'],
            ]
        );

        return $command;
    }

    protected function getOtherLicencesUpdateCommand($data)
    {
        $command = Command\OtherLicence\UpdateForTma::create(
            [
                'id' => $data['data']['id'],
                'version' => $data['data']['version'],
                'licNo' => $data['data']['licNo'],
                'role' => $data['data']['role'],
                'operatingCentres' => $data['data']['operatingCentres'],
                'totalAuthVehicles' => $data['data']['totalAuthVehicles'],
                'hoursPerWeek' => $data['data']['hoursPerWeek'],
            ]
        );

        return $command;
    }

    protected function getOtherEmploymentsCreateCommand($data)
    {
        $command = Command\TmEmployment\Create::create(
            [
                'tmaId' => $this->params('child_id'),
                'position' => $data['tm-employment-details']['position'],
                'hoursPerWeek' => $data['tm-employment-details']['hoursPerWeek'],
                'employerName' => $data['tm-employer-name-details']['employerName'],
                'address' => [
                    'addressLine1' => $data['address']['addressLine1'],
                    'addressLine2' => $data['address']['addressLine2'],
                    'addressLine3' => $data['address']['addressLine3'],
                    'addressLine4' => $data['address']['addressLine4'],
                    'town' => $data['address']['town'],
                    'postcode' => $data['address']['postcode'],
                    'countryCode' => $data['address']['countryCode'],
                ]
            ]
        );

        return $command;
    }

    protected function getOtherEmploymentsUpdateCommand($data)
    {
        $command = Command\TmEmployment\Update::create(
            [
                'id' => $data['tm-employment-details']['id'],
                'version' => $data['tm-employment-details']['version'],
                'position' => $data['tm-employment-details']['position'],
                'hoursPerWeek' => $data['tm-employment-details']['hoursPerWeek'],
                'employerName' => $data['tm-employer-name-details']['employerName'],
                'address' => [
                    'addressLine1' => $data['address']['addressLine1'],
                    'addressLine2' => $data['address']['addressLine2'],
                    'addressLine3' => $data['address']['addressLine3'],
                    'addressLine4' => $data['address']['addressLine4'],
                    'town' => $data['address']['town'],
                    'postcode' => $data['address']['postcode'],
                    'countryCode' => $data['address']['countryCode'],
                    'version' => $data['address']['version'],
                ]
            ]
        );

        return $command;
    }

    protected function getPreviousConvictionsCreateCommand($data)
    {
        $command = Command\PreviousConviction\CreateForTma::create(
            [
                'tmaId' => $this->params('child_id'),
                'convictionDate' => $data['tm-convictions-and-penalties-details']['convictionDate'],
                'categoryText' => $data['tm-convictions-and-penalties-details']['categoryText'],
                'notes' => $data['tm-convictions-and-penalties-details']['notes'],
                'courtFpn' => $data['tm-convictions-and-penalties-details']['courtFpn'],
                'penalty' => $data['tm-convictions-and-penalties-details']['penalty'],
            ]
        );

        return $command;
    }

    protected function getPreviousConvictionsUpdateCommand($data)
    {
        $command = Command\PreviousConviction\UpdatePreviousConviction::create(
            [
                'id' => $data['tm-convictions-and-penalties-details']['id'],
                'version' => $data['tm-convictions-and-penalties-details']['version'],
                'convictionDate' => $data['tm-convictions-and-penalties-details']['convictionDate'],
                'categoryText' => $data['tm-convictions-and-penalties-details']['categoryText'],
                'notes' => $data['tm-convictions-and-penalties-details']['notes'],
                'courtFpn' => $data['tm-convictions-and-penalties-details']['courtFpn'],
                'penalty' => $data['tm-convictions-and-penalties-details']['penalty'],
            ]
        );

        return $command;
    }

    protected function getPreviousLicencesCreateCommand($data)
    {
        $command = Command\OtherLicence\CreatePreviousLicence::create(
            [
                'tmaId' => $this->params('child_id'),
                'licNo' => $data['tm-previous-licences-details']['licNo'],
                'holderName' => $data['tm-previous-licences-details']['holderName'],
            ]
        );

        return $command;
    }

    protected function getPreviousLicencesUpdateCommand($data)
    {
        $command = Command\OtherLicence\UpdateForTma::create(
            [
                'id' => $data['tm-previous-licences-details']['id'],
                'version' => $data['tm-previous-licences-details']['version'],
                'licNo' => $data['tm-previous-licences-details']['licNo'],
                'holderName' => $data['tm-previous-licences-details']['holderName'],
            ]
        );

        return $command;
    }

    protected function getOtherLicencesData($id)
    {
        $query = $this->getServiceLocator()->get('TransferAnnotationBuilder')
            ->createQuery(\Dvsa\Olcs\Transfer\Query\OtherLicence\OtherLicence::create(['id' => $id]));
        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->getServiceLocator()->get('QueryService')->send($query);

        return ['data' => $response->getResult()];
    }

    protected function getPreviousConvictionsData($id)
    {
        $query = $this->getServiceLocator()->get('TransferAnnotationBuilder')
            ->createQuery(\Dvsa\Olcs\Transfer\Query\PreviousConviction\PreviousConviction::create(['id' => $id]));
        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->getServiceLocator()->get('QueryService')->send($query);

        return ['tm-convictions-and-penalties-details' => $response->getResult()];
    }

    protected function getPreviousLicencesData($id)
    {
        $query = $this->getServiceLocator()->get('TransferAnnotationBuilder')
            ->createQuery(\Dvsa\Olcs\Transfer\Query\OtherLicence\OtherLicence::create(['id' => $id]));
        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->getServiceLocator()->get('QueryService')->send($query);

        return ['tm-previous-licences-details' => $response->getResult()];
    }

    protected function getOtherEmploymentsData($id)
    {
        return $this->getServiceLocator()->get('Helper\TransportManager')->getOtherEmploymentData($id);
    }

    /**
     * Handle the upload of transport manager certificates
     *
     * @param array $file
     * @return array
     */
    public function processCertificateUpload($file)
    {
        $data = $this->getServiceLocator()->get('Helper\TransportManager')
            ->getCertificateFileData($this->tma['transportManager']['id'], $file);

        $isOk = $this->uploadFile($file, $data);
        // reload TMA data with new uploaded document in it
        if ($isOk) {
            $this->getTmaDetails($this->tma['id']);
        }

        return $isOk;
    }

    /**
     * Handle the upload of responsibility files
     *
     * @param array $file
     * @return array
     */
    public function processResponsibilityFileUpload($file)
    {
        $data = $this->getServiceLocator()->get('Helper\TransportManager')
            ->getResponsibilityFileData($this->tma['transportManager']['id'], $file);

        $data['application'] = $this->getIdentifier();
        $data['licence'] = $this->getLicenceId();

        $isOk = $this->uploadFile($file, $data);
        // reload TMA data with new uploaded document in it
        if ($isOk) {
            $this->getTmaDetails($this->tma['id']);
        }

        return $isOk;
    }

    /**
     * Get transport manager certificates
     *
     * @return array
     */
    public function getCertificates()
    {
        $documents = [];
        foreach ($this->tma['transportManager']['documents'] as $document) {
            if ($document['category']['id'] === \Common\Category::CATEGORY_TRANSPORT_MANAGER &&
                $document['subCategory']['id'] === \Common\Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION
            ) {
                $documents[] = $document;
            }
        }

        return $documents;
    }

    /**
     * Get transport manager certificates
     *
     * @return array
     */
    public function getResponsibilityFiles()
    {
        $documents = [];
        foreach ($this->tma['transportManager']['documents'] as $document) {
            if ($document['category']['id'] === \Common\Category::CATEGORY_TRANSPORT_MANAGER &&
                $document['subCategory']['id'] ===
                    \Common\Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL &&
                $document['application']['id'] === $this->tma['application']['id']
            ) {
                $documents[] = $document;
            }
        }

        return $documents;
    }

    protected function formatFormData($data, $postData)
    {
        $contactDetails = $data['transportManager']['homeCd'];
        $person = $contactDetails['person'];

        if (!empty($postData)) {
            $formData = $postData;
        } else {

            $ocs = [];
            foreach ($data['operatingCentres'] as $oc) {
                $ocs[] = $oc['id'];
            }

            $formData = [
                'details' => [
                    'emailAddress' => $contactDetails['emailAddress'],
                    'birthPlace' => $person['birthPlace']
                ],
                'responsibilities' => [
                    'tmType' => $data['tmType']['id'],
                    'isOwner' => $data['isOwner'],
                    'additionalInformation' => $data['additionalInformation'],
                    'operatingCentres' => $ocs,
                    'hoursOfWeek' => [
                        'hoursPerWeekContent' => [
                            'hoursMon' => $data['hoursMon'],
                            'hoursTue' => $data['hoursTue'],
                            'hoursWed' => $data['hoursWed'],
                            'hoursThu' => $data['hoursThu'],
                            'hoursFri' => $data['hoursFri'],
                            'hoursSat' => $data['hoursSat'],
                            'hoursSun' => $data['hoursSun'],
                        ]
                    ]
                ],
                'homeAddress' => $contactDetails['address'],
                'workAddress' => $data['transportManager']['workCd']['address']
            ];
            if (!empty($person['birthDate'])) {
                $birthDate = new \DateTime($person['birthDate']);
                $formData['details']['birthDate'] = [
                    'day' => $birthDate->format('d'),
                    'month' => $birthDate->format('m'),
                    'year' => $birthDate->format('Y'),
                ];
            }
        }

        $formData['details']['name'] = $person['forename'] . ' ' . $person['familyName'];

        return $formData;
    }

    protected function getDetailsForm($tma)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $tmHelper = $this->getServiceLocator()->get('Helper\TransportManager');

        $form = $formHelper->createForm('Lva\TransportManagerDetails');

        $tmHelper->alterResponsibilitiesFieldset(
            $form->get('responsibilities'),
            $this->getOperatingCentreSelectOptions($tma),
            $this->getOtherLicencesTable($tma['otherLicences'])
        );

        $tmHelper->alterPreviousHistoryFieldsetTm(
            $form->get('previousHistory'),
            $tma['transportManager']
        );

        if ($tma['application']['niFlag'] === 'Y') {
            $form->get('previousHistory')->get('convictions')->get('table')->getTable()
                ->setEmptyMessage('transport-manager.convictionsandpenalties.table.empty.ni');
            $niOrGb = 'ni';
        } else {
            $niOrGb = 'gb';
        }

        $tmHelper->prepareOtherEmploymentTableTm($form->get('otherEmployment'), $tma['transportManager']);

        $formHelper->remove($form, 'responsibilities->tmApplicationStatus');

        return $form;
    }

    /**
     * Get the operating centre select options
     *
     * @param array $tma
     *
     * @return array
     */
    protected function getOperatingCentreSelectOptions($tma)
    {
        $options = [];
        $formatOptions = ['name' => 'address', 'addressFields' => ['addressLine1', 'town']];

        foreach ($tma['application']['licence']['operatingCentres'] as $loc) {
            $options[$loc['operatingCentre']['id']] =
                \Common\Service\Table\Formatter\Address::format($loc['operatingCentre'], $formatOptions);
        }

        foreach ($tma['application']['operatingCentres'] as $aoc) {
            if ($aoc['action'] === 'D') {
                unset($options[$aoc['operatingCentre']['id']]);
                continue;
            }
            $options[$aoc['operatingCentre']['id']] =
                \Common\Service\Table\Formatter\Address::format($aoc['operatingCentre'], $formatOptions);
        }

        asort($options);
        return $options;
    }


    protected function getOtherLicencesTable($otherLicences)
    {
        return $this->getServiceLocator()->get('Table')->prepareTable('tm.otherlicences-applications', $otherLicences);
    }

    /**
     * Need to override this, as the TM detials page is special
     */
    protected function checkForRedirect($lvaId)
    {
        if ($this->isButtonPressed('cancel')) {
            // If we are on a sub-section, we need to go back to the section
            $action = $this->params('action');
            $childId = $this->params('child_id');
            if ($childId !== null && $action !== 'details' && $action !== 'index') {
                return $this->backToDetails();
            }

            return $this->handleCancelRedirect($lvaId);
        }
    }

    /**
     * @param string $which Which section has just been completed
     *
     * @return \Zend\Http\Response
     */
    protected function backToDetails($which = null)
    {
        return $this->redirect()->toRouteAjax(
            'lva-' . $this->lva . '/transport_manager_details',
            [],
            $which === null ? [] : ['fragment' => lcfirst($which)],
            true
        );
    }

    protected function getFormTables($postData)
    {
        $formTables = [];

        // @NOTE 'table' is the otherLicences table, can't currently change this as it is re-used in internal
        foreach (['table', 'convictions', 'previousLicences', 'employment'] as $tableName) {
            if (isset($postData[$tableName])) {
                $formTables[] = $postData[$tableName];
            }
        }

        return $formTables;
    }

    /**
     * Redirect to TM Application details page or display a message if application is not pre-granted
     * This action is reached from an email sent to TM's
     */
    public function editDetailsAction()
    {
        $tmApplicationId = (int) $this->params('child_id');
        $tma = $this->getTmaDetails($tmApplicationId);

        $preGrantedStatuses = [
            ApplicationEntityService::APPLICATION_STATUS_NOT_SUBMITTED,
            ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
        ];
        if (!in_array($tma['application']['status']['id'], $preGrantedStatuses)) {
            return new \Zend\View\Model\ViewModel(['translateMessage' => 'markup-tma-edit-error']);
        }

        // redirect to TM details page
        return $this->redirect()->toRoute(
            "lva-{$this->lva}/transport_manager_details",
            [],
            [],
            true
        );
    }

    /**
     * Redirect a user to ether the dashboard or transport managers page depending on permissions
     */
    protected function redirectTmToHome()
    {
        if ($this->isGranted(RefData::PERMISSION_SELFSERVE_TM_DASHBOARD) &&
            !$this->isGranted(RefData::PERMISSION_SELFSERVE_LVA)) {
            return $this->redirect()->toRoute('dashboard');
        } else {
            return $this->redirect()->toRoute(
                "lva-{$this->lva}/transport_managers",
                ['application' => $this->getIdentifier()],
                [],
                false
            );
        }
    }

    protected function getTmaDetails($tmaId)
    {
        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetDetails::create(['id' => $tmaId])
        );

        // this is need for use in the processFiles callbacks
        $this->tma = $response->getResult();

        return $response->getResult();
    }

    /**
     * Update TMA status
     *
     * @param int    $tmaId
     * @param string $newStatus
     * @param int    $version
     *
     * @return bool Success?
     */
    protected function updateTmaStatus($tmaId, $newStatus, $version = null)
    {
        $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')
            ->createCommand(
                Command\TransportManagerApplication\UpdateStatus::create(
                    ['id' => $tmaId, 'status' => $newStatus, 'version' => $version]
                )
            );
        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->getServiceLocator()->get('CommandService')->send($command);

        return $response->isOk();
    }

    /**
     * TM form is complete, but not submitted yet
     *
     * @param array $tma
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function page1Point2(array $tma)
    {
        if ($this->getRequest()->isPost()) {
            $response = $this->handleCommand(
                Command\TransportManagerApplication\Submit::create(['id' => $tma['id']])
            );

            $flashMessenger = $this->getServiceLocator()->get('Helper\FlashMessenger');
            if ($response->isOk()) {
                $flashMessenger->addSuccessMessage('lva-tm-details-submit-success');
                return $this->redirect()->refresh();
            } else {
                $flashMessenger->addErrorMessage('unknown-error');
            }
        }
        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $params = [
            'content' => $translationHelper->translateReplace(
                $this->isTmOperator($tma) ? 'markup-tma-b1-2' : 'markup-tma-a1-2',
                [$this->getViewTmUrl()]
            ),
            'bottomContent' => sprintf(
                '<p><a href="%s">%s</a></p>',
                $this->url()->fromRoute(null, [], [], true),
                $translationHelper->translate('TMA_CHANGE_DETAILS')
            ),
            'backLink' => null,
        ];

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createForm('GenericConfirmation');
        /* @var $form \Common\Form\Form */
        $formHelper->setFormActionFromRequest($form, $this->getRequest());
        $submitLabel = $this->isTmOperator($tma) ? 'submit' : 'submit-for-operator-approval';
        $form->setSubmitLabel($submitLabel);
        $form->removeCancel();

        return $this->renderTmAction('transport-manager-application.review-and-submit', $form, $tma, $params);
    }

    /**
     * Incomplete, resend email to TM
     *
     * @param array $tma
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function page1Point3(array $tma)
    {
        if ($this->getRequest()->isPost()) {
            $this->resendTmEmail();
        }

        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $params = [
            'content' => $translationHelper->translate('markup-tma-ab1-3'),
            'bottomContent' => $translationHelper->translateReplace(
                'TMA_RESEND_TM1',
                $translationHelper->translate('TM1_FORM_LINK')
            ),
        ];

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createForm('TransportManagerApplicationResend');
        /* @var $form \Common\Form\Form */
        $formHelper->setFormActionFromRequest($form, $this->getRequest());
        $form->get('emailAddress')->setValue($tma['transportManager']['homeCd']['emailAddress']);

        return $this->renderTmAction('transport-manager-application.details-not-submitted', $form, $tma, $params);
    }

    /**
     * TM signed, TM view
     *
     * @param array $tma
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function page2Point1(array $tma)
    {
        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $params = [
            'content' => $translationHelper->translateReplace('markup-tma-a2-1', [$this->getEditTmUrl()]),
        ];

        return $this->renderTmAction('transport-manager-application.awaiting-operator-approval', null, $tma, $params);
    }

    /**
     * TM signed, Operator view
     *
     * @param array $tma
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function page2Point2(array $tma)
    {
        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getPost('emailAddress')) {
                // resend form submitted
                $this->resendTmEmail();
            } else {
                // approve Operator
                $response = $this->handleCommand(
                    Command\TransportManagerApplication\OperatorApprove::create(['id' => $tma['id']])
                );

                $flashMessenger = $this->getServiceLocator()->get('Helper\FlashMessenger');
                if ($response->isOk()) {
                    $flashMessenger->addSuccessMessage('operator-approve-message');
                    return $this->redirect()->refresh();
                } else {
                    $flashMessenger->addErrorMessage('unknown-error');
                }
            }
        }

        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $params = [
            'content' => $translationHelper->translateReplace(
                'markup-tma-a2-2',
                [$this->getViewTmUrl(), $this->url()->fromRoute(null, [], [], true)]
            ),
            'bottomContent' => $translationHelper->translate('TMA_WRONG_DETAILS'),
            'backLink' => null,
        ];

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createForm('GenericConfirmation');
        /* @var $form \Common\Form\Form */
        $formHelper->setFormActionFromRequest($form, $this->getRequest());
        $form->setSubmitLabel('approve-details');
        $form->removeCancel();

        $resendForm = $formHelper->createForm('TransportManagerApplicationResend');
        /* @var $form \Common\Form\Form */
        $formHelper->setFormActionFromRequest($resendForm, $this->getRequest());
        $resendForm->get('emailAddress')->setValue($tma['transportManager']['homeCd']['emailAddress']);

        $params['resendForm'] = $resendForm;

        return $this->renderTmAction('transport-manager-application.review-and-submit', $form, $tma, $params);
    }

    /**
     * Operator signed
     *
     * @param array $tma
     * @param bool $isUserTm
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function page3(array $tma, $isUserTm)
    {
        if ($this->isTmOperator($tma)) {
            if ($isUserTm) {
                $template = 'markup-tma-b3-1';
            } else {
                $template = 'markup-tma-b3-2';
            }
        } else {
            if ($isUserTm) {
                $template = 'markup-tma-a3-1';
            } else {
                $template = 'markup-tma-a3-2';
            }
        }

        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $params['content'] = $translationHelper->translateReplace($template, [$this->getViewTmUrl()]);

        return $this->renderTmAction('transport-manager-application.print-sign', null, $tma, $params);
    }

    /**
     * Received
     *
     * @param array $tma
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function page4(array $tma)
    {
        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $params['content'] = $translationHelper->translateReplace('markup-tma-ab-4', [$this->getViewTmUrl()]);

        return $this->renderTmAction('transport-manager-application.details-received', null, $tma, $params);
    }

    /**
     * Postal application
     *
     * @param array $tma
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function pagePostal(array $tma)
    {
        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $params = [
            'content' => $translationHelper->translateReplace('markup-tma-c-0', [$this->getViewTmUrl()]),
            'backLink' => null,
        ];

        return $this->renderTmAction('transport-manager-application.print-sign', null, $tma, $params);
    }

    /**
     * Render the Transport manager application process pages
     *
     * @param string $title
     * @param \Common\Form\Form $form
     * @param array $tma
     * @param array $params
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function renderTmAction($title, $form, $tma, $params)
    {
        $defaultParams = [
            'tmFullName' => trim(
                $tma['transportManager']['homeCd']['person']['forename'].' '
                .$tma['transportManager']['homeCd']['person']['familyName']
            ),
            'backLink' => $this->getBacklink(),
            'backText' => $this->isTransportManagerRole() ? 'transport-manager-back-text-tm' :
                'transport-manager-back-text-admin',
        ];

        $params = array_merge($defaultParams, $params);

        $layout = $this->render($title, $form, $params);
        /* @var $layout \Zend\View\Model\ViewModel */

        $content = $layout->getChildrenByCaptureTo('content')[0];
        $content->setTemplate('pages/lva-tm-details-action');

        return $layout;
    }

    /**
     * Get the URL to review wth TMA
     *
     * @param id $tmaId
     *
     * @return string
     */
    private function getViewTmUrl()
    {
        $tmaId = (int) $this->params('child_id');
        return $this->url()->fromRoute('transport_manager_review', ['id' => $tmaId]);
    }

    /**
     * Get the URL to edit the TMA
     *
     * @return string
     */
    private function getEditTmUrl()
    {
        return $this->url()->fromRoute(
            "lva-{$this->lva}/transport_manager_details/action",
            ['action' => 'edit'],
            [],
            true
        );
    }

    /**
     * is the logged in user just TM, eg not an admin
     *
     * @return bool
     */
    private function isTransportManagerRole()
    {
        return ($this->isGranted(RefData::PERMISSION_SELFSERVE_TM_DASHBOARD) &&
            !$this->isGranted(RefData::PERMISSION_SELFSERVE_LVA));
    }

    /**
     * Is the TMA set as the operator/owner
     *
     * @param array $tma
     *
     * @return book
     */
    private function isTmOperator(array $tma)
    {
        return isset($tma['isOwner']) && $tma['isOwner'] == 'Y';
    }

    /**
     * Get the URL/link to go back
     *
     * @return string
     */
    private function getBacklink()
    {
        if ($this->isTransportManagerRole()) {
            return $this->url()->fromRoute('dashboard');
        } else {
            return $this->url()->fromRoute(
                "lva-{$this->lva}/transport_managers",
                ['application' => $this->getIdentifier()],
                [],
                false
            );
        }
    }

    /**
     * Resend the TMA application email to the TM
     */
    private function resendTmEmail()
    {
        $tmaId = (int) $this->params('child_id');
        $response = $this->handleCommand(
            Command\TransportManagerApplication\SendTmApplication::create(['id' => $tmaId])
        );

        $flashMessenger = $this->getServiceLocator()->get('Helper\FlashMessenger');
        if ($response->isOk()) {
            $flashMessenger->addSuccessMessage('transport-manager-application.resend-form.success');
        } else {
            $flashMessenger->addErrorMessage('transport-manager-application.resend-form.error');
        }
    }
}
