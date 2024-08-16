<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can Access TransportManagerLicence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CanAccessTransportManagerLicence extends AbstractCanAccessEntity
{
    protected $repo = 'TransportManagerLicence';
}
