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
     * Memoize Bus Reg details to prevent multiple backend calls with same id
     * @var array
     */
    protected $busRegDetailsCache = [];

    /**
     * Get view with Bus Registration
     *
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    public function getViewWithBusReg($variables = array())
    {
        $busReg = $this->getBusReg();

        $variables['busReg'] = $busReg;

        $view = $this->getView($variables);

        return $view;
    }

    /**
     * Gets the Bus Registration record
     *
     * @param int $id
     * @return array
     */
    public function getBusReg($id = null, $bypassCache = false)
    {
        if (is_null($id)) {
            $id = $this->getFromRoute('busRegId');
        }

        if ($bypassCache || !isset($this->busRegDetailsCache[$id])) {
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
            $this->busRegDetailsCache[$id] = $this->makeRestCall(
                'BusReg',
                'GET', array('id' => $id, 'bundle' => json_encode($bundle))
            );
        }
        return $this->busRegDetailsCache[$id];
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
