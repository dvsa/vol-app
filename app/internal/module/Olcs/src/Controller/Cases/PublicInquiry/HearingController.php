<?php

namespace Olcs\Controller\Cases\PublicInquiry;

use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;
use Zend\View\Model\ViewModel;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Data\Mapper\PiHearing as PiHearingMapper;
use Olcs\Form\Model\Form\PublicInquiryHearing as HearingForm;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\CreateHearing as CreateCmd;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\UpdateHearing as UpdateCmd;
use Dvsa\Olcs\Transfer\Query\Cases\Pi\Hearing as PiHearingDto;
use Dvsa\Olcs\Transfer\Query\Cases\Pi\HearingList as PiHearingListDto;
use Dvsa\Olcs\Transfer\Query\Cases\Pi as PiDto;
use Olcs\Mvc\Controller\ParameterProvider\PreviousPiHearingData;

/**
 * Class HearingController
 * @package Olcs\Controller\Cases\PublicInquiry
 */
class HearingController extends AbstractInternalController implements
    CaseControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
{
    use ControllerTraits\GenerateActionTrait;

    protected $listVars = ['pi'];
    protected $navigationId = 'case_hearings_appeals_public_inquiry';
    protected $formClass = HearingForm::class;
    protected $tableName = 'piHearing';
    protected $listDto = PiHearingListDto::class;
    protected $itemDto = PiHearingDto::class;
    protected $createCommand = CreateCmd::class;
    protected $updateCommand = UpdateCmd::class;
    protected $mapperClass = PiHearingMapper::class;

    protected $redirectConfig = [
        'add' => [
            'route' => 'case_pi',
            'action' => 'index'
        ],
        'edit' => [
            'route' => 'case_pi',
            'action' => 'index'
        ]
    ];

    protected $inlineScripts = [
        'addAction' => ['forms/pi-hearing', 'shared/definition'],
        'editAction' => ['forms/pi-hearing', 'shared/definition'],
        'indexAction' => ['table-actions']
    ];

    public function getPageInnerLayout()
    {
        return 'layout/case-details-subsection';
    }

    public function getPageLayout()
    {
        return 'layout/case-section';
    }

    /**
     * @var int $licenceId cache of licence id for a given case
     */
    protected $licenceId;

    public function addAction()
    {
        $pi = $this->getPi();
        $slaData = ['agreedDate' => $pi['agreedDate']];
        $this->getServiceLocator()->get('Common\Service\Data\Sla')->setContext('pi_hearing', $slaData);

        return $this->add(
            $this->formClass,
            new PreviousPiHearingData($pi),
            $this->createCommand,
            $this->mapperClass,
            $this->editViewTemplate
        );
    }

    public function editAction()
    {
        $data = $this->getHearing();
        $data['agreedDate'] = $data['pi']['agreedDate']; //used by sla
        $this->getServiceLocator()->get('Common\Service\Data\Sla')->setContext('pi_hearing', $data);

        return parent::editAction();
    }

    /**
     * Gets Pi information
     *
     * @return array|mixed
     */
    private function getHearing()
    {
        $params = ['id' => $this->params()->fromRoute('id')];
        $response = $this->handleQuery(PiHearingDto::create($params));

        if ($response->isNotFound()) {
            $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        return $response->getResult();
    }

    /**
     * Gets Pi information
     *
     * @return array|mixed
     */
    private function getPi()
    {
        $params = ['id' => $this->params()->fromRoute('case')];
        $response = $this->handleQuery(PiDto::create($params));

        if ($response->isNotFound()) {
            $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        return $response->getResult();
    }

    /**
     * Alter form for TM cases, set pubType and trafficAreas to be visible for publishing
     *
     * @param \Common\Controller\Form $form
     * @return \Common\Controller\Form
     */
    public function alterFormForAdd($form)
    {
        $data = $this->getPi();

        //only need to specify a pub type and traffic area if it's a tm case
        if (!($data['isTm'])) {
            $form->get('fields')->remove('pubType');
            $form->get('fields')->remove('trafficAreas');
        }

        return $form;
    }

    /**
     * Alter form for TM cases, set pubType and trafficAreas to be visible for publishing
     *
     * @param \Common\Controller\Form $form
     * @return \Common\Controller\Form
     */
    public function alterFormForEdit($form)
    {
        $data = $this->getHearing();

        // set the label to republish if *any* publication has NOT been printed
        if (!empty($data['pi']['publicationLinks'])) {
            foreach ($data['pi']['publicationLinks'] as $pl) {
                if (isset($pl['publication']) && $pl['publication']['pubStatus']['id'] != 'pub_s_printed') {
                    $form->get('form-actions')->get('publish')->setLabel('Republish');
                    break;
                }
            }
        }

        //only need to specify a pub type and traffic area if it's a tm case
        if (!($data['isTm'])) {
            $form->get('fields')->remove('pubType');
            $form->get('fields')->remove('trafficAreas');
        }

        return $form;
    }

    /**
     * Route for document generate action redirects
     * @see Olcs\Controller\Traits\GenerateActionTrait
     * @return string
     */
    protected function getDocumentGenerateRoute()
    {
        return 'case_licence_docs_attachments/entity/generate';
    }

    /**
     * Route params for document generate action redirects
     * @see Olcs\Controller\Traits\GenerateActionTrait
     * @return array
     */
    protected function getDocumentGenerateRouteParams()
    {
        return [
            'case' => $this->getFromRoute('case'),
            'licence' => $this->getLicenceIdForCase(),
            'entityType' => 'hearing',
            'entityId' => $this->getFromRoute('id')
        ];
    }

    /**
     * Gets licence id from route or backend, caching it in member variable
     */
    protected function getLicenceIdForCase()
    {
        if (is_null($this->licenceId)) {
            $this->licenceId = $this->getQueryOrRouteParam('licence');
            if (empty($this->licenceId)) {
                $case = $this->getCase();
                $this->licenceId = $case['licence']['id'];
            }
        }
        return $this->licenceId;
    }
}
