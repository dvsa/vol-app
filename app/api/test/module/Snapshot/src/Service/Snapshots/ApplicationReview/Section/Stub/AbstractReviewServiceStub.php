<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section\Stub;

use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewService;

/**
 * Stub class for testing AbstractReviewService
 */
class AbstractReviewServiceStub extends AbstractReviewService
{
    public function getConfigFromData(array $data = []): void
    {
    }
}
