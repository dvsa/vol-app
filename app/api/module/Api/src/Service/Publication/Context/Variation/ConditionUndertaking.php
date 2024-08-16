<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Variation;

use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Service\Helper\AddressFormatterAwareTrait;
use Dvsa\Olcs\Api\Service\Helper\AddressFormatterAwareInterface;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as ConditionUndertakingEntity;

/**
 * ConditionUndertaking
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class ConditionUndertaking extends AbstractContext implements AddressFormatterAwareInterface
{
    use AddressFormatterAwareTrait;

    /**
     * @param PublicationLink $publicationLink
     * @param \ArrayObject    $context
     *
     * @return \ArrayObject
     */
    public function provide(PublicationLink $publicationLink, \ArrayObject $context)
    {
        // only add data if we are working on certain sections
        if (
            $publicationLink->getPublicationSection()->getId() !== PublicationSection::VAR_NEW_SECTION &&
            $publicationLink->getPublicationSection()->getId() !== PublicationSection::VAR_GRANTED_SECTION
        ) {
            return $context;
        }

        $text = [];
        foreach ($publicationLink->getApplication()->getConditionUndertakings() as $conditionUndertaking) {
             /* @var $conditionUndertaking ConditionUndertakingEntity */
            $text[] = $this->getConditionUndertakingText($conditionUndertaking);
        }

        $context->offsetSet('conditionUndertaking', $text);

        return $context;
    }

    /**
     * Get the ConditionUndertaking text
     *
     *
     * @return string
     */
    private function getConditionUndertakingText(ConditionUndertakingEntity $conditionUndertaking)
    {
        $text = null;
        $text = match ($conditionUndertaking->getAction()) {
            'A' => sprintf(
                'New %s %s. %s',
                $conditionUndertaking->getConditionType()->getDescription(),
                $conditionUndertaking->getNotes(),
                $this->getAttachedToText($conditionUndertaking)
            ),
            'U' => sprintf(
                'Current %s %s. %s. Amended to: %s',
                $conditionUndertaking->getLicConditionVariation()->getConditionType()->getDescription(),
                $conditionUndertaking->getLicConditionVariation()->getNotes(),
                $this->getAttachedToText($conditionUndertaking->getLicConditionVariation()),
                $conditionUndertaking->getNotes()
            ),
            'D' => sprintf(
                '%s to be removed: %s. %s',
                $conditionUndertaking->getConditionType()->getDescription(),
                $conditionUndertaking->getNotes(),
                $this->getAttachedToText($conditionUndertaking)
            ),
            default => $text,
        };

        return $text;
    }

    /**
     * Get the Attached to text
     *
     *
     * @return string
     */
    private function getAttachedToText(ConditionUndertakingEntity $conditionUndertaking)
    {
        $text = null;
        if (
            $conditionUndertaking->getAttachedTo()->getId() ===
            ConditionUndertakingEntity::ATTACHED_TO_OPERATING_CENTRE
        ) {
            $text = 'Attached to Operating: ' .
                $this->getAddressFormatter()->format($conditionUndertaking->getOperatingCentre()->getAddress());
        } elseif ($conditionUndertaking->getAttachedTo()->getId() === ConditionUndertakingEntity::ATTACHED_TO_LICENCE) {
            $text = 'Attached to licence';
        }

        return $text;
    }
}
