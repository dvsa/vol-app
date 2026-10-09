<?php

namespace Dvsa\Olcs\Transfer\Util\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
class ArrayValidator extends Validator
{
}
