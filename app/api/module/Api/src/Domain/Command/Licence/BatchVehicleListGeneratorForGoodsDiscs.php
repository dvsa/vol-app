<?php

/**
 * Batch Vehicle List Generator for Goods Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Licence;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Batch Vehicle List Generator for Goods Discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class BatchVehicleListGeneratorForGoodsDiscs extends AbstractCommand
{
    protected $licences = [];

    protected $user;

    /**
     * @return mixed
     */
    public function getLicences()
    {
        return $this->licences;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}
