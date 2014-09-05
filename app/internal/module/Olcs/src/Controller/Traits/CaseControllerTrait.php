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

    protected function attachDefaultListeners()
    {
        parent::attachDefaultListeners();

        $this->getEventManager()->attach(MvcEvent::EVENT_DISPATCH, array($this, 'initialiseData'), 20);
    }

    public function initialiseData(MvcEvent $event)
    {
        $case = $this->getCase();

        $this->getViewHelperManager()->get('headTitle')->prepend('Case ' . $this->getIdentifier());
        $this->getViewHelperManager()->get('pageTitle')->append('Case ' . $this->getIdentifier());
        $this->getViewHelperManager()->get('pageSubtitle')->append('Case subtitle');

        $this->getViewHelperManager()->get('placeholder')->getContainer('case')->set($case);

        if ($licenceId = $this->fromRoute('licence')) {

            $licence = $this->makeRestCall('Licence', 'GET', array('id' => $licenceId));
            $licenceUrl = $this->url()->fromRoute('licence/details/overview', ['licence' => $licenceId]);

            $this->getViewHelperManager()
                ->get('pageTitle')->setAutoEscape(false)
                ->prepend('<a href="' . $licenceUrl . '">' . $licence['licNo'] . '</a>');
        }

        return true;
    }

    /**
     * Really useful method that gets us the view helper manager
     * from the service locator.
     *
     * @return ViewHelperManager
     */
    public function getViewHelperManager()
    {
        return $this->getServiceLocator()->get('viewHelperManager');
    }

    /**
     * Gets the licence by ID.
     *
     * @param integer $id
     * @return array
     */
    public function getCase($id = null)
    {
        if (is_null($id)) {
            $id = $this->getIdentifier();
        }

        if (!isset($this->cases[$id])) {
            $bundle = array(
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

            $this->cases[$id] = $this->makeRestCall('Cases', 'GET', array('id' => $id), $bundle);
        }

        return $this->cases[$id];
    }
}
