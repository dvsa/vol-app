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

    protected $detailsView = 'case/submission/overview';

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

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'submissionActions' => array(
                'properties' => 'ALL',
                'children' => array(
                    'senderUser' => array(
                        'properties' => 'ALL'
                    ),
                    'recipientUser' => array(
                        'properties' => 'ALL'
                    ),
                )
            )
        )
    );

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
        if (isset($formData['submission_sections']['submission_type_submit'])) {
            $this->setPersist(false);
        }
        return parent::addAction();
    }

    /**
     * Save data
     *
     * @param array $data
     * @param string $service
     * @return array
     */
    protected function save($data, $service = null)
    {
        // modify $data

        $data['text'] = json_encode($data['submission_sections']['submission_sections']);

        return parent::save($data, $service);
    }

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    protected function processLoad($data)
    {
        // modify $data for form population
        $routeParams = $this->params()->fromRoute();
        $caseId = $routeParams['case'];
        $case = $this->getCase($caseId);
        $data['fields']['case'] = $case['id'];
        if (isset($data['submission_sections']['submission_sections'])) {
            $data['fields']['submission_sections']['submission_sections'] = json_decode($data['submission_sections']['submission_sections']);
        }

        return parent::processLoad($data);
    }


    /**
     * Get form name. Overridden so as not to create a form called SubAction
     *
     * @return string
     */
    protected function getFormName()
    {
        return $this->formName;
    }

    /**
     * Callback function following save, redirects to
     * submission details list where each section can be completed.
     *
     * @param array $data
     * @return array
     */
    protected function processSave($data)
    {
        return $this->redirect()->toRoute('submission', ['action' => 'details', 'submission' => null], [], true);
    }
}
