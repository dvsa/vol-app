<?php

namespace Dvsa\Olcs\Api\Entity\Permits\Traits;

use Dvsa\Olcs\Api\Entity\System\RefData;
use RuntimeException;

/**
 * Candidate Permit Creation
 */
trait CandidatePermitCreationTrait
{
    /**
     * Get the mappings of internation journey ref data to multiplier used by the application score calculation
     *
     * @return float
     */
    public function getInternationalJourneysDecimalMap()
    {
        return [
            RefData::INTER_JOURNEY_LESS_60 => 0.3,
            RefData::INTER_JOURNEY_60_90 => 0.75,
            RefData::INTER_JOURNEY_MORE_90 => 1,
        ];
    }

    /**
     * Calculates the application score given intensity of use and international journeys selection
     *
     * @param float $permitIntensityOfUse
     * @param string $internationalJourneysId
     *
     * @return float
     */
    public function calculatePermitApplicationScore($permitIntensityOfUse, $internationalJourneysId)
    {
        $internationalJourneysDecimalMap = $this->getInternationalJourneysDecimalMap();
        return $permitIntensityOfUse * $internationalJourneysDecimalMap[$internationalJourneysId];
    }

    /**
     * Calculates the intensity of use given trips and number of permits
     *
     * @param int trips
     * @param int $numberOfPermits
     *
     * @return float
     */
    public function calculatePermitIntensityOfUse($trips, $numberOfPermits)
    {
        if ($numberOfPermits == 0) {
            throw new RuntimeException('Permit intensity of use cannot be calculated with zero number of permits');
        }

        return $trips / $numberOfPermits;
    }
}
