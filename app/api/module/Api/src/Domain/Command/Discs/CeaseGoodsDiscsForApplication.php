<?php

namespace Dvsa\Olcs\Api\Domain\Command\Discs;

use Dvsa\Olcs\Transfer\FieldType\Traits\Application;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Cease Goods Discs for an Application
 */
final class CeaseGoodsDiscsForApplication extends AbstractCommand
{
    use Application;
}
