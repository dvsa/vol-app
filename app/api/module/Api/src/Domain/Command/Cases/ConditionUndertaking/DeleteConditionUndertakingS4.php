<?php

/**
 * DeleteConditionUndertaking.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Cases\ConditionUndertaking;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Class DeleteConditionUndertaking
 *
 * Delete condition undertaking.
 *
 * @package Dvsa\Olcs\Api\Domain\Command\Discs
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class DeleteConditionUndertakingS4 extends AbstractCommand
{
    protected $s4 = null;

    /**
     * @return null
     */
    public function getS4()
    {
        return $this->s4;
    }
}
