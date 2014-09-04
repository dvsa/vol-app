<?php

/**
 * Case Controller Trait
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Traits;

/**
 * Case Controller Trait
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
trait CaseControllerTrait
{
    protected $cases = array();

    /**
     * Get view with case
     *
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    public function getViewWithCase($variables = array())
    {
        $case = $this->getCase();

        $variables['case'] = $case;

        $view = $this->getView($variables);

        $this->getViewHelperManager()->get('headTitle')->prepend('Case ' . $this->getIdentifier());

        $pageTitleHelper = $this->getViewHelperManager()->get('pageTitle');
        $pageTitleHelper->append('Case ' . $this->getIdentifier());

        $pageSubtitleHelper = $this->getViewHelperManager()->get('pageSubtitle');
        $pageSubtitleHelper->append('Case subtitle');

        return $view;
    }

    public function getTranslator()
    {
        return $this->getServiceLocator()->get('translator');
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
