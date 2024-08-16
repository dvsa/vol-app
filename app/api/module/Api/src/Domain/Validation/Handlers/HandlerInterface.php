<?php

/**
 * Handler Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Handler Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface HandlerInterface
{
    /**
     * @param CommandInterface|QueryInterface $dto
     *
     * @return boolean
     */
    public function isValid($dto);
}
