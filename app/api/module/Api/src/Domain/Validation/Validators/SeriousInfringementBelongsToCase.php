<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Serious Infringement Belongs To Case
 *
 * Confirms a serious infringement is attached to a given case.
 */
class SeriousInfringementBelongsToCase extends AbstractBelongsToCase
{
    protected $repo = 'SeriousInfringement';
}
