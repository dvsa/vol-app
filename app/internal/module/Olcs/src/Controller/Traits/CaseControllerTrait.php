<?php

/**
 * Case Controller Trait
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Traits;

use Zend\Mvc\MvcEvent;

/**
 * Case Controller Trait
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
trait CaseControllerTrait
{
    protected $cases = array();

    private $caseInformationBundle = array(
        'children' => array(
            'submissionSections' => array(
                'properties' => array(
                    'id',
                    'description'
                )
            ),
            'legacyOffences' => array(
                'properties' => 'ALL',
            ),
            'caseType' => array(
                'properties' => 'id',
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

    /**
     * @codeCoverageIgnore
     */
    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();

        $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'initialiseData'), 20);
    }

    public function initialiseData(MvcEvent $event)
    {
        $licence = null;

        if (true !== $this->getRequest()->isXmlHttpRequest()) {
            if ($this->params()->fromRoute('case')) {
                $this->setupCase();
                $case = $this->getCase();
                if (array_key_exists('licence', $case) && !empty($case['licence'])) {
                    $licence = $case['licence']['id'];
                    //$this->setupLicence($licence);
                }
                $this->setupMarkers($case);
            }

            if ($licence = $this->params()->fromRoute('licence', $licence)) {
                // get it from the route.
                $this->setupLicence($licence);
            }
        }

        return true;
    }

    public function setupLicence($licence)
    {
        $placeholder = $this->getViewHelperManager()->get('placeholder');
        /** @var \Olcs\Service\Data\Licence $dataService */
        $dataService = $this->getServiceLocator()->get('Olcs\Service\Data\Licence');
        $dataService->setId($licence); //set default licence id for use in forms
        $licence = $dataService->fetchLicenceData($licence);

        $licenceUrl = $this->url()->fromRoute(
            'licence/cases',
            ['licence' => $licence['id']]
        );
        $licenceLink = '<a href="' . $licenceUrl . '">' . $licence['licNo'] . '</a>';

        $placeholder->getContainer('pageTitle')->prepend($licenceLink);
    }

    public function setupCase()
    {
        $placeholder = $this->getViewHelperManager()->get('placeholder');
        $case = $this->getCase();

        $this->getViewHelperManager()->get('headTitle')->prepend('Case ' . $case['id']);

        $placeholder->getContainer('pageTitle')->append('Case ' . $case['id']);
        $placeholder->getContainer('pageSubtitle')->append('Case subtitle');

        $placeholder->getContainer('case')->set($case);
    }

    /**
     * Set up markers as placeholder
     */
    public function setupMarkers($case)
    {
        $placeholder = $this->getViewHelperManager()->get('placeholder');

        $caseMarkerPlugin = $this->getServiceLocator()
            ->get('Olcs\Service\Marker\MarkerPluginManager')
            ->get('Olcs\Service\Marker\CaseMarkers');

        $markers = $caseMarkerPlugin->getStayMarkers(['case' => $case]);

        $placeholder->getContainer('markers')->set($markers);
    }

    /**
     * @return array
     */
    public function getCaseInformationBundle()
    {
        return $this->caseInformationBundle;
    }

    /**
     * Gets the case by ID.
     *
     * @param integer $id
     * @return array
     */
    public function getCase($id = null)
    {
        if (is_null($id)) {
            $id = $this->params()->fromRoute('case');
        }

        if (!isset($this->cases[$id])) {

            $this->cases[$id] = $this->makeRestCall(
                'Cases',
                'GET',
                array('id' => $id),
                $this->getCaseInformationBundle()
            );
        }

        return $this->cases[$id];
    }
}
