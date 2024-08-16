<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can edit irhp app
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CanEditIrhpApplicationWithId extends AbstractCanEditEntity
{
    protected $repo = 'IrhpApplication';
}
