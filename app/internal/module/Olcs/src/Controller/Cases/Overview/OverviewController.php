<?php

/**
 * Overview Controller, also deals with add and edit of cases
 */
namespace Olcs\Controller\Cases\Overview;

use Olcs\Controller\AbstractInternalController;
use Dvsa\Olcs\Transfer\Command\Cases\CreateCase as CreateCaseCommand;
use Dvsa\Olcs\Transfer\Command\Cases\UpdateCase as UpdateCaseCommand;
use Dvsa\Olcs\Transfer\Query\Cases\Cases as CasesDto;
use Olcs\Data\Mapper\CaseOverview as CaseOverviewMapper;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;

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
        'case' => 'route',
        'licence' => 'route',
        'application' => 'route',
        'transportManager' => 'route'
    ];
    protected $itemParams = ['id' => 'case', 'case', 'application', 'licence', 'transportManager'];
    protected $formClass = 'cases';
    protected $createCommand = CreateCaseCommand::class;
    protected $updateCommand = UpdateCaseCommand::class;
    protected $mapperClass = CaseOverviewMapper::class;

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
                    return 'layout/transport-manager-section';
                }

                if ($application) {
                    return 'layout/application-section';
                }
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
            default:
                return 'layout/case-details-subsection';
        }
    }

    /**
     * @var int $licenceId cache of licence id for a given case
     */
    protected $licenceId;

    public function redirectAction()
    {
        return $this->redirect()->toRouteAjax('case', ['action' => 'details'], [], true);
    }

    /*public function redirectToIndex()
    {
        // Makes cancel work.
        $case = $this->params()->fromRoute('case', null);

        return $this->redirect()->toRouteAjax(
            null,
            ['action' => 'details', 'case' => $case],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            false
        );
    }*/

    /**
     * List of cases. Moved to Licence controller's cases method.
     */
    public function indexAction()
    {
        return $this->redirect()->toRoute('case', ['action' => 'details'], [], true);
    }

    /**
     * Alter Form to remove case type options depending on where the case was added from.
     *
     * @param \Common\Controller\Form $form
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
     * @return \Common\Controller\Form
     */
    private function getFormCaseTypes($form, $application, $transportManager, $licence) {
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
