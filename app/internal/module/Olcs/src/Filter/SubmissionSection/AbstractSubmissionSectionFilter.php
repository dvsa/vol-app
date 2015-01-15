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

    protected function calculateOor($application)
    {
        if (isset($application['operatingCentres'][0]['adPlacedDate'])) {
            $operatingCentres = $application['operatingCentres'];
            rsort($operatingCentres);

            $appDateObj = new \DateTime($application['receivedDate']);
            $appDateObj->setTime(0, 0, 0); //is from a datetime db field - stop the time affecting the 21 day calculation
            $newsDateObj = new \DateTime($operatingCentres[0]['adPlacedDate']);

            if ($appDateObj <= $newsDateObj) {
                $dateProcessor = $this->getServiceLocator()->getServiceLocator()->get('Common\Util\DateTimeProcessor');
                return $dateProcessor->calculateDate($newsDateObj, 21, false, false);
            }
        }
        return null;
    }

    protected function calculateOoo($application)
    {
        $latestPublication = $this->getLatestPublication($application);

        if (isset($latestPublication['pubDate'])) {
            $pubDateObj = new \DateTime($latestPublication['pubDate']);

            $dateProcessor = $this->getServiceLocator()->getServiceLocator()->get('Common\Util\DateTimeProcessor');
            return $dateProcessor->calculateDate($pubDateObj, 21, false, false);
        }
        return null;
    }

    private function getLatestPublication($application)
    {
        if (isset($application['publicationLinks'])) {
            $publications = array_column($application['publicationLinks'], 'publication');
            usort(
                $publications,
                function ($a, $b) {
                    return strtotime($b['pubDate']) - strtotime($a['pubDate']);
                }
            );
            return $publications[0];
        }
        return null;
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
