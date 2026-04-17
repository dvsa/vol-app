<?php

/**
 * Delete
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\CompanySubsidiary\Application;

/**
 * Delete
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Delete extends Update
{
    /**
     * Delete command has multiple ids
     *
     * @param $dto
     * @return mixed
     */
    #[\Override]
    public function getIds($dto)
    {
        return $dto->getIds();
    }
}
