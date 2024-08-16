<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;

/**
 * Review Service Interface
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
interface ReviewServiceInterface
{
    /**
     * Format the readonly config from the given record
     *
     * @param ContinuationDetail $continuationDetail continuation detail
     *
     * @return mixed
     */
    public function getConfigFromData(ContinuationDetail $continuationDetail);
}
