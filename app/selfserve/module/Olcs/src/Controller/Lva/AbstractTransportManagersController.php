<?php

/**
 * Abstract Transport Managers Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractTransportManagersController as CommonAbstractTmController;
use Common\Controller\Traits\GenericUpload;
use Common\Controller\Lva\Traits\CrudTableTrait;

/**
 * Abstract Transport Managers Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractTransportManagersController extends CommonAbstractTmController
{
    use GenericUpload,
        CrudTableTrait;

    /**
     * Store the tmId
     */
    protected $tmId;

    protected $deleteWhich;

    const TYPE_OTHER_LICENCE = 'OtherLicences';
    const TYPE_PREVIOUS_CONVICTION = 'PreviousConvictions';
    const TYPE_PREVIOUS_LICENCE = 'PreviousLicences';
    const TYPE_OTHER_EMPLOYMENT = 'OtherEmployments';

    protected $formMap = [
        self::TYPE_OTHER_LICENCE => 'Lva\TmOtherLicence',
        self::TYPE_PREVIOUS_CONVICTION => 'TmConvictionsAndPenalties',
        self::TYPE_PREVIOUS_LICENCE => 'TmPreviousLicences',
        self::TYPE_OTHER_EMPLOYMENT => 'TmEmployment',
    ];

    protected $saveBusinessServiceMap = [
        self::TYPE_OTHER_LICENCE => 'Lva\OtherLicence',
        self::TYPE_PREVIOUS_CONVICTION => 'Lva\PreviousConviction',
        self::TYPE_PREVIOUS_LICENCE => 'Lva\OtherLicence',
        self::TYPE_OTHER_EMPLOYMENT => 'TmEmployment',
    ];

    protected $deleteBusinessServiceMap = [
        self::TYPE_OTHER_LICENCE => 'Lva\DeleteOtherLicence',
        self::TYPE_PREVIOUS_CONVICTION => 'Lva\DeletePreviousConviction',
        self::TYPE_PREVIOUS_LICENCE => 'Lva\DeleteOtherLicence',
        self::TYPE_OTHER_EMPLOYMENT => 'Lva\DeleteOtherEmployment',
    ];

    /**
     * Details page
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function detailsAction()
    {
        $request = $this->getRequest();
        $childId = $this->params('child_id');

        $transportManagerApplicationData = $this->getServiceLocator()->get('Entity\TransportManagerApplication')
            ->getTransportManagerDetails($childId);

        $this->tmId = $transportManagerApplicationData['transportManager']['id'];

        $postData = (array)$request->getPost();
        $formData = $this->formatFormData($transportManagerApplicationData, $postData);

        $form = $this->getDetailsForm($transportManagerApplicationData['application']['id'])->setData($formData);

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

                $tm = $transportManagerApplicationData['transportManager'];
                $contactDetails = $tm['homeCd'];
                $person = $contactDetails['person'];

                $params = [
                    'submit' => $submit,
                    'transportManagerApplication' => [
                        'id' => $childId,
                        'version' => $transportManagerApplicationData['version']
                    ],
                    'transportManager' => [
                        'id' => $this->tmId,
                        'version' => $tm['version']
                    ],
                    'contactDetails' => [
                        'id' => $contactDetails['id'],
                        'version' => $contactDetails['version']
                    ],
                    'workContactDetails' => [
                        'id' => isset($tm['workCd']['id']) ? $tm['workCd']['id'] : null,
                        'version' => isset($tm['workCd']['version']) ? $tm['workCd']['version'] : null,
                    ],
                    'person' => [
                        'id' => $person['id'],
                        'version' => $person['version']
                    ],
                    'data' => $data
                ];

                $this->getServiceLocator()->get('BusinessServiceManager')
                    ->get('Lva\TransportManagerDetails')
                    ->process($params);

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

                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addSuccessMessage('lva-tm-details-' . ($submit ? 'submit' : 'save') . '-success');

                return $this->redirect()->refresh();
            }
        }

        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');

        $tmHeaderData = $this->getServiceLocator()->get('Entity\Application')->getTmHeaderData($this->getIdentifier());

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
        return $this->deleteAction(self::TYPE_OTHER_LICENCE);
    }

    public function deletePreviousConvictionAction()
    {
        return $this->deleteAction(self::TYPE_PREVIOUS_CONVICTION);
    }

    public function deletePreviousLicenceAction()
    {
        return $this->deleteAction(self::TYPE_PREVIOUS_LICENCE);
    }

    public function deleteEmploymentAction()
    {
        return $this->deleteAction(self::TYPE_OTHER_EMPLOYMENT);
    }

    /**
     * Delete confirmation and processing for each sub-section of TM
     *
     * @param string $type (Contant used to lookup services)
     * @return mixed
     */
    public function deleteAction($type = null)
    {
        $request = $this->getRequest();

        if ($request->isPost()) {

            $ids = explode(',', $this->params('grand_child_id'));

            $this->getServiceLocator()->get('BusinessServiceManager')
                ->get($this->deleteBusinessServiceMap[$type])
                ->process(['ids' => $ids]);

            $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage(
                'transport_managers-details-' . $type . '-delete-success'
            );

            return $this->backToDetails();
        }

        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest('GenericDeleteConfirmation', $request);

        $params = ['sectionText' => $this->getDeleteMessage()];

        return $this->render($this->getDeleteTitle(), $form, $params);
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

            $params = $this->{'get' . $type . 'Params'}($data);

            $this->getServiceLocator()->get('BusinessServiceManager')
                ->get($this->saveBusinessServiceMap[$type])
                ->process($params);

            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addSuccessMessage('lva.section.title.transport_managers-details-' . $type . '-success');

            if ($this->isButtonPressed('addAnother')) {
                return $this->redirect()->refresh();
            }

            return $this->backToDetails();
        }

        return $this->render('transport_managers-details-' . $mode . '-' . $type, $form);
    }

    protected function isAddressForm($type)
    {
        if ($type === self::TYPE_OTHER_EMPLOYMENT) {
            return true;
        }
    }

    protected function getOtherLicencesParams($data)
    {
        $data['data']['transportManagerApplication'] = $this->params('child_id');

        return [
            'data' => $data['data']
        ];
    }

    protected function getPreviousConvictionsParams($data)
    {
        $tmId = $this->getServiceLocator()->get('Entity\TransportManagerApplication')
            ->getTransportManagerId($this->params('child_id'));

        $data['tm-convictions-and-penalties-details']['transportManager'] = $tmId;

        return ['data' => $data['tm-convictions-and-penalties-details']];
    }

    protected function getPreviousLicencesParams($data)
    {
        $tmId = $this->getServiceLocator()->get('Entity\TransportManagerApplication')
            ->getTransportManagerId($this->params('child_id'));

        $data['tm-previous-licences-details']['transportManager'] = $tmId;

        return ['data' => $data['tm-previous-licences-details']];
    }

    protected function getOtherEmploymentsParams($data)
    {
        $tmId = $this->getServiceLocator()->get('Entity\TransportManagerApplication')
            ->getTransportManagerId($this->params('child_id'));

        $employment = $data['tm-employment-details'];
        $employment['transportManager'] = $tmId;
        $employment['employerName'] = $data['tm-employer-name-details']['employerName'];

        return [
            'address' => $data['address'],
            'data' => $employment
        ];
    }

    protected function getOtherLicencesData($id)
    {
        return ['data' => $this->getServiceLocator()->get('Entity\OtherLicence')->getById($id)];
    }

    protected function getPreviousConvictionsData($id)
    {
        $data = $this->getServiceLocator()->get('Entity\PreviousConviction')->getById($id);

        return ['tm-convictions-and-penalties-details' => $data];
    }

    protected function getPreviousLicencesData($id)
    {
        $data = $this->getServiceLocator()->get('Entity\OtherLicence')->getById($id);

        return ['tm-previous-licences-details' => $data];
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
            ->getCertificateFileData($this->tmId, $file);

        return $this->uploadFile($file, $data);
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
            ->getResponsibilityFileData($this->tmId, $file);

        $data['application'] = $this->getIdentifier();
        $data['licence'] = $this->getLicenceId();

        return $this->uploadFile($file, $data);
    }

    /**
     * Get transport manager certificates
     *
     * @return array
     */
    public function getCertificates()
    {
        return $this->getServiceLocator()->get('Helper\TransportManager')->getCertificateFiles($this->tmId);
    }

    /**
     * Get transport manager certificates
     *
     * @return array
     */
    public function getResponsibilityFiles()
    {
        return $this->getServiceLocator()->get('Helper\TransportManager')
            ->getResponsibilityFiles($this->tmId, $this->getIdentifier());
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
        }

        $formData['details']['name'] = $person['forename'] . ' ' . $person['familyName'];
        $formData['details']['birthDate'] = date('d/m/Y', strtotime($person['birthDate']));

        return $formData;
    }

    protected function getDetailsForm($applicationId)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $tmHelper = $this->getServiceLocator()->get('Helper\TransportManager');

        $form = $formHelper->createForm('Lva\TransportManagerDetails');

        $ocOptions = $this->getServiceLocator()->get('Entity\ApplicationOperatingCentre')
            ->getForSelect($applicationId);

        $tmHelper->alterResponsibilitiesFieldset(
            $form->get('responsibilities'),
            $ocOptions,
            $this->getOtherLicencesTable()
        );

        $tmHelper->alterPreviousHistoryFieldset($form->get('previousHistory'), $this->tmId);

        $tmHelper->prepareOtherEmploymentTable($form->get('otherEmployment'), $this->tmId);

        $formHelper->remove($form, 'responsibilities->tmApplicationStatus');

        $licenceType = $this->getServiceLocator()->get('Entity\Application')->getTypeOfLicenceData($applicationId);

        $niOrGb = ($licenceType['niFlag'] === 'Y' ? 'ni' : 'gb');

        $form->get('declarations')->get('internal')->setValue(
            'markup-tm-declaration-' . $niOrGb . '-internal'
        );

        $form->get('declarations')->get('external')->setValue(
            'markup-tm-declaration-' . $niOrGb . '-external'
        );

        $form->get('declarations')->get('confirmation')->setLabel(
            'markup-tm-declaration-' . $niOrGb . '-confirmation'
        );

        return $form;
    }

    protected function getOtherLicencesTable()
    {
        $id = $this->params('child_id');

        $data = $this->getServiceLocator()->get('Entity\OtherLicence')->getByTmApplicationId($id);

        return $this->getServiceLocator()->get('Table')->prepareTable('tm.otherlicences-applications', $data);
    }

    /**
     * Need to override this, as the TM detials page is special
     */
    protected function checkForRedirect($lvaId)
    {
        if ($this->isButtonPressed('cancel')) {
            // If we are on a sub-section, we need to go back to the section
            if ($this->params('action') !== 'details') {
                return $this->backToDetails();
            }

            return $this->handleCancelRedirect($lvaId);
        }
    }

    protected function backToDetails()
    {
        return $this->redirect()->toRouteAjax('lva-' . $this->lva . '/transport_manager_details', [], [], true);
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
}
