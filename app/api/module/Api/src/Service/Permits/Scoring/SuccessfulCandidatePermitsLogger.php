<?php

namespace Dvsa\Olcs\Api\Service\Permits\Scoring;

use Dvsa\Olcs\Api\Domain\Command\Result;

class SuccessfulCandidatePermitsLogger
{
    /**
     * Logs a list of successful candidate permits to a CQRS result object
     */
    public function log(array $candidatePermits, Result $result)
    {
        $result->addMessage('      The following ' . count($candidatePermits) . ' permits will be marked as successful:');
        foreach ($candidatePermits as $candidatePermit) {
            $result->addMessage(
                sprintf(
                    '        - id = %d, assigned category = %s',
                    $candidatePermit['id'],
                    $candidatePermit['emissions_category']
                )
            );
        }
    }
}
