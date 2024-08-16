<?php

/**
 * Version Conflict Exception
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Exception;

/**
 * Version Conflict Exception
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VersionConflictException extends Exception
{
    protected $messages = [
        'VER_CONF' => 'The resource you are editing is out of date'
    ];
}
