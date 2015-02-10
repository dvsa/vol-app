<?php

/**
 * Case Submission  Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Submission;

//use Olcs\Controller\Traits\DeleteActionTrait;
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Case Submission Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class RecommendationController extends OlcsController\CrudAbstract implements
    OlcsController\Interfaces\CaseControllerInterface
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'id';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'recommendation';

    /**
     * Name of comment box field.
     *
     * @var string
     */
    protected $commentBoxName = '';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'submission-recommendation';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case-section';

    protected $pageLayoutInner = null;

    protected $defaultTableSortField = '';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'SubmissionAction';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_submissions';

    /**
     * Holds an array of variables for the default
     * index list page.
     */
    protected $listVars = [
        'submission',
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
        'properties' => 'ALL',
        'children' => array(
            'submission' => array(
                'properties' => 'ALL',
            ),
            'submissionActionStatus' => array(
                'properties' => 'ALL',
            ),
            'recipientUser' => array(
                'properties' => 'ALL',
            ),
            'senderUser' => array(
                'properties' => 'ALL',
            ),
            'reasons' => array(
                'properties' => 'ALL',
            )
        )
    );

    /**
     * @var array
     */
    protected $inlineScripts = ['submission-rec-dec'];

    /**
     * Simple redirect to index.
     */
    public function redirectToIndex()
    {
        $submission = $this->params()->fromRoute('submission');

        return $this->redirectToRoute(
            'submission',
            ['action'=>'details', 'submission' => $submission],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function parentProcessLoad($data)
    {
        return parent::processLoad($data);
    }

    public function processLoad($data)
    {
        $data = $this->parentProcessLoad($data);

        if (!isset($data['fields']['submission'])) {
            $data['fields']['submission'] = $this->params()->fromRoute('submission');
        }

        if (!isset($data['fields']['senderUser'])) {
            $data['fields']['senderUser'] = $this->getLoggedInUser();
        }

        return $data;
    }
}
