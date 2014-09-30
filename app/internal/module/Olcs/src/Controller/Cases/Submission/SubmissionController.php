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
     * Holds all the submission section data
     */
    protected $submissionSectionData = array();

    /**
     * Save data. Also processes the submit submission select type drop down
     * in order to dictate which checkboxes to manipulate.
     *
     * @param array $data
     * @param string $service
     * @return array
     */
    public function addAction()
    {
        // Modify $data
        $formData = $this->getFromPost('fields');

        // Intercept Submission type submit button to prevent saving
        if (isset($formData['submissionSections']['submissionTypeSubmit'])) {
            $this->setPersist(false);
        } else {
            // remove form-actions
            $form = $this->getForm($this->getFormName());
            $form->remove('formActions[submit]');
        }

        $form = $this->generateFormWithData($this->getFormName(), $this->getFormCallback(), $this->getDataForForm());

        $view = $this->getView();

        $this->getViewHelperManager()->get('placeholder')->getContainer('form')->set($form);

        $view->setTemplate('crud/form');

        return $this->renderView($view);
    }

    /**
     * Save data. Also processes the submit submission select type drop down
     * in order to dictate which checkboxes to manipulate.
     *
     * @param array $data
     * @param string $service
     * @return array
     */
    public function editAction()
    {
        // Modify $data
        $formData = $this->getFromPost('fields');

        // Intercept Submission type submit button to prevent saving
        if (isset($formData['submissionSections']['submissionTypeSubmit'])) {
            $this->setPersist(false);
        } else {
            // remove form-actions
            $form = $this->getForm($this->getFormName());
            $form->remove('formActions[submit]');
        }

        $form = $this->generateFormWithData($this->getFormName(), $this->getFormCallback(), $this->getDataForForm());

        $view = $this->getView();

        $this->getViewHelperManager()->get('placeholder')->getContainer('form')->set($form);

        $view->setTemplate('crud/form');

        return $this->renderView($view);
    }

    /**
     * Override Save data to allow json encoding of submission sections
     * into submission 'text' field.
     *
     * @param array $data
     * @param string $service
     * @return array
     */
    protected function save($data, $service = null)
    {
        // modify $data

        $data['text'] = json_encode($data['submissionSections']['sections']);
        $data['submissionType'] = $data['submissionSections']['submissionType'];
        $data = parent::save($data, $service);

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
        $data = parent::processLoad($data);

        $case = $this->getCase();

        $data['fields']['case'] = $case['id'];

        if (isset($data['submissionSections']['sections'])) {
            $data['fields']['submissionSections']['sections'] = json_decode($data['submissionSections']['sections']);
        } elseif (isset($data['text'])) {
            $data['fields']['submissionSections']['submissionType'] = $data['submissionType'];
            $data['fields']['submissionSections']['sections'] = json_decode($data['text']);
            $data['case'] = $case['id'];
            $data['fields']['id'] = $data['id'];
            $data['fields']['version'] = $data['version'];
        }

        return $data;
    }

    /**
     * Details action - shows each section detail
     *
     * @return ViewModel
     */
    public function detailsAction()
    {
        $submission = $this->loadCurrent();

        $view = $this->getView([]);

        $submissionsArray = json_decode($submission['text']);

        $this->submissionSections = $this->getServiceLocator()->get(
            'Common\Service\Data\RefData'
        )->fetchListData('submission_section');

        $submission['submissionTypeTitle'] = $this->getSubmissionTypeTitle($submission['submissionType']['id']);

        foreach ($this->submissionSections as $submissionSection) {
            if (in_array($submissionSection['id'], $submissionsArray)) {
                $this->submissionSectionData[$submissionSection['id']]['sectionInfo'] =
                    $submissionSection;
                $this->submissionSectionData[$submissionSection['id']]['data'] = isset
                ($submissionsArray[$submissionSection['id']]) ? $submissionsArray[$submissionSection['id']] : [];
            }
        }

        $this->getViewHelperManager()
            ->get('placeholder')
            ->getContainer('sectionData')
            ->set($this->submissionSectionData);

        $this->getViewHelperManager()
            ->get('placeholder')
            ->getContainer($this->getIdentifierName())
            ->set($submission);

        $view->setTemplate($this->detailsView);

        return $this->renderView($view);
    }

    /**
     * Extracts the title from ref_data based on a given submission type.
     *
     * @param string $submissionType
     * @return string
     */
    private function getSubmissionTypeTitle($submissionType)
    {
        $submissionTitles = $this->getServiceLocator()
            ->get('Common\Service\Data\RefData')->fetchListData('submission_type_title');

        foreach ($submissionTitles as $title) {
            if ($title['id'] == str_replace('_o_', '_t_', $submissionType)) {
                return $title['description'];
            }
        }
        return '';
    }
}
