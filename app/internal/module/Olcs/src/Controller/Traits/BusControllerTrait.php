<?php

/**
 * Bus Controller Trait
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Traits;

/**
 * Bus Controller Trait
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait BusControllerTrait
{
    /**
     * Get view with licence
     *
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    public function getViewWithBusReg($variables = array())
    {
        $busReg = $this->getBusReg();

        $variables['busReg'] = $busReg;

        $view = $this->getView($variables);

        $this->pageTitle = $busReg['regNo'];
        $this->pageSubTitle = $busReg['licence']['organisation']['name'] . ', Variation ' .
            $busReg['variationNo']
            . ', ' . $busReg['status']['description'];

        return $view;
    }

    /**
     * Gets the Bus Registration record
     *
     * @param int $id
     * @return array
     */
    public function getBusReg($id = null)
    {
        if (is_null($id)) {
            $id = $this->getFromRoute('busRegId');
        }

        $bundle = [
            'children' => [
                'licence' => [
                    'properties' => 'ALL',
                    'children' => [
                        'organisation'
                    ]
                ],
                'status' => [
                    'properties' => 'ALL'
                ]
            ]
        ];

        return $this->makeRestCall('BusReg', 'GET', array('id' => $id, 'bundle' => json_encode($bundle)));
    }

    /**
     * @param int $id
     * @return bool
     */
    public function isFromEbsr($id = null)
    {
        if (is_null($id)) {
            $id = $this->getFromRoute('busRegId');
        }

        $ebsr = $this->makeRestCall('EbsrSubmission', 'GET', array('busReg' => $id));

        return (bool)$ebsr['Count'];
    }

    /**
     * Gets the main navigation
     *
     * @return \Zend\Navigation\Navigation
     */
    public function getNavigation()
    {
        return $this->getServiceLocator()->get('Navigation');
    }

    /**
     * Gets the sub navigation
     *
     * @return \Zend\Navigation\Page\Mvc
     */
    public function getSubNavigation()
    {
        return $this->getNavigation()->findOneBy('id', $this->getSubNavRoute());
    }

    /**
     * Returns the sub nav route
     *
     * @return string
     */
    public function getSubNavRoute()
    {
        return $this->subNavRoute;
    }

    /**
     * Returns the section
     *
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Returns the menu item
     *
     * @return string
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Returns the layout file
     *
     * @return string
     */
    public function getLayoutFile()
    {
        return $this->layoutFile;
    }
}
