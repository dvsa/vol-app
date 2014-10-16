<?php

/**
 * Cases Submission Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Submission;

use Olcs\Controller as OlcsController;
use Zend\View\Model\ViewModel;
use Olcs\Controller\Cases\AbstractController as AbstractCasesController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Cases Submission Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class SubmissionController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'submission';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'submission';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'submission';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case';

    protected $detailsView = 'case/submission/details';

    protected $pageLayoutInner = null;

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Submission';

    /**
     * Holds an array of variables for the default
     * index list page.
     */
    protected $listVars = [
        'case',
    ];

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields'
            )
        )
    );

    protected $action = false;

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => 'ALL',
        'children' => array(
            'submissionType' => array(
                'properties' => 'ALL',
            ),
            'case' => array(
                'properties' => 'ALL',
            )
        )
    );

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_submissions';

    /**
     * Holds all the submission section ref data with descriptions
     */
    protected $submissionSectionRefData = array();

    public function alterFormBeforeValidation($form)
    {
        $postData = $this->getFromPost('fields');
        $formData = $this->getDataForForm();

        // Intercept Submission type submit button to prevent saving
        if (isset($postData['submissionSections']['submissionTypeSubmit']) ||
            !(empty($formData['submissionType']))) {
            $this->setPersist(false);
        } else {
            // remove form-actions
            $form->remove('form-actions');
        }

        return $form;
    }

    /**
     * Override Save data to allow json encoding of submission sections
     * into submission 'dataSnapshot' field.
     *
     * @param array $data
     * @param string $service
     * @return array
     */
    public function save($data, $service = null)
    {
        // modify $data
        $this->submissionConfig = $this->getServiceLocator()->get('config')['submission_config'];
        $submissionService = $this->getServiceLocator()->get('Olcs\Service\Data\Submission');
        $params = $this->getParams(array('case'));
        $caseId = $params['case'];

        if (is_array($data['submissionSections']['sections'])) {

            foreach ($data['submissionSections']['sections'] as $index => $sectionId) {
                $sectionConfig = isset($this->submissionConfig['sections'][$sectionId]) ?
                    $this->submissionConfig['sections'][$sectionId] : [];

                $data['submissionSections']['sections'][$index] = [
                    'sectionId' => $sectionId,
                    'data' => $submissionService->createSubmissionSection(
                        $caseId,
                        $sectionId,
                        $sectionConfig
                    )
                ];
            }
        }

        $data['dataSnapshot'] = json_encode($data['submissionSections']['sections']);
        $data['submissionType'] = $data['submissionSections']['submissionType'];

        $data = $this->callParentSave($data, $service);

        return $data;
    }

    /**
     * Complete section and save
     * Redirects to details action.
     *
     * @param array $data
     * @return array
     */
    public function processSave($data)
    {
        $result = $this->callParentProcessSave($data);

        $id = isset($result['id']) ? $result['id'] : $data['fields']['id'];
        return $this->redirect()->toRoute('submission', ['action' => 'details', 'submission' => $id], [], true);
    }

    /**
     * Call parent process save and return result. Public method to allow unit testing
     *
     * @param array $data
     * @return array
     */
    public function callParentProcessSave($data)
    {
        // pass false to prevent default redirect back to index action
        // and return result of the save
        return parent::processSave($data, false);
    }

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        $data = $this->callParentProcessLoad($data);

        $case = $this->getCase();

        $data['fields']['case'] = $case['id'];

        if (isset($data['submissionSections']['sections'])) {
            $sectionData = json_decode($data['submissionSections']['sections'], true);
            $data['fields']['submissionSections']['sections'] = $this->extractSectionIds($sectionData);
        } elseif (isset($data['dataSnapshot'])) {
            $sectionData = json_decode($data['dataSnapshot'], true);
            $data['fields']['submissionSections']['submissionType'] = $data['submissionType'];
            $data['fields']['submissionSections']['sections'] = $this->extractSectionIds($sectionData);
            $data['case'] = $case['id'];
            $data['fields']['id'] = $data['id'];
            $data['fields']['version'] = $data['version'];
        }

        return $data;
    }

    /**
     * Call parent process load and return result. Public method to allow unit testing
     *
     * @param array $data
     * @return array
     */
    public function callParentProcessLoad($data)
    {
        return parent::processLoad($data);
    }

    /**
     * Call parent process load and return result. Public method to allow unit testing
     *
     * @param array $data
     * @return array
     */
    public function callParentSave($data, $service = null)
    {
        return parent::save($data, $service);
    }

    private function extractSectionIds($sectionData)
    {
        $sectionIds = [];
        if (is_array($sectionData)) {
            foreach ($sectionData as $section) {
                $sectionIds[] = $section['sectionId'];
            }
        }
        return $sectionIds;
    }

    /**
     * Details action - shows each section detail
     *
     * @return ViewModel
     */
    public function detailsAction()
    {
        $submissionId = $this->getQueryOrRouteParam('submission');

        $submissionService = $this->getServiceLocator()
            ->get('Olcs\Service\Data\Submission');

        $submission = $submissionService->fetchSubmissionData($submissionId);

        $submission['submissionTypeTitle'] =
            $submissionService->getSubmissionTypeTitle(
                $submission['submissionType']['id']
            );

        $selectedSectionsArray =
            $submissionService->extractSelectedSubmissionSectionsData(
                $submission
            );

        $this->getViewHelperManager()
            ->get('placeholder')
            ->getContainer('selectedSectionsArray')
            ->set($selectedSectionsArray);

        $this->getViewHelperManager()
            ->get('placeholder')
            ->getContainer($this->getIdentifierName())
            ->set($submission);

        $view = $this->getView([]);
        $view->setVariable('allSections', $submissionService->getAllSectionsRefData());

        $view->setTemplate($this->detailsView);

        return $this->renderView($view);
    }
}
