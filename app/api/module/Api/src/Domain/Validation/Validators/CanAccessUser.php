<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can Access User
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CanAccessUser extends AbstractCanAccessEntity
{
    protected $repo = 'User';
}
