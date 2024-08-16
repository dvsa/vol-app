<?php

namespace Dvsa\Olcs\Api\Service\Publication\Formatter;

use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;

/**
 * OcVehicleTrailer
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OcVehicleTrailer
{
    /**
     * Return a string representing the vehicle and/or trailer count relating to the provided operating centre
     *
     * @param bool $useHgvCaption (defaults to false)
     * @return string
     */
    public static function format(ApplicationOperatingCentre $aoc, $useHgvCaption = false)
    {
        $vehicleCaption = 'vehicle(s)';
        if ($useHgvCaption) {
            $vehicleCaption = 'Heavy goods vehicle(s)';
        }

        $text = [];
        if ((int) $aoc->getNoOfVehiclesRequired() > 0) {
            $text[] = $aoc->getNoOfVehiclesRequired() . ' ' . $vehicleCaption;
        }
        if ((int) $aoc->getNoOfTrailersRequired() > 0) {
            $text[] = $aoc->getNoOfTrailersRequired() . ' trailer(s)';
        }

        return implode(', ', $text);
    }
}
