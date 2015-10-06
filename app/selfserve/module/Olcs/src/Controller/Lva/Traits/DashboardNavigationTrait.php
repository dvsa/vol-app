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
     * @param int $feeCount optionally pass in the fee count to save calculating it twice
     * @param int $feeCount optionally pass in the correspondence count to save calculating it twice
     */
    protected function populateTabCounts($feeCount = null, $correspondenceCount = null)
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

    /**
     * Get count of outstanding fees to display in nav tab
     */
    protected function getFeeCount()
    {
        $organisationId = $this->getCurrentOrganisationId();
        $query = OutstandingFees::create(['id' => $organisationId, 'hideExpired' => true]);
        $response = $this->handleQuery($query);

        if ($response->isOk()) {
            return count($response->getResult()['outstandingFees']);
        }
    }

    /**
     * Get count of unread correspondence inbox messages to display in nav tab
     *
     * @return int
     */
    protected function getCorrespondenceCount()
    {
        $organisationId = $this->getCurrentOrganisationId();
        $query = Correspondences::create(['organisation' => $organisationId]);
        $response = $this->handleQuery($query);

        if ($response->isOk()) {
            $correspondence = $response->getResult();
            $count = 0;
            array_walk(
                $correspondence['results'],
                function ($record) use (&$count) {
                    $count = ($record['accessed'] === 'N' ? $count + 1 : $count);
                }
            );
            return $count;
        }
    }
}
