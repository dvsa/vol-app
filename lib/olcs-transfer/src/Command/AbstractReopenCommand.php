<?php

namespace Dvsa\Olcs\Transfer\Command;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType as FieldType;

/**
 * AbstractReopenCommand class
 */
abstract class AbstractReopenCommand extends AbstractCommand implements FieldType\IdentityInterface
{
    use FieldType\Traits\Identity;
}
