<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;

/**
 * Builds the context array the bookmark system needs to resolve [[GRABS]] for a letter.
 *
 * Shared by rendering and by generation-time resolution so both see the same entities.
 */
class VolGrabContextBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(LetterInstance $letterInstance, ?bool $isNiOverride = null): array
    {
        $context = array_filter([
            'licence' => $letterInstance->getLicence()?->getId(),
            'application' => $letterInstance->getApplication()?->getId(),
            'user' => $letterInstance->getCreatedBy()?->getId(),
            'case' => $letterInstance->getCase()?->getId(),
            'busRegId' => $letterInstance->getBusReg()?->getId(),
            'organisation' => $letterInstance->getOrganisation()?->getId(),
        ]);

        // false is a real value here (GB letter), so it sits outside the array_filter
        $context['isNi'] = $isNiOverride ?? (bool) ($letterInstance->getLicence()?->isNi() ?? false);

        return $context;
    }
}
