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
class CaseController extends OlcsController\CrudAbstract implements OlcsController\Interfaces\CaseControllerInterface
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
    protected $pageLayout = 'case-section';

    protected $pageLayoutInner = 'layout/case-details-subsection';

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

    protected $detailsView = 'pages/case/overview';

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
        $this->setPageLayout('case-section');
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

            //if we don't have a licence, try to find one from the application
            if (!$licence) {
                $applicationData = $this->getApplication($application);
                if (isset($applicationData['licence']['id'])) {
                    $data['licence'] = $applicationData['licence']['id'];
                    $data['fields']['licence'] = $applicationData['licence']['id'];
                }
            }
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

        $this->setPageLayoutInner(null);

        return $this->getView(
            array(
                'table' => $table,
                'form'  => $form
            )
        );
    }

    /**
     * Gets licence id from route or backend, caching it in member variable
     */
    protected function getLicenceIdForCase()
    {
        if (is_null($this->licenceId)) {
            $this->licenceId = $this->getQueryOrRouteParam('licence');
            if (empty($this->licenceId)) {
                $case = $this->getCase();
                $this->licenceId = $case['licence']['id'];
            }
        }
        return $this->licenceId;
    }

    /**
     * Gets application data (used to retrieve the licence id for an application)
     */
    protected function getApplication($application)
    {
        $service = $this->getServiceLocator()->get('DataServiceManager')->get('Generic\Service\Data\Application');
        return $service->fetchOne($application);
    }

    /**
     * Alter Form to remove case type options depending on where the case was added from.
     *
     * @param \Common\Controller\Form $form
     * @return \Common\Controller\Form
     */
    public function alterForm($form)
    {
        $licence = $this->params()->fromRoute('licence', '');
        $application = $this->params()->fromRoute('application', '');
        $transportManager = $this->params()->fromRoute('transportManager', '');
        $unwantedOptions = [];

        if (!empty($licence)) {
            $unwantedOptions = ['case_t_tm' => '', 'case_t_app' => ''];
        } elseif (!empty($application)) {
            $unwantedOptions = ['case_t_tm' => '', 'case_t_lic' => '', 'case_t_imp' => ''];
        } elseif (!empty($transportManager)) {
            $unwantedOptions = ['case_t_imp' => '', 'case_t_app' => '', 'case_t_lic' => ''];
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
