<?php

/**
 * Overview Controller, also deals with add and edit of cases
 */
namespace Olcs\Controller\Cases\Overview;

use Olcs\Controller\AbstractInternalController;
use Dvsa\Olcs\Transfer\Command\Cases\CreateCase as CreateCaseCommand;
use Dvsa\Olcs\Transfer\Command\Cases\UpdateCase as UpdateCaseCommand;
use Dvsa\Olcs\Transfer\Command\Cases\DeleteCase as DeleteCaseCommand;
use Dvsa\Olcs\Transfer\Query\Cases\Cases as CasesDto;
use Olcs\Data\Mapper\GenericFields as GenericMapper;
use Olcs\Form\Model\Form\Cases as CaseForm;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;

/**
 * Overview Controller, also deals with add and edit of cases
 */
class OverviewController extends AbstractInternalController implements
    CaseControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
{
    protected $navigationId = 'case_details_overview';
    protected $detailsViewTemplate = 'pages/case/overview';
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

    protected $redirectConfig = [
        'add' => [
            'action' => 'details',
            'resultIdMap' => [
                'case' => 'case'
            ]
        ],
        'edit' => [
            'action' => 'details'
        ]
    ];

    public function getPageLayout()
    {
        $action = $this->params()->fromRoute('action');

        switch ($action) {
            case 'add':
                $licence = $this->params()->fromRoute('licence');
                $application = $this->params()->fromRoute('application');
                $transportManager = $this->params()->fromRoute('transportManager');

                $this->navigationId = 'case';
                $this->setNavigationCurrentLocation();

                if ($licence) {
                    return 'layout/licence-section';
                }

                if ($transportManager) {
                    return 'layout/transport-manager-section-crud';
                }

                if ($application) {
                    return 'layout/application-section';
                }
                //missing break is intentional
            default:
                return 'layout/case-section';
        }
    }

    public function getPageInnerLayout()
    {
        $action = $this->params()->fromRoute('action');

        switch ($action) {
            case 'add':
                $licence = $this->params()->fromRoute('licence');
                $application = $this->params()->fromRoute('application');
                $transportManager = $this->params()->fromRoute('transportManager');

                if ($licence) {
                    return 'layout/licence-details-subsection';
                }

                if ($transportManager) {
                    return 'layout/wide-layout';
                }

                if ($application) {
                    return 'layout/wide-layout';
                }
                //missing break is intentional
            default:
                return 'layout/case-details-subsection';
        }
    }

    /**
     * If we're deleting then we need to set redirect config dynamically
     *
     * @param array $restResponse
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

    public function redirectAction()
    {
        return $this->redirect()->toRouteAjax('case', ['action' => 'details'], [], true);
    }

    /**
     * Alter Form to remove case type options depending on where the case was added from.
     *
     * @param \Common\Controller\Form $form
     * @param array $initialData
     * @return \Common\Controller\Form
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
     * @param \Common\Controller\Form $form
     * @param array $initialData
     * @return \Common\Controller\Form
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
     * @param \Common\Controller\Form $form
     * @param $application
     * @param $transportManager
     * @param $licence
     * @return \Common\Controller\Form
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
