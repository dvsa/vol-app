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
    use ControllerTraits\CaseControllerTrait,
        ControllerTraits\ListDataTrait,
        ControllerTraits\CloseActionTrait;

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
    protected $tableName = 'cases';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'cases';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Cases';

    protected $dataServiceName = 'Cases';
    protected $entityDisplayName = 'Case';

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
            'outcomes',
            'legacyOffences' => array(),
            'caseType' => array(),
            'categorys' => array(),
            'licence' => array(
                'children' => array(
                    'status' => array(),
                    'licenceType' => array(),
                    'goodsOrPsv' => array(),
                    'trafficArea' => array(),
                    'organisation' => array(
                        'children' => array(
                            'type' => array()
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

        return $this->redirectToRouteAjax(
            'case',
            ['action' => 'details', $this->getIdentifierName() => $case],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }

    /**
     * List of cases. Moved to Licence controller's cases method.
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
        $this->setPageLayout(null);
        $this->setPageLayoutInner(null);

        return parent::addAction();
    }

    public function editAction()
    {
        $this->setPageLayout(null);
        $this->setPageLayoutInner(null);

        return parent::editAction();
    }

    public function processLoad($data)
    {
        $data = parent::processLoad($data);

        $licence
            = !empty($this->getCase()['licence']['id']) ?
                $this->getCase()['licence']['id'] :
                $this->getQueryOrRouteParam('licence', null);

        if ($licence) {
            $data['licence'] = $licence;
            $data['fields']['licence'] = $licence;
        }

        $application
            = !empty($this->getCase()['application']['id']) ?
                $this->getCase()['application']['id'] :
                $this->getQueryOrRouteParam('application', null);

        if ($application) {
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

        $transportManager
            = !empty($this->getCase()['transportManager']['id']) ?
                $this->getCase()['transportManager']['id'] :
                $this->getQueryOrRouteParam('transportManager', null);

        if ($transportManager) {
            $data['transportManager'] = $transportManager;
            $data['fields']['transportManager'] = $transportManager;
        }

        return $data;
    }

    /**
     * Gets licence id from route or backend, caching it in member variable
     */
    protected function getLicenceIdForCase()
    {
        if (is_null($this->licenceId)) {
            $case = $this->getCase();
            $this->licenceId = $case['licence']['id'];
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
        $case = $this->getCase();

        if (!empty($case['licence']['id'])) {
            $licence = $case['licence']['id'];
        } else {
            $licence = $this->params()->fromRoute('licence', '');
        }

        if (!empty($case['application']['id'])) {
            $application = $case['application']['id'];
        } else {
            $application = $this->params()->fromRoute('application', '');
        }

        if (!empty($case['transportManager']['id'])) {
            $transportManager = $case['transportManager']['id'];
        } else {
            $transportManager = $this->params()->fromRoute('transportManager', '');
        }

        $unwantedOptions = [];

        if (!empty($application)) {

            $unwantedOptions = ['case_t_tm' => '', 'case_t_lic' => '', 'case_t_imp' => ''];

            $form->get('fields')->get('caseType')->setEmptyOption(null);

        } elseif (!empty($transportManager)) {

            $unwantedOptions = ['case_t_imp' => '', 'case_t_app' => '', 'case_t_lic' => ''];

            $form->get('fields')->get('caseType')->setEmptyOption(null);

        } elseif (!empty($licence)) {

            $unwantedOptions = ['case_t_tm' => '', 'case_t_app' => ''];
        }

        $options = $form->get('fields')->get('caseType')->getValueOptions();

        $form->get('fields')->get('caseType')
            ->setValueOptions(array_diff_key($options, $unwantedOptions));

        return $form;
    }
}
