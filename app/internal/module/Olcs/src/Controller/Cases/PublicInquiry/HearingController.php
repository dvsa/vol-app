<?php

namespace Olcs\Controller\Cases\PublicInquiry;

use Common\Service\Helper\FlashMessengerHelperService;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\CreateHearing as CreateCmd;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\UpdateHearing as UpdateCmd;
use Dvsa\Olcs\Transfer\Query\Cases\Pi as PiDto;
use Dvsa\Olcs\Transfer\Query\Cases\Pi\Hearing as PiHearingDto;
use Dvsa\Olcs\Transfer\Query\Cases\Pi\HearingList as PiHearingListDto;
use Laminas\Form\Form as LaminasForm;
use Laminas\Form\FormInterface;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\PiHearing as PiHearingMapper;
use Olcs\Form\Model\Form\PublicInquiryHearing as HearingForm;
use Olcs\Mvc\Controller\ParameterProvider\GenericList;
use Olcs\Mvc\Controller\ParameterProvider\PreviousPiHearingData;

class HearingController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    public const MSG_CLOSED_PI = 'The Pi has already been closed';

    protected $listVars = ['pi'];
    protected $navigationId = 'case_hearings_appeals_public_inquiry';
    protected $formClass = HearingForm::class;
    protected $tableName = 'piHearing';
    protected $listDto = PiHearingListDto::class;
    protected $itemDto = PiHearingDto::class;
    protected $createCommand = CreateCmd::class;
    protected $updateCommand = UpdateCmd::class;
    protected $mapperClass = PiHearingMapper::class;
    protected $addContentTitle = 'Add hearing';
    protected $editContentTitle = 'Edit hearing';

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

    protected FlashMessengerHelperService $flashMessenger;
    /**
     * get Method Left View
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/cases/partials/left');

        return $view;
    }

    /**
     * index Action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $pi = $this->getPi();

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
     * @return ViewModel|\Laminas\Http\Response
     */
    public function addAction()
    {
        $pi = $this->getPi();

        if ($pi['isClosed']) {
            $this->flashMessenger->addErrorMessage(self::MSG_CLOSED_PI);

            return $this->redirectTo([]);
        }

        return $this->add(
            $this->formClass,
            new PreviousPiHearingData($pi),
            $this->createCommand,
            $this->mapperClass,
            $this->editViewTemplate,
            'Create record',
            $this->addContentTitle
        );
    }

    /**
     * Link to generate a hearing letter, redirects to the Pi index page with a message if the Pi is closed
     *
     * @return \Laminas\Http\Response
     */
    public function generateAction()
    {
        $pi = $this->getPi();

        if ($pi['isClosed']) {
            $this->flashMessenger->addErrorMessage(self::MSG_CLOSED_PI);

            return $this->redirectTo([]);
        }

        return $this->redirect()->toRoute(
            'case_licence_docs_attachments/entity/generate',
            [
                'case' => $this->params()->fromRoute('case'),
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

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessenger->addErrorMessage('unknown-error');
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

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessenger->addErrorMessage('unknown-error');
        }

        return $response->getResult();
    }

    /**
     * Alter form for TM cases, set pubType and trafficAreas to be visible for publishing
     *
     * @param FormInterface $form form
     *
     * @return FormInterface
     */
    public function alterFormForAdd($form)
    {
        $data = $this->getPi();

        //only need to specify a pub type and traffic area if it's a tm case
        if (!($data['isTm'])) {
            $form->get('fields')->remove('pubType');
            $form->get('fields')->remove('trafficAreas');
        }

        // set SLAs
        $form = $this->setSlaTargetHint($form, $data);

        return $form;
    }

    /**
     * Alter form for TM cases, set pubType and trafficAreas to be visible for publishing
     *
     * @param FormInterface $form form
     *
     * @return FormInterface
     */
    public function alterFormForEdit($form)
    {
        $data = $this->getHearing();

        if ($data['pi']['isClosed']) {
            if (isset($data['venue']['id'])) {
                $form->get('fields')->remove('venueOther');
            } else {
                $form->get('fields')->remove('venue');
                $form->get('fields')->get('venueOther')->setLabel('Venue');
            }

            $form->get('fields')->remove('pubType');
            $form->get('fields')->remove('trafficAreas');
            $form->get('fields')->remove('definition');
            $form->setOption('readonly', true);
        } else {
            if ($data['isCancelled'] === 'Y' || $data['isAdjourned'] === 'Y') {
                // if cancelled or adjourned remove the publish button (OLCS-11222)
                $form->get('form-actions')->remove('publish');
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
            }

            //only need to specify a pub type and traffic area if it's a tm case
            if (!($data['isTm'])) {
                $form->get('fields')->remove('pubType');
                $form->get('fields')->remove('trafficAreas');
            }
        }

        // set SLAs
        $form = $this->setSlaTargetHint($form, $data);

        return $form;
    }

    /**
     * Sets the SLA target date as a hint on the form elements
     *
     * @param LaminasForm $form from
     * @param String      $data data
     *
     * @return LaminasForm
     */
    private function setSlaTargetHint(LaminasForm $form, $data)
    {
        $date = $data['hearingDateTarget'];
        if (empty($date)) {
            $hint = 'There was no target date found';
        } else {
            $hint = 'Target date: ' . date('d/m/Y', strtotime($date));
        }

        $element = $form->get('fields')->get('hearingDate');
        $element->setOption('hint', $hint);

        return $form;
    }
}
