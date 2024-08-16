<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;

class ForCpProvider
{
    /**
     * Create service instance
     *
     *
     * @return ForCpProvider
     */
    public function __construct(private readonly ForCpWithCountriesProvider $forCpWithCountriesProvider, private readonly ForCpWithNoCountriesProvider $forCpWithNoCountriesProvider, private readonly EntityIdsExtractor $entityIdsExtractor, private readonly RangeSubsetGenerator $rangeSubsetGenerator)
    {
    }

    /**
     * Returns the best fitting range for an application with the specified countries
     *
     * @param IrhpCandidatePermit $irhpCandidatePermit
     *
     * @return IrhpPermitRange
     */
    public function selectRange(
        Result $result,
        IrhpCandidatePermit $candidatePermit,
        array $ranges
    ) {
        $applicationCountries = $candidatePermit->getIrhpPermitApplication()
            ->getIrhpApplication()
            ->getCountrys()
            ->getValues();

        $rangeSubset = $this->rangeSubsetGenerator->generate($candidatePermit, $ranges);

        if (count($applicationCountries) > 0) {
            $applicationCountryIds = $this->entityIdsExtractor->getExtracted($applicationCountries);

            $result->addMessage('    - has one or more countries: ' . implode(', ', $applicationCountryIds));

            $range = $this->forCpWithCountriesProvider->selectRange(
                $result,
                $rangeSubset,
                $applicationCountryIds
            );
        } else {
            $result->addMessage('    - has no countries');
            $range = $this->forCpWithNoCountriesProvider->selectRange($result, $rangeSubset);
        }

        return $range['entity'];
    }
}
