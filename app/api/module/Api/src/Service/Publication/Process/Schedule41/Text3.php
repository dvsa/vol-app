<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\Schedule41;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\ProcessInterface;
use Dvsa\Olcs\Api\Service\Publication\Formatter;

/**
 * Schedule41True Text3
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Text3 implements ProcessInterface
{
    private $text = [];

    /**
     * @param PublicationLink      $publicationLink
     * @param ImmutableArrayObject $context
     */
    public function process(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        $this->addCorrespondanceText($context);
        $this->addTransportManagersText($publicationLink, $context);
        $this->addOperatingCentreText($publicationLink);

        // Only one S4 is possible on an application, even though DB model allows multiple
        $s4 = $publicationLink->getApplication()->getS4s()->first();
        $this->addTcText($s4);
        $this->addClosingText($s4);
        $this->addUpgradeText($publicationLink);

        $publicationLink->setText3(implode("\n", $this->text));
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
     * Add correspondance address
     */
    private function addCorrespondanceText(ImmutableArrayObject $context)
    {
        $this->addText($context->offsetGet('licenceAddress'));
    }

    /**
     * Add operting centre text
     */
    private function addOperatingCentreText(PublicationLink $publicationLink)
    {
        foreach ($publicationLink->getApplication()->getOperatingCentres() as $aoc) {
            /* @var $aoc \Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre */
            if (!empty($aoc->getS4())) {
                $this->addText(
                    'Operating Centre: ' . Formatter\Address::format($aoc->getOperatingCentre()->getAddress())
                );
                $this->addText('Authorisation: ' . Formatter\OcVehicleTrailer::format($aoc));
            }
        }
    }
    /**
     * Add TC text
     */
    private function addTcText(\Dvsa\Olcs\Api\Entity\Application\S4 $s4)
    {
        $text = ($s4->getApplication()->getLicence()->getTrafficArea()->getIsNi()) ?
            "The Department has given a direction under paragraph 2 of Schedule 1(NI) that the above operating "
            . "centre(s) shall be transferred from licence %s held by %s" :
            "The Traffic Commissioner has given a direction under paragraph 2 of Schedule 4 that the above operating "
            . "centre(s) shall be transferred from licence %s held by %s";

        $this->addText(sprintf($text, $s4->getLicence()->getLicNo(), $s4->getLicence()->getOrganisation()->getName()));
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

    /**
     * Add Transport Manager text
     */
    private function addTransportManagersText(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        if (
            $publicationLink->getApplication()->isNew() ||
            ($publicationLink->getApplication()->isVariation() && $publicationLink->getApplication()->isRealUpgrade())
        ) {
            if ($context->offsetExists('applicationTransportManagers')) {
                $this->addText(
                    Formatter\TransportManagers::format($context->offsetGet('applicationTransportManagers'))
                );
            }
        }
    }
}
