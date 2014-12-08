<?php

/**
 * Case Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Cases;

use Zend\View\Model\ViewModel;
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Case Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CaseController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;
    use ControllerTraits\DocumentActionTrait;
    use ControllerTraits\DocumentSearchTrait;
    use ControllerTraits\ListDataTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'case';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'case';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'cases';

    /**
     * The current page's extra layout, over and above the
     * standard base template
     *
     * @var string
     */
    protected $pageLayout = 'case';

    protected $pageLayoutInner = 'case/inner-layout';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Cases';

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
            /**
             * @todo [OLCS-5306] check this, it appears to be an invalid part of the bundle
            'submissionSections' => array(
                'properties' => array(
                    'id',
                    'description'
                )
            ),
             */
            'legacyOffences' => array(
                'properties' => 'ALL',
            ),
            'caseType' => array(
                'properties' => 'ALL',
            ),
            'categorys' => array(
                'properties' => 'ALL',
            ),
            'licence' => array(
                'properties' => 'ALL',
                'children' => array(
                    'status' => array(
                        'properties' => array('id')
                    ),
                    'licenceType' => array(
                        'properties' => array('id')
                    ),
                    'goodsOrPsv' => array(
                        'properties' => array('id')
                    ),
                    'trafficArea' => array(
                        'properties' => 'ALL'
                    ),
                    'organisation' => array(
                        'properties' => 'ALL',
                        'children' => array(
                            'type' => array(
                                'properties' => array('id')
                            )
                        )
                    )
                )
            )
        )
    );

    protected $detailsView = 'case/overview';

    /**
     * Holds an array of variables for the default
     * index list page.
     */
    protected $listVars = [
        'licence',
        'application',
        'transportManager'
    ];

    /**
     * @var int $licenceId cache of licence id for a given case
     */
    protected $licenceId;

    public function redirectAction()
    {
        return $this->redirectToRoute('case', ['action' => 'details'], [], true);
    }

    /**
     * Simple redirect to index.
     */
    public function redirectToIndex()
    {
        // Makes cancel work.
        $case = $this->getQueryOrRouteParam('case', null);

        if (!$case && (!func_num_args() || !$case = func_get_arg(0))) {
            throw new \LogicException('Case missing');
        }

        return $this->redirectToRoute(
            'case',
            ['action' => 'details', $this->getIdentifierName() => $case],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }

    public function processSave($data)
    {
        if (empty($data['fields']['id'])) {
            $data['fields']['openDate'] = date('Y-m-d');
        }

        $result = parent::processSave($data, false);

        if (empty($data['fields']['id'])) {
            $case = $result['id'];
        } else {
            $case = $data['fields']['id'];
        }

        return $this->redirectToIndex($case);
    }

    /**
     * List of cases. Moved to Licence controller's cases method.
     *
     * @return void
     */
    public function indexAction()
    {
        return $this->redirectToRoute('case', ['action' => 'details'], [], true);
    }

    /**
     * Add a new case
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $this->setPageLayout('simple');
        $this->setPageLayoutInner(null);

        return parent::addAction();
    }

    public function editAction()
    {
        $this->setPageLayout('case');
        $this->setPageLayoutInner(null);

        return parent::editAction();
    }

    public function processLoad($data)
    {
        $data = parent::processLoad($data);

        if ($licence = $this->getQueryOrRouteParam('licence', null)) {
            $data['licence'] = $licence;
            $data['fields']['licence'] = $licence;
        }

        if ($application = $this->getQueryOrRouteParam('application', null)) {
            $data['application'] = $application;
            $data['fields']['application'] = $application;
        }

        if ($transportManager = $this->getQueryOrRouteParam('transportManager', null)) {
            $data['transportManager'] = $transportManager;
            $data['fields']['transportManager'] = $transportManager;
        }

        return $data;
    }

    /**
     * Route (prefix) for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'case_licence_docs_attachments';
    }

    /**
     * Route params for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return array(
            'case' => $this->getFromRoute('case'),
            'licence' => $this->getLicenceIdForCase()
        );
    }

    /**
     * Get view model for document action
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return ViewModel
     */
    protected function getDocumentView()
    {
        $licenceId = $this->getLicenceIdForCase();

        // caution, if $licenceId is empty we get ALL documents
        // AC says this will be addressed in later stories

        $filters = $this->mapDocumentFilters(
            array('licenceId' => $licenceId)
        );

        $table = $this->getDocumentsTable($filters);
        $form  = $this->getDocumentForm($filters);

        $view = $this->getView(
            array(
                'table' => $table,
                'form'  => $form
            )
        );

        $this->setPageLayoutInner(null);

        return $this->render($view);
    }

    /**
     * Gets licence id from route or backend, caching it in member variable
     */
    protected function getLicenceIdForCase() {
        if (is_null($this->licenceId)) {
            $this->licenceId = $this->getQueryOrRouteParam('licence');
            if (empty($this->licenceId)) {
                $case = $this->getCase();
                $this->licenceId = $case['licence']['id'];
            }
        }
        return $this->licenceId;
    }
}
