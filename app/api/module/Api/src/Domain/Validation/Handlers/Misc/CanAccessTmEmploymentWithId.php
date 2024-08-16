<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Entity\Tm\TmEmployment;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Can Access TmEmployment With ID
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanAccessTmEmploymentWithId extends AbstractHandler
{
    /**
     * Is valid
     *
     * @param CommandInterface $dto dto
     *
     * @return bool
     */
    public function isValid($dto)
    {
        return $this->canAccessTmEmployment($this->getId($dto));
    }

    /**
     * Get id
     *
     * @param TmEmployment $dto dto
     *
     * @return int
     */
    protected function getId($dto)
    {
        return $dto->getId();
    }
}
