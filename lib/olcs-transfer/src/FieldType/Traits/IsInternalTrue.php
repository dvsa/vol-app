<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
trait IsInternalTrue
{
    public function getIsInternal(): bool
    {
        return true;
    }
}
