<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Traits;

use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;

class StubGetSetDatePropertiesTrait
{
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use ProcessDateTrait;
    use SoftDeletableTrait;
}
