<?php

namespace Dvsa\Olcs\Transfer\Command;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType as FieldType;

/**
 * AbstractDeleteCommand class
 */
abstract class AbstractDeleteCommand extends AbstractCommand implements FieldType\IdentityInterface
{
    use FieldType\Traits\Identity;
}
