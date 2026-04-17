<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use RuntimeException;

class PermitUsageSelectionGenerator
{
    /**
     * Derive a value to be stored as the permit usage answer for the application
     *
     *
     * @throws RuntimeException
     */
    public function generate(array $requiredPermits)
    {
        $journeyTypes = [];
        foreach ($requiredPermits as $permitUsage => $quantity) {
            $permitUsageComponents = explode('-', (string) $permitUsage);
            $journeyType = $permitUsageComponents[1];
            $journeyTypes[$journeyType] = true;
        }

        $distinctJourneyTypes = array_keys($journeyTypes);
        if (count($distinctJourneyTypes) != 1) {
            throw new RuntimeException('Found zero or multiple journey types in input data');
        }

        return $distinctJourneyTypes[0];
    }
}
