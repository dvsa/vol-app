<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\Variation;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\ProcessInterface;

/**
 * Variation Text3
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
        $this->addCorrespondanceAddress($context);
        $this->addOperatingCentreText($context);
        $this->addAuthorisationText($context);
        $this->addTransportManagerText($context);
        $this->addConditionUndertakingText($context);
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
     * Add licence correspondance address
     */
    private function addCorrespondanceAddress(ImmutableArrayObject $context)
    {
        if ($context->offsetExists('licenceAddress')) {
            $this->addText($context->offsetGet('licenceAddress'));
        }
    }

    /**
     * @return bool
     */
    private function hasOneOrMoreOperatingCentreLines(ImmutableArrayObject $context)
    {
        return $context->offsetExists('operatingCentres') &&
            is_array($context->offsetGet('operatingCentres')) &&
            !empty($context->offsetGet('operatingCentres'));
    }

    private function addOperatingCentreText(ImmutableArrayObject $context)
    {
        if ($this->hasOneOrMoreOperatingCentreLines($context)) {
            foreach ($context->offsetGet('operatingCentres') as $textLine) {
                $this->addText($textLine);
            }
        }
    }

    private function addAuthorisationText(ImmutableArrayObject $context)
    {
        if ($context->offsetExists('authorisation')) {
            foreach ($context->offsetGet('authorisation') as $authorisationLine) {
                $this->addText($authorisationLine);
            }
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
            $this->addText('Transport Manager(s): ' . implode(', ', $tmNames));
        }
    }

    private function addConditionUndertakingText(ImmutableArrayObject $context)
    {
        if ($context->offsetExists('conditionUndertaking') && is_array($context->offsetGet('conditionUndertaking'))) {
            foreach ($context->offsetGet('conditionUndertaking') as $textLine) {
                $this->addText($textLine);
            }
        }
    }

    /**
     * Add Licence upgrade text
     */
    private function addUpgradeText(PublicationLink $publicationLink)
    {
        if ($publicationLink->getApplication()->isRealUpgrade()) {
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
