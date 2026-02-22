<?php

namespace Olcs\Controller\Cases\PublicInquiry;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\CreatePiSlaException as CreatePiSlaExceptionCmd;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\PiSlaException as PiSlaExceptionMapper;
use Olcs\Form\Model\Form\PublicInquirySlaException;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;

class SlaExceptionController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    /**
     * Details view
     */
    protected $navigationId = 'case_hearings_appeals_public_inquiry';

    /**
     * SLA Exception form and commands
     */
    protected $slaExceptionForm = PublicInquirySlaException::class;
    protected $addSlaExceptionCommand = CreatePiSlaExceptionCmd::class;

    protected $defaultData = ['case' => AddFormDefaultData::FROM_ROUTE];

    protected $redirectConfig = [
        'add' => [
            'route' => 'case_pi',
            'action' => 'index'
        ]
    ];

    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        Navigation $navigation
    ) {
        parent::__construct($translationHelper, $formHelper, $flashMessengerHelper, $navigation);
    }

    /**
     * get method View Model
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
     * Add SLA Exception
     *
     * @return array|\Laminas\View\Model\ViewModel
     */
    #[\Override]
    public function addAction()
    {
        return $this->add(
            $this->slaExceptionForm,
            new GenericItem(['id' => 'case']),
            $this->addSlaExceptionCommand,
            PiSlaExceptionMapper::class,
            $this->editViewTemplate,
            'SLA Exception added successfully',
            'Add SLA Exception'
        );
    }

    /**
     * Alter form for SLA Exception - populate case ID
     *
     * @param \Laminas\Form\FormInterface $form Form
     * @param array $data Data
     *
     * @return \Laminas\Form\FormInterface
     */
    public function alterFormForAdd($form, $data = [])
    {
        // Case ID should already be set in initial data via mapper
        // But if it's missing, get it from route as fallback
        $caseId = $data['fields']['case'] ?? $this->params()->fromRoute('case');

        if ($caseId) {
            $form->get('fields')->get('case')->setValue($caseId);
        }

        return $form;
    }

    /**
     * Gets Pi information for the current case
     *
     * @return array|mixed
     */
    private function getPi()
    {
        $params = ['id' => $this->params()->fromRoute('case')];
        $response = $this->handleQuery(\Dvsa\Olcs\Transfer\Query\Cases\Pi::create($params));

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelperService->addErrorMessage('unknown-error');
            return [];
        }

        return $response->getResult();
    }
}
