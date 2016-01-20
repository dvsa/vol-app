<?php

/**
 * Dashboard Navigation Trait
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Traits;

use Dvsa\Olcs\Transfer\Query\Organisation\OutstandingFees;
use Dvsa\Olcs\Transfer\Query\Correspondence\Correspondences;

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
     * @param int $feeCount
     * @param int $correspondenceCount
     */
    protected function populateTabCounts($feeCount, $correspondenceCount)
    {
        $nav = $this->getServiceLocator()->get('navigation');

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
}
