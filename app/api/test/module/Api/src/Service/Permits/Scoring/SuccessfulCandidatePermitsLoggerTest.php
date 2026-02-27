<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Scoring;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Scoring\SuccessfulCandidatePermitsLogger;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * SuccessfulCandidatePermitsLoggerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SuccessfulCandidatePermitsLoggerTest extends MockeryTestCase
{
    public function testLog(): void
    {
        $candidatePermit1Id = 1;
        $candidatePermit1EmissionsCategory = RefData::EMISSIONS_CATEGORY_EURO6_REF;

        $candidatePermit2Id = 3;
        $candidatePermit2EmissionsCategory = RefData::EMISSIONS_CATEGORY_EURO5_REF;

        $candidatePermit3Id = 8;
        $candidatePermit3EmissionsCategory = RefData::EMISSIONS_CATEGORY_EURO6_REF;

        $candidatePermits = [
            [
                'id' => $candidatePermit1Id,
                'emissions_category' => $candidatePermit1EmissionsCategory
            ],
            [
                'id' => $candidatePermit2Id,
                'emissions_category' => $candidatePermit2EmissionsCategory
            ],
            [
                'id' => $candidatePermit3Id,
                'emissions_category' => $candidatePermit3EmissionsCategory
            ],
        ];

        $result = new Result();
        $successfulCandidatePermitsLogger = new SuccessfulCandidatePermitsLogger();
        $successfulCandidatePermitsLogger->log($candidatePermits, $result);

        $expectedMessages = [
            '      The following 3 permits will be marked as successful:',
            '        - id = 1, assigned category = emissions_cat_euro6',
            '        - id = 3, assigned category = emissions_cat_euro5',
            '        - id = 8, assigned category = emissions_cat_euro6',
        ];

        $this->assertEquals(
            $expectedMessages,
            $result->getMessages()
        );
    }
}
