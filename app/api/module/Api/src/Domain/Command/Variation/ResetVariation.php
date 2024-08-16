<?php

/**
 * Reset Variation
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Variation;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Reset Variation
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class ResetVariation extends AbstractCommand
{
    /**
     * @return int
     */
    protected $id;

    /**
     * @return bool
     */
    protected $confirm = false;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function getConfirm(): bool
    {
        return $this->confirm;
    }
}
