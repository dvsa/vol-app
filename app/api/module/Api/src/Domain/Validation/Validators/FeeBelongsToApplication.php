<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Fee Belongs To Application
 *
 * Confirms a fee is attached to a given application.
 */
class FeeBelongsToApplication extends AbstractBelongsToApplication
{
    protected $repo = 'Fee';
}
