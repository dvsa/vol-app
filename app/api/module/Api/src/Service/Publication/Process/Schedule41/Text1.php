<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\Schedule41;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\ProcessInterface;
use Dvsa\Olcs\Api\Service\Publication\Formatter;

/**
 * Schedule41True Text1
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Text1 implements ProcessInterface
{
    private $text = [];

    /**
     * @param PublicationLink      $publicationLink
     * @param ImmutableArrayObject $context
     */
    public function process(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        $this->addOperatingCentreText($publicationLink);

        // Only one S4 is possible on an application, even though DB model allows multiple
        $s4 = $publicationLink->getApplication()->getS4s()->first();
        $this->addTransferredText($s4, $context);

        $this->addClosingText($s4);
        $this->addUpgradeText($publicationLink);

        $publicationLink->setText1(implode("\n", $this->text));
    }

    /**
     * Add text to the
     *
     * @param string $text
     */
    private function addText($text)
    {
        if ($text) {
            $this->text[] = $text;
        }
    }

    /**
     * Add operting centre text
     */
    private function addOperatingCentreText(PublicationLink $publicationLink)
    {
        $this->addText('Operating Centre(s):');

        foreach ($publicationLink->getApplication()->getOperatingCentres() as $aoc) {
            /* @var $aoc \Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre */
            if (!empty($aoc->getS4())) {
                $this->addText(Formatter\Address::format($aoc->getOperatingCentre()->getAddress()));
                $this->addText(Formatter\OcVehicleTrailer::format($aoc));
            }
        }
    }

    /**
     * Add transferred text
     */
    private function addTransferredText(\Dvsa\Olcs\Api\Entity\Application\S4 $s4, ImmutableArrayObject $context)
    {
        $organistionPeople = [];
        foreach ($s4->getLicence()->getOrganisation()->getOrganisationPersons() as $op) {
            $organistionPeople[] = $op->getPerson();
        }

        $this->addText(
            sprintf(
                'Transferred from %s %s (%s) to %s %s (%s).',
                $s4->getLicence()->getLicNo(),
                $s4->getLicence()->getLicenceTypeShortCode(),
                trim(
                    Formatter\OrganisationName::format($s4->getLicence()->getOrganisation())
                    . ' ' .
                    Formatter\People::format(
                        $s4->getLicence()->getOrganisation(),
                        $organistionPeople
                    )
                ),
                $s4->getApplication()->getLicence()->getLicNo(),
                $s4->getApplication()->getLicenceTypeShortCode(),
                trim(
                    Formatter\OrganisationName::format($s4->getApplication()->getLicence()->getOrganisation())
                    . ' ' .
                    Formatter\People::format(
                        $s4->getApplication()->getLicence()->getOrganisation(),
                        $context->offsetGet('applicationPeople')
                    )
                )
            )
        );
    }

    /**
     * Add the closing text
     */
    private function addClosingText(\Dvsa\Olcs\Api\Entity\Application\S4 $s4)
    {
        $text = ($s4->getSurrenderLicence() === 'Y') ?
            '%s has been surrendered as part of this application.' :
            'The operating centre(s) being removed from %s as part of this application.';

        $this->addText(sprintf($text, $s4->getLicence()->getLicNo()));
    }

    /**
     * Add Licence upgrade text
     */
    private function addUpgradeText(PublicationLink $publicationLink)
    {
        if ($publicationLink->getApplication()->isVariation() && $publicationLink->getApplication()->isRealUpgrade()) {
            $this->addText(
                sprintf(
                    'Upgrade of Licence from %s to %s',
                    $publicationLink->getLicence()->getLicenceType()->getDescription(),
                    $publicationLink->getApplication()->getLicenceType()->getDescription()
                )
            );
        }
    }
}
