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
     * bus reg new / variation and cancellation statuses
     *
     * @var array
     */
    protected $newVariationCancellationStatuses = [
        'breg_s_new',
        'breg_s_var',
        'breg_s_cancellation'
    ];

    /**
     * bus reg rejected statuses
     *
     * @var array
     */
    protected $rejectedStatuses = [
        'breg_s_admin',
        'breg_s_refused',
        'breg_s_cancelled',
        'breg_s_withdrawn'
    ];

    /**
     * Memoize Bus Reg details to prevent multiple backend calls with same id
     * @var array
     */
    protected $busRegDetailsCache = [];

    /**
     * returns array of new / variation / cancellation statuses
     *
     * @return array
     */
    public function getNewVariationCancellationStatuses()
    {
        return $this->newVariationCancellationStatuses;
    }

    /**
     * returns array of rejected statuses
     *
     * @return array
     */
    public function getRejectedStatuses()
    {
        return $this->rejectedStatuses;
    }

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
                    ],
                    'withdrawnReason' => [
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
     * Gets the main navigation
     *
     * @return \Zend\Navigation\Navigation
     */
    public function getSidebarNavigation()
    {
        return $this->getServiceLocator()->get('right-sidebar');
    }

    /**
     * Disables the bus reg actions based on the status
     */
    public function disableActions()
    {
        $busReg = $this->getBusReg();

        $newVariationCancellation = $this->getNewVariationCancellationStatuses();

        $newVariationCancellationButtons = [
            'bus-registration-quick-actions-request-withdrawn', //withdrawn
            'bus-registration-decisions-grant', //grant
            'bus-registration-decisions-refuse', //refuse
            'bus-registration-decisions-refuse-by-short-notice' //refuse by short notice
        ];

        $registeredButtons = [
            'bus-registration-quick-actions-create-variation', //create variation
            'bus-registration-quick-actions-create-cancellation', //create cancellation
        ];

        $sidebarNav = $this->getSidebarNavigation();

        //if status is not new, variation or cancellation, disable corresponding nav
        if (!in_array($busReg['status']['id'], $newVariationCancellation)) {
            foreach ($registeredButtons as $navId) {
                $sidebarNav->findById($navId)->setVisible(0);
            }
        } else {
            //status is new, variation or cancelled
            $sidebarNav->findById('bus-registration-decisions-reset-registration')->setVisible(0);

            //Refuse by short notice
            if ($busReg['shortNoticeRefused'] == 'Y' || $busReg['isShortNotice'] == 'N') {
                $sidebarNav->findById('bus-registration-decisions-refuse-by-short-notice')->setVisible(0);
            }
        }

        //if status is not registered, disable corresponding nav
        if ($busReg['status']['id'] != 'breg_s_registered') {
            foreach ($newVariationCancellationButtons as $navId) {
                $sidebarNav->findById($navId)->setVisible(0);
            }

            $sidebarNav->findById('bus-registration-decisions-admin-cancel')->setVisible(0);
        }
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
