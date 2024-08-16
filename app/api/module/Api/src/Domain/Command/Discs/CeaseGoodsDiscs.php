<?php

/**
 * Cease Goods Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Discs;

use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Cease Goods Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CeaseGoodsDiscs extends AbstractCommand
{
    use Licence;
}
