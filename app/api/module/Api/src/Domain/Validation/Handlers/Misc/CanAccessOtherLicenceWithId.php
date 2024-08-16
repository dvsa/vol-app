<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access OtherLicence With ID
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanAccessOtherLicenceWithId extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return $this->canAccessOtherLicence($this->getId($dto));
    }

    protected function getId($dto)
    {
        return $dto->getId();
    }
}
