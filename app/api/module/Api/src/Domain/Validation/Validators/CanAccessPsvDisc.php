<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can Access PsvDisc
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanAccessPsvDisc extends AbstractCanAccessEntity
{
    protected $repo = 'PsvDisc';
}
