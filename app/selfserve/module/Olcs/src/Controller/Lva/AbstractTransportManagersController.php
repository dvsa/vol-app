<?php

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractTransportManagersController as CommonAbstractTmController;
use Common\Controller\Traits\GenericUpload;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\TransportManagerHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Transport Managers Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractTransportManagersController extends CommonAbstractTmController
{
    use GenericUpload;

    public const TYPE_OTHER_LICENCE = 'OtherLicences';
    public const TYPE_PREVIOUS_CONVICTION = 'PreviousConvictions';
    public const TYPE_PREVIOUS_LICENCE = 'PreviousLicences';
    public const TYPE_OTHER_EMPLOYMENT = 'OtherEmployments';
    public const TM_APPLICATION_RESEND_EMAIL = 'tm_app_resend_email';
    public const TM_APPLICATION_AMEND_EMAIL = 'tm_app_amend_email';

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

    protected TransportManagerHelperService $transportManagerHelper;
    protected FormHelperService $formHelper;
    protected FlashMessengerHelperService $flashMessengerHelper;
    protected FormServiceManager $formServiceManager;
    protected ScriptFactory $scriptFactory;
    protected QuerySender $querySender;
    protected QueryService $queryService;
    protected CommandService $commandService;
    protected AnnotationBuilder $transferAnnotationBuilder;
    protected FileUploadHelperService $uploadHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FormServiceManager $formServiceManager
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param ScriptFactory $scriptFactory
     * @param QueryService $queryService
     * @param CommandService $commandService
     * @param AnnotationBuilder $transferAnnotationBuilder
     * @param TransportManagerHelperService $transportManagerHelper
     * @param TranslationHelperService $translationHelper
     * @param $lvaAdapter
     * @param TableFactory $tableFactory
     * @param FileUploadHelperService $uploadHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FormServiceManager $formServiceManager,
        FlashMessengerHelperService $flashMessengerHelper,
        ScriptFactory $scriptFactory,
        QueryService $queryService,
        CommandService $commandService,
        AnnotationBuilder $transferAnnotationBuilder,
        TransportManagerHelperService $transportManagerHelper,
        protected TranslationHelperService $translationHelper,
        $lvaAdapter,
        protected TableFactory $tableFactory,
        FileUploadHelperService $uploadHelper
    ) {
        $this->uploadHelper = $uploadHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $flashMessengerHelper,
            $scriptFactory,
            $queryService,
            $commandService,
            $transferAnnotationBuilder,
            $transportManagerHelper,
            $lvaAdapter
        );
    }

    /**
     * Revert to editing the form
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function editAction()
    {
        // move status back to incomplete
        $tmaId = (int)$this->params('child_id');
        if ($this->updateTmaStatus($tmaId, RefData::TMA_STATUS_INCOMPLETE)) {
            return $this->redirect()->toRouteAjax("lva-{$this->lva}/transport_manager_details", [], [], true);
        } else {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
            return $this->redirect()->refresh();
        }
    }

    /**
     * Display details of the Transport Manager Application process
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function detailsAction()
    {
        $tmaId = (int)$this->params('child_id');
        $tma = $this->getTmaDetails($tmaId);
        return $this->callActionByStatus($tma);
    }

    /**
     * @param array $tma
     *
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    private function callActionByStatus($tma)
    {
        $isUserTm = $tma['isTmLoggedInUser'];
        switch ($tma['tmApplicationStatus']['id']) {
            case RefData::TMA_STATUS_POSTAL_APPLICATION:
                return $this->pagePostal($tma);

            case RefData::TMA_STATUS_DETAILS_SUBMITTED:
            case RefData::TMA_STATUS_DETAILS_CHECKED:
                $tma = $this->changeToCorrectTmaStatus(
                    $tma,
                    RefData::TMA_STATUS_INCOMPLETE
                );
                return $this->callActionByStatus($tma);
            case RefData::TMA_STATUS_INCOMPLETE:
                if ($isUserTm) {
                    return $this->page1Point1($tma);
                } else {
                    return $this->page1Point3($tma);
                }
            case RefData::TMA_STATUS_TM_SIGNED:
            case RefData::TMA_STATUS_OPERATOR_APPROVED:
                if ($isUserTm) {
                    return $this->page2Point1($tma);
                } else {
                    $tma = $this->changeToCorrectTmaStatus(
                        $tma,
                        RefData::TMA_STATUS_TM_SIGNED
                    );
                    return $this->page2Point2($tma);
                }
            case RefData::TMA_STATUS_OPERATOR_SIGNED:
                return $this->page3($tma, $isUserTm);
            case RefData::TMA_STATUS_RECEIVED:
                return $this->page4($tma);
            default:
                throw new \RuntimeException("Unexpected TMA status: {$tma['tmApplicationStatus']['id']}");
        }
    }

    /**
     * Details page, the big form for TM to input all details
     *
     * @param array $transportManagerApplicationData TM application data
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    protected function page1Point1(array $transportManagerApplicationData)
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        $postData = (array)$request->getPost();
        $formData = $this->formatFormData($transportManagerApplicationData, $postData);

        $form = $this->getDetailsForm($transportManagerApplicationData)->setData($formData);

        $flashMessenger = $this->flashMessengerHelper;
        $this->maybeSelectOptions($transportManagerApplicationData, $form);
        $formHelper = $this->formHelper;

        $hasProcessedAddressLookup = $formHelper->processAddressLookupForm($form, $request);

        $hasProcessedCertificateFiles = $this->processFiles(
            $form,
            'details->certificate',
            $this->processCertificateUpload(...),
            $this->deleteFile(...),
            $this->getCertificates(...)
        );

        $hasProcessedResponsibilitiesFiles = $this->processFiles(
            $form,
            'responsibilities->file',
            $this->processResponsibilityFileUpload(...),
            $this->deleteFile(...),
            $this->getResponsibilityFiles(...)
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
                $command = $this->transferAnnotationBuilder->createCommand(
                    Command\TransportManagerApplication\UpdateDetails::create(
                        [
                            'id' => $transportManagerApplicationData['id'],
                            'version' => $transportManagerApplicationData['version'],
                            'email' => $data['details']['emailAddress'],
                            'placeOfBirth' => $data['details']['birthPlace'],
                            'lgvAcquiredRightsReferenceNumber' => $data['details']['lgvAcquiredRightsReferenceNumber'] ?? null,
                            'hasUndertakenTraining' => $data['details']['hasUndertakenTraining'],
                            'homeAddress' => $data['homeAddress'],
                            'workAddress' => $data['workAddress'],
                            'tmType' => $data['responsibilities']['tmType'],
                            'isOwner' => $data['responsibilities']['isOwner'],
                            'hoursMon' => (float)$hoursOfWeek['hoursPerWeekContent']['hoursMon'],
                            'hoursTue' => (float)$hoursOfWeek['hoursPerWeekContent']['hoursTue'],
                            'hoursWed' => (float)$hoursOfWeek['hoursPerWeekContent']['hoursWed'],
                            'hoursThu' => (float)$hoursOfWeek['hoursPerWeekContent']['hoursThu'],
                            'hoursFri' => (float)$hoursOfWeek['hoursPerWeekContent']['hoursFri'],
                            'hoursSat' => (float)$hoursOfWeek['hoursPerWeekContent']['hoursSat'],
                            'hoursSun' => (float)$hoursOfWeek['hoursPerWeekContent']['hoursSun'],
                            'additionalInfo' => $data['responsibilities']['additionalInformation'],
                            'hasOtherLicences' => $data['responsibilities']['otherLicencesFieldset']['hasOtherLicences'],
                            'hasOtherEmployment' => $data['otherEmployments']['hasOtherEmployment'],
                            'hasConvictions' => $data['previousHistory']['hasConvictions'],
                            'hasPreviousLicences' => $data['previousHistory']['hasPreviousLicences'],
                            'submit' => ($submit) ? 'Y' : 'N',
                            'dob' => $data['details']['birthDate']
                        ]
                    )
                );
                /* @var $response \Common\Service\Cqrs\Response */
                $response = $this->commandService->send($command);
                if (!$response->isOk()) {
                    $acquiredRightsError = $this->getAcquiredRightsErrorIfExists($response);
                    if (!empty($acquiredRightsError)) {
                        $form->setMessages([
                            'details' => [
                                'lgvAcquiredRightsReferenceNumber' => $acquiredRightsError,
                            ],
                        ]);
                        return $this->renderWithForm($transportManagerApplicationData['application'], $form);
                    }
                    $flashMessenger->addErrorMessage('unknown-error');
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
                    $this->flashMessengerHelper
                        ->addSuccessMessage('lva-tm-details-save-success');

                    return $this->redirectTmToHome();
                }

                $this->updateTmaStatus(
                    $transportManagerApplicationData['id'],
                    RefData::TMA_STATUS_DETAILS_SUBMITTED
                );
                return $this->redirectToCheckAnswersPage($transportManagerApplicationData);
            }
        }

        return $this->renderWithForm($transportManagerApplicationData['application'], $form);
    }

    /**
     * Add other licence applications action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function addOtherLicenceApplicationsAction()
    {
        return $this->addOrEdit(self::TYPE_OTHER_LICENCE, 'add');
    }

    /**
     * Edit other licence applications action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function editOtherLicenceApplicationsAction()
    {
        $id = $this->params('grand_child_id');

        return $this->addOrEdit(self::TYPE_OTHER_LICENCE, 'edit', $id);
    }

    /**
     * Add previous conviction action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function addPreviousConvictionAction()
    {
        return $this->addOrEdit(self::TYPE_PREVIOUS_CONVICTION, 'add');
    }

    /**
     * Edit previous conviction action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function editPreviousConvictionAction()
    {
        $id = $this->params('grand_child_id');

        return $this->addOrEdit(self::TYPE_PREVIOUS_CONVICTION, 'edit', $id);
    }

    /**
     * Add previous licence action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function addPreviousLicenceAction()
    {
        return $this->addOrEdit(self::TYPE_PREVIOUS_LICENCE, 'add');
    }

    /**
     * Edit previous licence action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function editPreviousLicenceAction()
    {
        $id = $this->params('grand_child_id');

        return $this->addOrEdit(self::TYPE_PREVIOUS_LICENCE, 'edit', $id);
    }

    /**
     * Add employment action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function addEmploymentAction()
    {
        return $this->addOrEdit(
            self::TYPE_OTHER_EMPLOYMENT,
            'add',
            null,
            ['headerText' => 'lva.section.headerText.transport_managers-details-add-OtherEmployments']
        );
    }

    /**
     * Edit employment action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function editEmploymentAction()
    {
        $id = $this->params('grand_child_id');

        return $this->addOrEdit(self::TYPE_OTHER_EMPLOYMENT, 'edit', $id);
    }

    /**
     * Delete other licence applications action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function deleteOtherLicenceApplicationsAction()
    {
        return $this->genericDelete(self::TYPE_OTHER_LICENCE);
    }

    /**
     * Delete previous conviction action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function deletePreviousConvictionAction()
    {
        return $this->genericDelete(self::TYPE_PREVIOUS_CONVICTION);
    }

    /**
     * Delete previous licence action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function deletePreviousLicenceAction()
    {
        return $this->genericDelete(self::TYPE_PREVIOUS_LICENCE);
    }

    /**
     * Delete employment action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function deleteEmploymentAction()
    {
        return $this->genericDelete(self::TYPE_OTHER_EMPLOYMENT);
    }

    /**
     * Delete confirmation and processing for each sub-section of TM
     *
     * @param string $type (Contant used to lookup services)
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function genericDelete($type = null)
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $ids = explode(',', (string) $this->params('grand_child_id'));

            $commandClass = $this->deleteCommandMap[$type];
            $command = $this->transferAnnotationBuilder
                ->createCommand($commandClass::create(['ids' => $ids]));
            /* @var $response \Common\Service\Cqrs\Response */
            $response = $this->commandService->send($command);
            if ($response->isOk()) {
                $this->flashMessengerHelper->addSuccessMessage(
                    'transport_managers-details-' . $type . '-delete-success'
                );
            } else {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }

            return $this->backToDetails();
        }

        $form = $this->formHelper
            ->createFormWithRequest('GenericDeleteConfirmation', $request);

        $params = ['sectionText' => 'delete.confirmation.text'];

        return $this->render('delete-' . $type, $form, $params);
    }

    /**
     * Add or edit
     *
     * @param string $type      Type
     * @param string $mode      Mode
     * @param int    $id        Id
     * @param array  $variables Variables
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    protected function addOrEdit($type, $mode, $id = null, $variables = [])
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->backToDetails();
        }

        $request = $this->getRequest();

        $formHelper = $this->formHelper;

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

            $commandContainer = $this->transferAnnotationBuilder
                ->createCommand($command);
            /* @var $response \Common\Service\Cqrs\Response */
            $response = $this->commandService->send($commandContainer);

            if ($response->isOk()) {
                $this->flashMessengerHelper
                    ->addSuccessMessage('lva.section.title.transport_managers-details-' . $type . '-success');
            } else {
                $this->flashMessengerHelper
                    ->addErrorMessage('unknown-error');
                return $this->render('transport_managers-details-' . $mode . '-' . $type, $form);
            }

            if ($this->isButtonPressed('addAnother')) {
                return $this->redirect()->refresh();
            }

            return $this->backToDetails($type);
        }

        return $this->render('transport_managers-details-' . $mode . '-' . $type, $form, $variables);
    }

    /**
     * Is address form
     *
     * @param string $type Type
     *
     * @return bool
     */
    protected function isAddressForm($type)
    {
        return ($type === self::TYPE_OTHER_EMPLOYMENT);
    }

    /**
     * Get other licences create command
     *
     * @param array $data Data
     *
     * @return \Dvsa\Olcs\Transfer\Command\CommandInterface
     */
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

    /**
     * Get other licences update command
     *
     * @param array $data Data
     *
     * @return \Dvsa\Olcs\Transfer\Command\CommandInterface
     */
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

    /**
     * Get other employments create command
     *
     * @param array $data Data
     *
     * @return \Dvsa\Olcs\Transfer\Command\CommandInterface
     */
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

    /**
     * Get other employments update command
     *
     * @param array $data Data
     *
     * @return \Dvsa\Olcs\Transfer\Command\CommandInterface
     */
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

    /**
     * Get previous convictions create command
     *
     * @param array $data Data
     *
     * @return \Dvsa\Olcs\Transfer\Command\CommandInterface
     */
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

    /**
     * Get previous convictions update command
     *
     * @param array $data Data
     *
     * @return \Dvsa\Olcs\Transfer\Command\CommandInterface
     */
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

    /**
     * Get previous licences create command
     *
     * @param array $data Data
     *
     * @return \Dvsa\Olcs\Transfer\Command\CommandInterface
     */
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

    /**
     * Get previous licences update command
     *
     * @param array $data Data
     *
     * @return \Dvsa\Olcs\Transfer\Command\CommandInterface
     */
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

    /**
     * Get other licences data
     *
     * @param int $id id
     *
     * @return array
     */
    protected function getOtherLicencesData($id)
    {
        $query = $this->transferAnnotationBuilder
            ->createQuery(\Dvsa\Olcs\Transfer\Query\OtherLicence\OtherLicence::create(['id' => $id]));
        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->queryService->send($query);

        return ['data' => $response->getResult()];
    }

    /**
     * Get previous convictions data
     *
     * @param int $id id
     *
     * @return array
     */
    protected function getPreviousConvictionsData($id)
    {
        $query = $this->transferAnnotationBuilder
            ->createQuery(\Dvsa\Olcs\Transfer\Query\PreviousConviction\PreviousConviction::create(['id' => $id]));
        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->queryService->send($query);

        return ['tm-convictions-and-penalties-details' => $response->getResult()];
    }

    /**
     * Get previous licences data
     *
     * @param int $id id
     *
     * @return array
     */
    protected function getPreviousLicencesData($id)
    {
        $query = $this->transferAnnotationBuilder
            ->createQuery(\Dvsa\Olcs\Transfer\Query\OtherLicence\OtherLicence::create(['id' => $id]));
        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->queryService->send($query);

        return ['tm-previous-licences-details' => $response->getResult()];
    }

    /**
     * Get other employments data
     *
     * @param int $id id
     *
     * @return array
     */
    protected function getOtherEmploymentsData($id)
    {
        return $this->transportManagerHelper->getOtherEmploymentData($id);
    }

    /**
     * Handle the upload of transport manager certificates
     *
     * @param array $file File data
     */
    public function processCertificateUpload($file): bool
    {
        $data = $this->transportManagerHelper
            ->getCertificateFileData($this->tma['transportManager']['id'], $file);

        $additionalData = [
            'additionalCopy' => true,
            'additionalEntities' => ['application', 'licence'],
            'application' => $this->getIdentifier(),
            'licence' => $this->getLicenceId()
        ];
        $data = array_merge($data, $additionalData);
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
     * @param array $file File data
     */
    public function processResponsibilityFileUpload($file): bool
    {
        $data = $this->transportManagerHelper
            ->getResponsibilityFileData($this->tma['transportManager']['id']);

        $data['application'] = $this->getIdentifier();
        $data['licence'] = $this->getLicenceId();
        $data['description'] = $file['name'];

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
            if (
                $document['category']['id'] === \Common\Category::CATEGORY_TRANSPORT_MANAGER &&
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
            if (
                $document['category']['id'] === \Common\Category::CATEGORY_TRANSPORT_MANAGER &&
                $document['subCategory']['id'] ===
                \Common\Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL &&
                $document['application']['id'] === $this->tma['application']['id']
            ) {
                $documents[] = $document;
            }
        }

        return $documents;
    }

    /**
     * Format form data
     *
     * @param array $data     Data
     * @param array $postData POST data
     *
     * @return array
     */
    protected function formatFormData($data, $postData)
    {
        $contactDetails = $data['transportManager']['homeCd'];
        $person = $contactDetails['person'];

        if (!empty($postData)) {
            $formData = $postData;
        } else {
            $formData = [
                'details' => [
                    'emailAddress' => $contactDetails['emailAddress'],
                    'hasUndertakenTraining' => $data['hasUndertakenTraining'],
                    'birthPlace' => $person['birthPlace']
                ],
                'responsibilities' => [
                    'tmType' => $data['tmType']['id'],
                    'isOwner' => $data['isOwner'],
                    'additionalInformation' => $data['additionalInformation'],
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
                    ],
                    'otherLicencesFieldset' => [
                        'hasOtherLicences' => $this->formatYesNo($data['hasOtherLicences'])
                    ],
                ],
                'otherEmployments' => [
                    'hasOtherEmployment' => $this->formatYesNo($data['hasOtherEmployment'])
                ],
                'previousHistory' => [
                    'hasConvictions' => $this->formatYesNo($data['hasConvictions']),
                    'hasPreviousLicences' => $this->formatYesNo($data['hasPreviousLicences'])
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

    private function formatYesNo($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value ? 'Y' : 'N';
    }

    /**
     * Get details form
     *
     * @param array $tma TM application data
     *
     * @return \Common\Form\Form
     */
    protected function getDetailsForm(array $tma)
    {
        $form = $this->formHelper->createForm('Lva\TransportManagerDetails');

        $this->transportManagerHelper->removeTmTypeBothOption($form->get('responsibilities')->get('tmType'));
        $this->transportManagerHelper->populateOtherLicencesTable(
            $form->get('responsibilities')->get('otherLicencesFieldset')->get('otherLicences'),
            $this->getOtherLicencesTable($tma['otherLicences'])
        );

        $this->transportManagerHelper->alterPreviousHistoryFieldsetTm(
            $form->get('previousHistory'),
            $tma['transportManager']
        );

        if ($tma['application']['niFlag'] === 'Y') {
            $form->get('previousHistory')->get('convictions')->get('table')->getTable()
                ->setEmptyMessage('transport-manager.convictionsandpenalties.table.empty.ni');
        }

        $this->transportManagerHelper->prepareOtherEmploymentTableTm(
            $form->get('otherEmployments')->get('otherEmployment'),
            $tma['transportManager']
        );

        $this->formHelper->remove($form, 'responsibilities->tmApplicationStatus');

        if ($tma['application']['vehicleType']['id'] === RefData::APP_VEHICLE_TYPE_LGV) {
            // LGV only
            $detailsField = $form->get('details');

            $detailsField->get('certificate')->setLabel('lva-tm-details-details-certificate-lgv-only');

            if (!empty($tma['lgvAcquiredRightsReferenceNumber'])) {
                // LGV Acquired Rights ref number already set
                $lgvAcquiredRightsReferenceNumberField = $detailsField->get('lgvAcquiredRightsReferenceNumber');

                // set value
                $lgvAcquiredRightsReferenceNumberField ->setValue($tma['lgvAcquiredRightsReferenceNumber']);

                // add padlock
                $this->formHelper->lockElement(
                    $lgvAcquiredRightsReferenceNumberField,
                    'lva-tm-details-details-lgvAcquiredRightsReferenceNumber-locked'
                );

                // disable element
                $this->formHelper->disableElement($form, 'details->lgvAcquiredRightsReferenceNumber');
            }
        } else {
            $this->formHelper->remove($form, 'details->certificateHtml');
            $this->formHelper->remove($form, 'details->lgvAcquiredRightsHtml');
            $this->formHelper->remove($form, 'details->lgvAcquiredRightsReferenceNumber');
        }

        /** @var \Laminas\Form\Fieldset $formActions */
        $formActions = $form->get('form-actions');
        $formActions->get('submit')->setLabel('lva.external.save_and_continue.button');

        /** @var \Laminas\Form\Element $saveButton */
        $saveButton = $formActions->get('save');
        $saveButton->setLabel('lva.external.save_and_return_to_tm.link');
        $saveButton->removeAttribute('class');
        $saveButton->setAttribute('class', 'govuk-button govuk-button--secondary');

        $this->formHelper->remove($form, 'form-actions->cancel');

        return $form;
    }

    /**
     * Get other licences table
     *
     * @param array $otherLicences Other licences
     *
     * @return \Common\Service\Table\TableBuilder
     */
    protected function getOtherLicencesTable($otherLicences)
    {
        return $this->tableFactory->prepareTable('tm.otherlicences-applications', $otherLicences);
    }

    /**
     * Need to override this, as the TM details page is special
     *
     * @param int $lvaId LVA id
     *
     * @return \Common\Service\Cqrs\Response|\Laminas\Http\Response|null
     */
    #[\Override]
    protected function checkForRedirect($lvaId)
    {
        if (!$this->isButtonPressed('cancel')) {
            return null;
        }

        $action = $this->params('action');
        $childId = $this->params('child_id');
        $normalRedirect = ['details', 'index', 'addTm', 'add'];
        if ($childId !== null && !in_array($action, $normalRedirect)) {
            return $this->backToDetails();
        }

        return $this->handleCancelRedirect($lvaId);
    }

    /**
     * Redirect to the details page
     *
     * @param string $which Which section has just been completed
     *
     * @return \Laminas\Http\Response
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

    /**
     * Get form tables
     *
     * @param array $postData POST data
     *
     * @return array
     */
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
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function editDetailsAction()
    {
        $tmApplicationId = (int)$this->params('child_id');
        $tma = $this->getTmaDetails($tmApplicationId);

        $preGrantedStatuses = [
            RefData::APPLICATION_STATUS_NOT_SUBMITTED,
            RefData::APPLICATION_STATUS_UNDER_CONSIDERATION,
        ];
        if (!in_array($tma['application']['status']['id'], $preGrantedStatuses)) {
            return new \Laminas\View\Model\ViewModel(['translateMessage' => 'markup-tma-edit-error']);
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
     *
     * @return \Laminas\Http\Response
     */
    protected function redirectTmToHome()
    {
        if (
            $this->isGranted(RefData::PERMISSION_SELFSERVE_TM_DASHBOARD) &&
            !$this->isGranted(RefData::PERMISSION_SELFSERVE_LVA)
        ) {
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

    /**
     * Get TM application details
     *
     * @param int $tmaId TM application id
     *
     * @return array
     */
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
     * @param int    $tmaId     TM application id
     * @param string $newStatus New status
     * @param int    $version   Version
     *
     * @return bool
     */
    protected function updateTmaStatus($tmaId, $newStatus, $version = null)
    {
        $command = $this->transferAnnotationBuilder
            ->createCommand(
                Command\TransportManagerApplication\UpdateStatus::create(
                    ['id' => $tmaId, 'status' => $newStatus, 'version' => $version]
                )
            );
        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->commandService->send($command);

        return $response->isOk();
    }

    /**
     * Incomplete, resend email to TM
     *
     * @param array $tma TM application
     *
     * @return \Laminas\View\Model\ViewModel
     */
    private function page1Point3(array $tma)
    {

        $translationHelper = $this->translationHelper;

        $tmaEmailAddress = $tma['transportManager']['homeCd']['emailAddress'] ?? null;

        $params = [
            'content' => $translationHelper->translateReplace('markup-tma-ab1-3', [$tmaEmailAddress])
        ];

        $formHelper = $this->formHelper;
        $form = $formHelper->createForm('TransportManagerApplicationResend');
        /* @var $form \Common\Form\Form */
        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $this->sendTmApplicationEmail(self::TM_APPLICATION_RESEND_EMAIL);
            }
        }

        return $this->renderTmAction('transport-manager-application.details-not-submitted', $form, $tma, $params);
    }

    /**
     * TM signed, TM view
     *
     * @param array $tma TM application
     *
     * @return \Laminas\View\Model\ViewModel
     */
    private function page2Point1(array $tma)
    {
        $translationHelper = $this->translationHelper;
        $params = [
            'content' => $translationHelper->translateReplace('markup-tma-a2-1', [$this->getEditTmUrl()]),
        ];

        return $this->renderTmAction('transport-manager-application.awaiting-operator-approval', null, $tma, $params);
    }

    /**
     * TM signed, Operator view
     *
     * @param array $tma TM application
     *
     * @return \Laminas\View\Model\ViewModel | \Laminas\Http\Response
     */
    private function page2Point2(array $tma)
    {
        $translationHelper = $this->translationHelper;
        $params = [
            'content' => $translationHelper->translateReplace(
                'markup-tma-a2-2',
                [$this->getViewTmUrl(), $this->url()->fromRoute(null, [], [], true)]
            ),
            'bottomContent' => $translationHelper->translate('TMA_WRONG_DETAILS'),
            'backLink' => null,
        ];

        $formHelper = $this->formHelper;
        $form = $formHelper->createForm('GenericConfirmation');
        /* @var $form \Common\Form\Form */
        $formHelper->setFormActionFromRequest($form, $this->getRequest());
        $form->setSubmitLabel('approve-details');
        $form->removeCancel();

        $resendForm = $formHelper->createForm('TransportManagerApplicationResend');
        /* @var $form \Common\Form\Form */
        $formHelper->setFormActionFromRequest($resendForm, $this->getRequest());

        $params['resendForm'] = $resendForm;

        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getPost('formName') === 'transport-manager-application-resend') {
                $resendForm->setData($this->getRequest()->getPost());
                if ($resendForm->isValid()) {
                    $this->updateTmaStatusAndSendAmendTmApplicationEmail();
                    return $this->redirectToTransportManagersPage();
                }
            } else {
                $tma = $this->changeToCorrectTmaStatus(
                    $tma,
                    RefData::TMA_STATUS_OPERATOR_APPROVED
                );
                return $this->redirectToOperatorDeclarationPage($tma);
            }
        }

        return $this->renderTmAction('transport-manager-application.review-and-submit', $form, $tma, $params);
    }

    /**
     * Operator signed
     *
     * @param array $tma      TM application
     * @param bool  $isUserTm Is user TM
     *
     * @return \Laminas\View\Model\ViewModel
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

        $translationHelper = $this->translationHelper;
        $params['content'] = $translationHelper->translateReplace($template, [$this->getViewTmUrl()]);

        $this->flashMessenger()->addSuccessMessage('operator-approve-message');
        return $this->renderTmAction('transport-manager-application.print-sign', null, $tma, $params);
    }

    /**
     * Received
     *
     * @param array $tma TM application
     *
     * @return \Laminas\View\Model\ViewModel
     */
    private function page4(array $tma)
    {
        $translationHelper = $this->translationHelper;
        $params['content'] = $translationHelper->translateReplace('markup-tma-ab-4', [$this->getViewTmUrl()]);

        return $this->renderTmAction('transport-manager-application.details-received', null, $tma, $params);
    }

    /**
     * Postal application
     *
     * @param array $tma TM application
     *
     * @return \Laminas\View\Model\ViewModel
     */
    private function pagePostal(array $tma)
    {
        $translationHelper = $this->translationHelper;
        $params = [
            'content' => $translationHelper->translate('markup-tma-c-0'),
            'backLink' => null,
        ];

        return $this->renderTmAction('transport-manager-application.postal', null, $tma, $params);
    }

    /**
     * Render the Transport manager application process pages
     *
     * @param string            $title  Title
     * @param \Common\Form\Form $form   Form
     * @param array             $tma    TM application
     * @param array             $params Params
     *
     * @return \Laminas\View\Model\ViewModel
     */
    private function renderTmAction($title, $form, $tma, $params)
    {
        $defaultParams = [
            'tmFullName' => trim(
                $tma['transportManager']['homeCd']['person']['forename'] . ' '
                . $tma['transportManager']['homeCd']['person']['familyName']
            ),
            'backLink' => $this->getBacklink(),
            'backText' => $this->isTransportManagerRole() ? 'transport-manager-back-text-tm' :
                'transport-manager-back-text-admin',
        ];

        $params = array_merge($defaultParams, $params);

        $layout = $this->render($title, $form, $params);
        /* @var $layout \Laminas\View\Model\ViewModel */

        $content = $layout->getChildrenByCaptureTo('content')[0];
        $content->setTemplate('pages/lva-tm-details-action');

        return $layout;
    }

    /**
     * Get the URL to review wth TMA
     *
     * @return string
     */
    private function getViewTmUrl()
    {
        $tmaId = (int)$this->params('child_id');
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
     * @param array $tma TM application
     *
     * @return bool
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
     * Send Tm application emails
     *
     *
     * @return void
     */
    private function sendTmApplicationEmail(string $resendOrAmend): void
    {
        $tmaId = (int)$this->params('child_id');

        $dtoData = [
            'id' => $tmaId,
        ];

        if ($resendOrAmend === self::TM_APPLICATION_AMEND_EMAIL) {
            $response = $this->handleCommand(
                Command\TransportManagerApplication\SendAmendTmApplication::create($dtoData)
            );
        }

        if ($resendOrAmend === self::TM_APPLICATION_RESEND_EMAIL) {
            $response = $this->handleCommand(
                Command\TransportManagerApplication\SendTmApplication::create($dtoData)
            );
        }

        $flashMessenger = $this->flashMessengerHelper;
        if ($response->isOk()) {
            $flashMessenger->addSuccessMessage('transport-manager-application.resend-form.success');
        } else {
            $flashMessenger->addErrorMessage('transport-manager-application.resend-form.error');
        }
    }

    /**
     * @return \Laminas\Http\Response
     */
    private function redirectToOperatorDeclarationPage(array $tma): \Laminas\Http\Response
    {
        return $this->redirect()->toRoute(
            'lva-' . $this->lva . '/transport_manager_operator_declaration',
            [
                'child_id' => $tma['id'],
                'application' => $tma['application']['id'],
                'action' => 'index'
            ]
        );
    }

    /**
     * @param object $form
     * @return void
     */
    protected function maybeSelectOptions(array $tma, $form): void
    {
        $hasOtherLicences = $form->get('responsibilities')->get('otherLicencesFieldset')->get('hasOtherLicences')->getValue();
        if (!empty($tma['otherLicences']) && $hasOtherLicences === null) {
            $form->get('responsibilities')->get('otherLicencesFieldset')->get('hasOtherLicences')->setValue('Y');
        }
        $hasOtherEmployment = $form->get('otherEmployments')->get('hasOtherEmployment')->getValue();
        if (!empty($tma['transportManager']['employments']) && $hasOtherEmployment === null) {
            $form->get('otherEmployments')->get('hasOtherEmployment')->setValue('Y');
        }
        $hasConvictions = $form->get('previousHistory')->get('hasConvictions')->getValue();
        if (!empty($tma['transportManager']['previousConvictions']) && $hasConvictions === null) {
            $form->get('previousHistory')->get('hasConvictions')->setValue('Y');
        }
        $hasPreviousLicences = $form->get('previousHistory')->get('hasPreviousLicences')->getValue();
        if (!empty($tma['transportManager']['otherLicences']) && $hasPreviousLicences === null) {
            $form->get('previousHistory')->get('hasPreviousLicences')->setValue('Y');
        }
    }

    /**
     * Update Tma status and send amend tm applcation email
     *
     * @return void
     */
    private function updateTmaStatusAndSendAmendTmApplicationEmail(): void
    {
        $tmaId = (int)$this->params('child_id');
        if ($this->updateTmaStatus($tmaId, RefData::TMA_STATUS_INCOMPLETE)) {
            $this->sendTmApplicationEmail(self::TM_APPLICATION_AMEND_EMAIL);
        } else {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }
    }

    private function redirectToTransportManagersPage(): \Laminas\Http\Response
    {
        return $this->redirect()->toRoute(
            "lva-{$this->lva}/transport_managers",
            ['application' => $this->getIdentifier()]
        );
    }

    /**
     * @return \Laminas\Http\Response
     */
    protected function redirectToCheckAnswersPage(array $tma): \Laminas\Http\Response
    {
        return $this->redirect()->toRoute(
            'lva-' . $this->lva . '/transport_manager_check_answer',
            [
                'action' => 'index',
                'child_id' => $tma['id'],
                'application' => (int)$this->params('application')
            ]
        );
    }

    /**
     * Change TMA status to correct status
     *
     * @param array  $tma
     * @param string $status
     *
     * @return mixed
     */
    private function changeToCorrectTmaStatus($tma, $status)
    {
        if ($tma['tmApplicationStatus']['id'] === $status) {
            return $tma;
        }

        if ($this->updateTmaStatus($tma['id'], $status) === false) {
            throw new \RuntimeException('updateTmaStatus failed');
        }

        $tma['tmApplicationStatus']['id'] = $status;
        return $tma;
    }

    /**
     * @deprecated To be removed when LGV Acquired Rights is no longer allowed.
     * @return false|mixed
     */
    private function getAcquiredRightsErrorIfExists(\Common\Service\Cqrs\Response $response)
    {
        try {
            $errorArray = json_decode($response->getBody(), true);
        } catch (\InvalidArgumentException) {
            // do nothing, not valid JSON.
            return false;
        }
        $lgvAcquiredRightsError = $errorArray['messages']['lgvAcquiredRightsReferenceNumber'] ?? null;
        if (empty($lgvAcquiredRightsError)) {
            return false;
        }
        return $lgvAcquiredRightsError;
    }

    /**
     * @param $application
     * @return \Common\View\Model\Section
     */
    protected function renderWithForm($application, \Common\Form\Form $form)
    {
        $translationHelper = $this->translationHelper;

        $tmHeaderData = $application;
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

        $this->scriptFactory
            ->loadFiles(['lva-crud', 'tm-previous-history', 'tm-other-employment', 'tm-details']);

        $layout = $this->render('transport_managers-details', $form, $params);

        $content = $layout->getChildrenByCaptureTo('content')[0];
        $content->setTemplate('pages/lva-tm-details');

        return $layout;
    }
}
