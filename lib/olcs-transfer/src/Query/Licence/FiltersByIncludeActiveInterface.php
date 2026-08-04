<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Query\Licence;

interface FiltersByIncludeActiveInterface
{
    /**
     * @return mixed
     */
    public function getIncludeActive();
}
