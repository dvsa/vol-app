<?php

/**
 * Is Owner
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * Is Owner
 *
 * Check whether the organisation that belongs to the current user, matches an organisation that is linked to the
 * entity
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class IsOwner extends AbstractValidator implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * Check whether the organisation that belongs to the current user, matches an organisation that is linked to the
     * entity
     *
     * @param OrganisationProviderInterface $entity Entity implementing organisation provider interface
     *
     * @return bool
     */
    public function isValid(OrganisationProviderInterface $entity)
    {
        $currentUserOrg = $this->getCurrentUser()->getRelatedOrganisation();

        // This is needed as if user has no organisation and entity has no organisation they would be granted access
        if (empty($currentUserOrg)) {
            return false;
        }

        $relatedOrganisations = $entity->getRelatedOrganisation();

        if (empty($relatedOrganisations)) {
            return false;
        }

        // The entity may be linked to multiple organisation, so we can just cast as an array and check that one matches
        if (!is_array($relatedOrganisations)) {
            $relatedOrganisations = [$relatedOrganisations];
        }

        foreach ($relatedOrganisations as $relatedOrganisation) {
            if ($currentUserOrg === $relatedOrganisation) {
                return true;
            }
        }

        return false;
    }
}
