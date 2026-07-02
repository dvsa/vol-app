<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Fee Belongs To Licence
 *
 * Confirms a fee is attached to a given licence.
 */
class FeeBelongsToLicence extends AbstractBelongsToLicence
{
    protected $repo = 'Fee';
}
