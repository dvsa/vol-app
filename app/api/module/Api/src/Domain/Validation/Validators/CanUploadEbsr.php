<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Can upload EBSR validator
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CanUploadEbsr extends AbstractDoesOwnEntity
{
    protected $repo = 'Organisation';

    /**
     * Ebsr files may only be uploaded where the organisation has at least one active PSV licence
     *
     * @param int|null $entityId we normally expect an id there, but we do deal with nulls
     *
     * @return bool
     */
    #[\Override]
    public function isValid($entityId)
    {
        if ($entityId === null) {
            return false;
        }

        /** @var Organisation $organisation */
        $organisation = $this->getEntity($entityId);

        return $organisation->hasActiveLicences(Licence::LICENCE_CATEGORY_PSV);
    }
}
