<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Edit Licence With Id
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanEditLicenceWithId extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return $this->canEditLicence($this->getId($dto));
    }

    protected function getId($dto)
    {
        return $dto->getId();
    }
}
