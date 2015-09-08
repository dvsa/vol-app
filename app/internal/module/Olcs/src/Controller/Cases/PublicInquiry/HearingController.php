<?php

namespace Olcs\Controller\Cases\PublicInquiry;

use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Controller\AbstractInternalController;
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
use Olcs\Mvc\Controller\ParameterProvider\GenericList;

/**
 * Class HearingController
 * @package Olcs\Controller\Cases\PublicInquiry
 */
class HearingController extends AbstractInternalController implements
    CaseControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
{
    const MSG_CLOSED_PI = 'The Pi has already been closed';

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
        ],
        'generate' => [
            'route' => 'case_pi',
            'action' => 'index'
        ]

    ];

    protected $inlineScripts = [
        'addAction' => ['forms/pi-hearing', 'shared/definition'],
        'editAction' => ['forms/pi-hearing', 'shared/definition'],
        'indexAction' => ['table-actions']
    ];

    /**
     * @return string
     */
    public function getPageInnerLayout()
    {
        return 'layout/case-details-subsection';
    }

    /**
     * @return string
     */
    public function getPageLayout()
    {
        return 'layout/case-section';
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $pi = $this->getPi();
        $slaData = ['agreedDate' => $pi['agreedDate']];
        $this->getServiceLocator()->get('Common\Service\Data\Sla')->setContext('pi_hearing', $slaData);

        if ($pi['isClosed']) {
            $this->tableName = 'piHearingReadOnly';
        }

        return $this->index(
            $this->listDto,
            new GenericList($this->listVars, $this->defaultTableSortField),
            $this->tableViewPlaceholderName,
            $this->tableName,
            $this->tableViewTemplate,
            $this->filterForm
        );
    }



    /**
     * Adds a Pi Hearing, redirects to the Pi index page with a message if the Pi is closed
     *
     * @return ViewModel|\Zend\Http\Response
     */
    public function addAction()
    {
        $pi = $this->getPi();

        if ($pi['isClosed']) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage(self::MSG_CLOSED_PI);
            return $this->redirectTo([]);
        }

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

    /**
     * Edits a Pi Hearing. No check for whether Pi is closed - if it's closed then read only version is shown
     *
     * @return array|ViewModel
     */
    public function editAction()
    {
        $data = $this->getHearing();
        $data['agreedDate'] = $data['pi']['agreedDate']; //used by sla
        $this->getServiceLocator()->get('Common\Service\Data\Sla')->setContext('pi_hearing', $data);

        return parent::editAction();
    }

    /**
     * Link to generate a hearing letter, redirects to the Pi index page with a message if the Pi is closed
     *
     * @return \Zend\Http\Response
     */
    public function generateAction()
    {
        $pi = $this->getPi();

        if ($pi['isClosed']) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage(self::MSG_CLOSED_PI);
            return $this->redirectTo([]);
        }

        return $this->redirect()->toRoute(
            'case_licence_docs_attachments/entity/generate',
            [
                'case' => $this->params()->fromRoute('case'),
                'licence' => (isset($pi['case']['licence']['id']) ? $pi['case']['licence']['id'] : null),
                'entityType' => 'hearing',
                'entityId' => $this->params()->fromRoute('id')
            ]
        );
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

        if ($data['pi']['isClosed']) {
            if (isset($data['piVenue']['id'])) {
                $form->get('fields')->remove('piVenueOther');
            } else {
                $form->get('fields')->remove('piVenue');
                $form->get('fields')->get('piVenueOther')->setLabel('Venue');
            }

            $form->get('fields')->remove('pubType');
            $form->get('fields')->remove('trafficAreas');
            $form->get('fields')->remove('definition');
            $form->setOption('readonly', true);
        } else {
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
        }

        return $form;
    }
}
