<?php

/**
 * Create a Replacement
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Transfer\Command\Replacement;

use Dvsa\Olcs\Transfer\FieldType\Traits\Placeholder;
use Dvsa\Olcs\Transfer\FieldType\Traits\ReplacementText;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/replacement")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    use Placeholder;
    use ReplacementText;
}
