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
        $licence = null;

        if (true !== $this->getRequest()->isXmlHttpRequest()) {

            $placeholder = $this->getViewHelperManager()->get('placeholder');

            if ($this->params()->fromRoute('case')) {

                $case = $this->getCase();

                $this->getViewHelperManager()->get('headTitle')->prepend('Case ' . $case['id']);

                $placeholder->getContainer('pageTitle')->append('Case ' . $case['id']);
                $placeholder->getContainer('pageSubtitle')->append('Case subtitle');

                $placeholder->getContainer('case')->set($case);

                // Takes care of when a case is connected to a licence.
                if (array_key_exists('licence', $case) && !empty($case['licence'])) {
                    $licence = $case['licence']['id'];
                }
            }

            if ($licence = $this->params()->fromRoute('licence', $licence)) {

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
        }

        return true;
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
