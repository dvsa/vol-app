<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Variation;

use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Service\Helper\AddressFormatterAwareTrait;
use Dvsa\Olcs\Api\Service\Helper\AddressFormatterAwareInterface;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;

/**
 * OperatingCentres
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class OperatingCentres extends AbstractContext implements AddressFormatterAwareInterface
{
    use AddressFormatterAwareTrait;

    public const string INCREASE = 'Increase';
    public const string DECREASE = 'Decrease';

    /**
     * @param PublicationLink $publicationLink
     * @param \ArrayObject    $context
     *
     * @return \ArrayObject
     */
    public function provide(PublicationLink $publicationLink, \ArrayObject $context)
    {
        $text = [];
        foreach ($publicationLink->getApplication()->getOperatingCentres() as $aoc) {
            /* @var $aoc ApplicationOperatingCentre */
            // don't include OC's that are part of an S4
            if (!empty($aoc->getS4())) {
                continue;
            }

            $ocText = $this->getOperatingCentreText($aoc);
            if ($ocText !== null) {
                $text[] = $ocText;
            }
        }

        $context->offsetSet('operatingCentres', $text);

        return $context;
    }

    /**
     * Get the OperatingCentre text
     *
     *
     * @return string
     */
    private function getOperatingCentreText(ApplicationOperatingCentre $aoc)
    {
        $text = null;
        switch ($aoc->getAction()) {
            case 'A':
                $text = sprintf(
                    "New operating centre: %s\nNew authorisation at this operating centre will be: %s",
                    $this->getAddressFormatter()->format($aoc->getOperatingCentre()->getAddress()),
                    $this->getVehicleTrailerText($aoc)
                );
                break;
            case 'U':
                $increaseDecreaseText = $this->getIncreaseDecreaseText($aoc);
                if ($increaseDecreaseText === null) {
                    return null;
                }
                $text = sprintf(
                    "%s at existing operating centre: %s\nNew authorisation at this operating centre will be: %s",
                    $increaseDecreaseText,
                    $this->getAddressFormatter()->format($aoc->getOperatingCentre()->getAddress()),
                    $this->getVehicleTrailerText($aoc)
                );
                break;
            case 'D':
                $text = sprintf(
                    'Removed operating centre: %s',
                    $this->getAddressFormatter()->format($aoc->getOperatingCentre()->getAddress())
                );
                break;
        }

        return $text;
    }

    /**
     * @return string
     */
    private function getVehicleTrailerText(ApplicationOperatingCentre $aoc)
    {
        $text = [];
        if ((int) $aoc->getNoOfVehiclesRequired() > 0) {
            $suffix = ' vehicle(s)';
            if ($aoc->getApplication()->isVehicleTypeMixedWithLgv()) {
                $suffix = ' Heavy goods vehicle(s)';
            }
            $text[] = $aoc->getNoOfVehiclesRequired() . $suffix;
        }
        if ((int) $aoc->getNoOfTrailersRequired() > 0) {
            $text[] = $aoc->getNoOfTrailersRequired() . ' trailer(s)';
        }

        return implode(', ', $text);
    }

    /**
     * Calculate if the update is an increase or a decrease and return the string to insert into text
     *
     *
     * @return string|null null returned if no change
     */
    private function getIncreaseDecreaseText(ApplicationOperatingCentre $aoc)
    {
        $loc = $this->getLicenceOperatingCentre($aoc);
        if ($loc === null) {
            return null;
        }

        if ($aoc->getNoOfVehiclesRequired() > $loc->getNoOfVehiclesRequired()) {
            return  self::INCREASE;
        }
        if ($aoc->getNoOfVehiclesRequired() < $loc->getNoOfVehiclesRequired()) {
            return  self::DECREASE;
        }
        if ($aoc->getNoOfTrailersRequired() > $loc->getNoOfTrailersRequired()) {
            return  self::INCREASE;
        }
        if ($aoc->getNoOfTrailersRequired() < $loc->getNoOfTrailersRequired()) {
            return  self::DECREASE;
        }

        return null;
    }

    /**
     * Get the Licence Operating Centre, that the ApplicationOperatingCentre is an update to
     *
     *
     * @return LicenceOperatingCentre
     */
    private function getLicenceOperatingCentre(ApplicationOperatingCentre $aoc)
    {
        foreach ($aoc->getApplication()->getLicence()->getOperatingCentres() as $loc) {
            /* @var $loc LicenceOperatingCentre */
            if ($loc->getOperatingCentre()->getId() === $aoc->getOperatingCentre()->getId()) {
                return $loc;
            }
        }

        // this shouldn't be possible, as an updated aoc has to have an loc
        return null;
    }
}
