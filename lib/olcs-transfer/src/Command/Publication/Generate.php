<?php

/**
 * Generate a publication
 */

namespace Dvsa\Olcs\Transfer\Command\Publication;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/publication/single/generate")
 * @Transfer\Method("PUT")
 */
final class Generate extends AbstractCommand
{
    use FieldType\Identity;
}
