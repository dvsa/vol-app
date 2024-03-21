<?php

namespace Olcs\Controller\Cases\Overview;

use Dvsa\Olcs\Transfer\Command\Cases\CloseCase as CloseCmd;
use Dvsa\Olcs\Transfer\Command\Cases\CreateCase as CreateCaseCommand;
use Dvsa\Olcs\Transfer\Command\Cases\DeleteCase as DeleteCaseCommand;
use Dvsa\Olcs\Transfer\Command\Cases\ReopenCase as ReopenCmd;
use Dvsa\Olcs\Transfer\Command\Cases\UpdateCase as UpdateCaseCommand;
use Dvsa\Olcs\Transfer\Query\Cases\Cases as CasesDto;
use Laminas\Form\FormInterface;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\NavigationIdProvider;
use Olcs\Controller\Interfaces\RightViewProvider;
use Olcs\Data\Mapper\GenericFields as GenericMapper;
use Olcs\Form\Model\Form\Cases as CaseForm;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;

class OverviewController extends AbstractInternalController implements
    CaseControllerInterface,
    LeftViewProvider,
    RightViewProvider,
    NavigationIdProvider
{
    protected $navigationId = 'case_details_overview';
    protected $detailsViewTemplate = 'sections/cases/pages/overview';
    protected $itemDto = CasesDto::class;
    protected $defaultData = [
        'case' => AddFormDefaultData::FROM_ROUTE,
        'licence' => AddFormDefaultData::FROM_ROUTE,
        'application' => AddFormDefaultData::FROM_ROUTE,
        'transportManager' => AddFormDefaultData::FROM_ROUTE
    ];
    protected $itemParams = ['id' => 'case', 'case', 'application', 'licence', 'transportManager'];
    protected $formClass = CaseForm::class;
    protected $createCommand = CreateCaseCommand::class;
    protected $updateCommand = UpdateCaseCommand::class;
    protected $deleteParams = ['id' => 'case'];
    protected $deleteCommand = DeleteCaseCommand::class;
    protected $mapperClass = GenericMapper::class;
    protected $addContentTitle = 'Add case';
    protected $editContentTitle = 'Edit case';

    /**
     * Close
     */
    protected $closeCommand = CloseCmd::class;
    protected $closeParams = ['id' => 'case'];
    protected $closeModalTitle = 'Close the case';
    protected $closeConfirmMessage = 'Are you sure you want to close the case?';
    protected $closeSuccessMessage = 'Case closed';

    /**
     * Reopen
     */
    protected $reopenCommand = ReopenCmd::class;
    protected $reopenParams = ['id' => 'case'];
    protected $reopenModalTitle = 'Reopen the Case?';
    protected $reopenConfirmMessage = 'Are you sure you want to reopen the case?';
    protected $reopenSuccessMessage = 'Case reopened';

    protected $redirectConfig = [
        'add' => [
            'action' => 'details',
            'resultIdMap' => [
                'case' => 'case'
            ]
        ],
        'edit' => [
            'action' => 'details'
        ],
        'close' => [
            'action' => 'details'
        ],
        'reopen' => [
            'action' => 'details'
        ]
    ];


    /**
     * Get Method for Navigation
     *
     * @return null|string
     */
    public function getNavigationId()
    {
        $action = $this->params()->fromRoute('action');

        switch ($action) {
            case 'add':
                $licence = $this->params()->fromRoute('licence');
                $application = $this->params()->fromRoute('application');
                $transportManager = $this->params()->fromRoute('transportManager');

                if ($licence) {
                    return 'case';
                }

                if ($transportManager) {
                    return 'transport_managers';
                }

                if ($application) {
                    return 'application';
                }
                // Missing break is intentional
            default:
                return null;
        }
    }

    /**
     * get method for Right View
     *
     * @return null|ViewModel
     */
    public function getRightView()
    {
        $action = $this->params()->fromRoute('action');

        switch ($action) {
            case 'add':
                $licence = $this->params()->fromRoute('licence');
                $application = $this->params()->fromRoute('application');
                $transportManager = $this->params()->fromRoute('transportManager');

                if ($licence) {
                    $viewModel = new ViewModel();
                    $viewModel->setTemplate('sections/licence/partials/right');
                    return $viewModel;
                }

                if ($transportManager) {
                    $viewModel = new ViewModel();
                    $viewModel->setTemplate('sections/transport-manager/partials/right');
                    return $viewModel;
                }

                if ($application) {
                    $viewModel = new ViewModel();
                    $viewModel->setTemplate('sections/application/partials/right');
                    return $viewModel;
                }
                // Missing break is intentional
            default:
                // Already setup in the listener
                return null;
        }
    }

    /**
     * get method for Left View
     *
     * @return null|ViewModel
     */
    public function getLeftView()
    {
        $action = $this->params()->fromRoute('action');

        switch ($action) {
            case 'add':
                $licence = $this->params()->fromRoute('licence');
                $application = $this->params()->fromRoute('application');
                $transportManager = $this->params()->fromRoute('transportManager');

                if ($licence) {
                    $viewModel = new ViewModel();
                    $viewModel->setTemplate('sections/licence/partials/left');
                    return $viewModel;
                }

                if ($transportManager) {
                    return null;
                }

                if ($application) {
                    return null;
                }
                // Missing break is intentional
            default:
                $viewModel = new ViewModel();
                $viewModel->setTemplate('sections/cases/partials/left');
                return $viewModel;
        }
    }

    /**
     * If we're deleting then we need to set redirect config dynamically
     *
     * @param array $restResponse rest response
     *
     * @return string
     */
    public function redirectConfig(array $restResponse)
    {
        $action = $this->params()->fromRoute('action');

        if (strtolower($action) == 'delete') {
            $licence = $this->params()->fromRoute('licence');
            $application = $this->params()->fromRoute('application');
            $transportManager = $this->params()->fromRoute('transportManager');

            if ($licence) {
                $this->redirectConfig['delete'] = [
                    'route' => 'licence/cases',
                    'action' => 'cases'
                ];
            } elseif ($transportManager) {
                $this->redirectConfig['delete'] = [
                    'route' => 'transport-manager/cases',
                    'action' => 'index'
                ];
            } elseif ($application) {
                $this->redirectConfig['delete'] = [
                    'route' => 'lva-application/case',
                    'action' => 'case'
                ];
            }
        }

        return parent::redirectConfig($restResponse);
    }

    /**
     * @var int $licenceId cache of licence id for a given case
     */
    protected $licenceId;

    /**
     * redirectAction
     *
     * @return \Laminas\Http\Response
     */
    public function redirectAction()
    {
        return $this->redirect()->toRouteAjax('case', ['action' => 'details'], [], true);
    }

    /**
     * Alter Form to remove case type options depending on where the case was added from.
     *
     * @param FormInterface           $form        form
     * @param array                   $initialData initialData
     *
     * @return FormInterface
     */
    public function alterFormForAdd($form, $initialData)
    {
        return $this->getFormCaseTypes(
            $form,
            $initialData['fields']['application'],
            $initialData['fields']['transportManager'],
            $initialData['fields']['licence']
        );
    }

    /**
     * Alter Form to remove case type options depending on where the case was added from.
     *
     * @param FormInterface           $form        form
     * @param array                   $initialData initialData
     *
     * @return FormInterface
     */
    public function alterFormForEdit($form, $initialData)
    {
        switch ($initialData['fields']['caseType']) {
            case 'case_t_app':
                $unwantedOptions = ['case_t_tm' => '', 'case_t_lic' => '', 'case_t_imp' => ''];
                break;
            case 'case_t_tm':
                $unwantedOptions = ['case_t_app' => '', 'case_t_lic' => '', 'case_t_imp' => ''];
                break;
            default:
                $unwantedOptions = ['case_t_tm' => '', 'case_t_app' => ''];
                break;
        }

        $options = $form->get('fields')
            ->get('caseType')
            ->getValueOptions();

        $form->get('fields')
            ->get('caseType')
            ->setValueOptions(array_diff_key($options, $unwantedOptions));

        $form->get('fields')
            ->get('caseType')
            ->setEmptyOption(null);

        return $form;
    }

    /**
     * Works out the case types
     *
     * @param FormInterface           $form             form
     * @param int                     $application      application
     * @param int                     $transportManager transportManager
     * @param int                     $licence          licence
     *
     * @return FormInterface
     */
    private function getFormCaseTypes($form, $application, $transportManager, $licence)
    {
        $unwantedOptions = [];

        if (!empty($application)) {
            $unwantedOptions = ['case_t_tm' => '', 'case_t_lic' => '', 'case_t_imp' => ''];
            $form->get('fields')
                ->get('caseType')
                ->setEmptyOption(null);
        } elseif (!empty($transportManager)) {
            $unwantedOptions = ['case_t_imp' => '', 'case_t_app' => '', 'case_t_lic' => ''];
            $form->get('fields')
                ->get('caseType')
                ->setEmptyOption(null);
        } elseif (!empty($licence)) {
            $unwantedOptions = ['case_t_tm' => '', 'case_t_app' => ''];
        }

        $options = $form->get('fields')
            ->get('caseType')
            ->getValueOptions();

        $form->get('fields')
            ->get('caseType')
            ->setValueOptions(array_diff_key($options, $unwantedOptions));

        return $form;
    }
}
