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
        // append fee count to the navigation label
        if (is_null($feeCount)) {
            $feeCount = $this->getFeeCount();
        }
        $navItem = $this->getServiceLocator()->get('Olcs\Navigation\DashboardNavigation')
            ->findOneById('dashboard-fees');
        $navItem->setLabel($navItem->getLabel().' ('.$feeCount.')');

        // append correspondence inbox count to the navigation label
        if (is_null($correspondenceCount)) {
            $correspondenceCount = $this->getCorrespondenceCount();
        }
        $navItem = $this->getServiceLocator()->get('Olcs\Navigation\DashboardNavigation')
            ->findOneById('dashboard-correspondence');
        $navItem->setLabel($navItem->getLabel().' ('.$correspondenceCount.')');

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
