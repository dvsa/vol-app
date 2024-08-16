<?php

/**
 * Generate a cache based on the cache type and optional unique id
 */

namespace Dvsa\Olcs\Api\Domain\Command\Cache;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\IdentityString;
use Dvsa\Olcs\Transfer\FieldType\Traits\UniqueIdStringOptional;

/**
 * Generate a cache based on the cache type and optional unique id
 */
final class Generate extends AbstractCommand
{
    use IdentityString;
    use UniqueIdStringOptional;
}
