<?php

namespace Olcs\Filter\SubmissionSection;

use Zend\Filter\AbstractFilter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\Util\DateTimeProcessor;

/**
 * Class AbstractSubmissionSectionFilter
 * @package Olcs\Filter\SubmissionSection
 */
class AbstractSubmissionSectionFilter extends AbstractFilter implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Calculates the vehicles in possession.
     *
     * @param array $licenceData
     * @return int
     */
    protected function calculateVehiclesInPossession($licenceData)
    {
        $vehiclesInPossession = 0;

        if (isset($licenceData['licenceVehicles']) && is_array($licenceData['licenceVehicles'])) {
            foreach ($licenceData['licenceVehicles'] as $vehicle) {
                if (!empty($vehicle['specifiedDate']) && empty($vehicle['deletedDate'])) {
                    $vehiclesInPossession++;
                }
            }
        }
        return $vehiclesInPossession;
    }

    /**
     * Calculates the trailers in possession. At present, no entity seems to have this information
     *
     * @param array $licenceData
     * @return int
     */
    protected function calculateTrailersInPossession($licenceData)
    {
        return $licenceData['totAuthTrailers'];
    }

    /**
     * Method should be overridden
     * @codeCoverageIgnore This method should be overridden
     *
     * @param mixed $value
     * @return mixed|void
     */
    public function filter($value)
    {
    }
}
