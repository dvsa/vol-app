<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\Application;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection;
use Dvsa\Olcs\Api\Service\Publication\Formatter;

/**
 * Class Text3
 * @package Dvsa\Olcs\Api\Service\Publication\Process\Application
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class Text3 extends \Dvsa\Olcs\Api\Service\Publication\Process\AbstractText
{
    public function process(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        if (
            $publicationLink->getPublicationSection()->getId() !== PublicationSection::APP_NEW_SECTION &&
            $publicationLink->getPublicationSection()->getId() !== PublicationSection::APP_GRANTED_SECTION
        ) {
            // if not in New (received) or granted section then no text
            $publicationLink->setText3(null);
            return;
        }

        $this->addLicenceAddressText($context);
        $this->addOperatingCentreText($publicationLink);
        $this->addAuthorisationText($context, $publicationLink);
        $this->addTransportManagerText($context);
        $this->addConditionsAndUndertakingsText($context);

        $publicationLink->setText3($this->getTextWithNewLine());
    }

    /**
     * Add the licence address to the internally stored collection of text lines
     */
    private function addLicenceAddressText(ImmutableArrayObject $context)
    {
        $this->addTextLine($context->offsetGet('licenceAddress'));
    }

    private function addOperatingCentreText(PublicationLink $publicationLink)
    {
        $useHgvCaption = $publicationLink->getApplication()->isVehicleTypeMixedWithLgv();

        foreach ($this->getApplicationOperatingCentres($publicationLink) as $aoc) {
            $this->addTextLine(
                'Operating Centre: ' . Formatter\Address::format($aoc->getOperatingCentre()->getAddress())
            );
            $this->addTextLine('Authorisation: ' . Formatter\OcVehicleTrailer::format($aoc, $useHgvCaption));
        }
    }

    private function addAuthorisationText(ImmutableArrayObject $context, PublicationLink $publicationLink)
    {
        if ($context->offsetExists('authorisation')) {
            if (count($this->getApplicationOperatingCentres($publicationLink)) > 0) {
                $this->addLicenceAddressText($context);
            }
            $this->addTextLine($context->offsetGet('authorisation'));
        }
    }

    private function addTransportManagerText(ImmutableArrayObject $context)
    {
        if ($context->offsetExists('applicationTransportManagers')) {
            $tmNames = [];
            foreach ($context->offsetGet('applicationTransportManagers') as $transportManager) {
                /* @var $transportManager \Dvsa\Olcs\Api\Entity\Tm\TransportManager */
                $tmNames[] = $transportManager->getHomeCd()->getPerson()->getFullName();
            }
            $this->addTextLine('Transport Manager(s): ' . implode(', ', $tmNames));
        }
    }

    private function addConditionsAndUndertakingsText(ImmutableArrayObject $context)
    {
        $textLines = $context->offsetGet('conditionUndertaking');
        foreach ($textLines as $text) {
            $this->addTextLine($text);
        }
    }

    /**
     * Return a list of the operating centres that need to be listed within the publication summary
     *
     *
     * @return array
     */
    private function getApplicationOperatingCentres(PublicationLink $publicationLink)
    {
        $applicationOperatingCentres = [];

        foreach ($publicationLink->getApplication()->getOperatingCentres() as $aoc) {
            /* @var $aoc \Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre */

            // don't include OC's that are part of an S4
            if (!empty($aoc->getS4()) && !$publicationLink->getPublicationSection()->isDecisionSection()) {
                continue;
            }

            $applicationOperatingCentres[] = $aoc;
        }

        return $applicationOperatingCentres;
    }
}
