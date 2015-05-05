<?php

/**
 * Dashboard Navigation Trait
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Traits;

/**
 * Dashboard Navigation Trait
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
trait DashboardNavigationTrait
{
    /**
     * Populate tab counts
     *
     * @param int $feeCount optionally pass in the fee count to save calculating it twice
     * @param int $feeCount optionally pass in the correspondence count to save calculating it twice
     */
    protected function populateTabCounts($feeCount = null, $correspondenceCount = null)
    {
        $nav = $this->getServiceLocator()->get('Olcs\Navigation\DashboardNavigation');

        // set fee count on the navigation item
        $navItem = $nav->findOneById('dashboard-fees');
        if (is_null($feeCount)) {
            $feeCount = $this->getFeeCount();
        }
        $navItem->set('count', $feeCount);

        // set correspondence inbox count on the navigation item
        $navItem = $nav->findOneById('dashboard-correspondence');
        if (is_null($correspondenceCount)) {
            $correspondenceCount = $this->getCorrespondenceCount();
        }
        $navItem->set('count', $correspondenceCount);

        return $this;
    }

    /**
     * Get count of outstanding fees to display in nav tab
     */
    protected function getFeeCount()
    {
        $organisationId = $this->getCurrentOrganisationId();
        $fees = $this->getServiceLocator()->get('Entity\Fee')
            ->getOutstandingFeesForOrganisation($organisationId);
        return count($fees);
    }

    /**
     * Get count of unread correspondence inbox messages to display in nav tab
     */
    protected function getCorrespondenceCount()
    {
        return 0;
    }
}
