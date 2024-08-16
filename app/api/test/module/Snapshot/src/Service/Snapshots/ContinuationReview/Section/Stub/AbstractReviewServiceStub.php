<?php

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ContinuationReview\Section\Stub;

use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\AbstractReviewService as ReviewService;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;

/**
 * Stub class for testing AbstractReviewService
 */
class AbstractReviewServiceStub extends ReviewService
{
    public function getConfigFromData(ContinuationDetail $continuationDetail)
    {
    }

    public function translate($string)
    {
        return parent::translate($string);
    }
}
