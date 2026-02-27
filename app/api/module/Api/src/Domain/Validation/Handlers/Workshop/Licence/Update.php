<?php

/**
 * Update
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Workshop\Licence;

/**
 * Update
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Update extends Modify
{
    #[\Override]
    protected function getWorkshops($dto)
    {
        return [$this->getRepo('Workshop')->fetchById($dto->getId())];
    }
}
