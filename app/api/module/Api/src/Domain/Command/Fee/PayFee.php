<?php

/**
 * Pay Fee (handles fee side effects)
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Fee;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Pay Fee (handles fee side effects)
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class PayFee extends AbstractCommand
{
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
