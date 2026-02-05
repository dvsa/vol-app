<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ContinuationReview\Section\Stub;

use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\AbstractReviewService as ReviewService;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;

/**
 * Stub class for testing AbstractReviewService
 */
class AbstractReviewServiceStub extends ReviewService
{
    public function getConfigFromData(ContinuationDetail $continuationDetail): void
    {
    }

    #[\Override]
    public function translate(mixed $string): mixed
    {
        return parent::translate($string);
    }
}
