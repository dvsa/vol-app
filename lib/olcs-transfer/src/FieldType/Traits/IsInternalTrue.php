<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

trait IsInternalTrue
{
    public function getIsInternal(): bool
    {
        return true;
    }
}
