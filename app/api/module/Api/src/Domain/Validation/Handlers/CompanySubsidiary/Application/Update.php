<?php

/**
 * Update
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\CompanySubsidiary\Application;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\CompanySubsidiary\Modify;

/**
 * Update
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Update extends Modify
{
    /**
     * We have to grab the licence from the application record
     *
     * @param $dto
     * @return mixed
     */
    #[\Override]
    protected function getLicence($dto)
    {
        $application = $this->getRepo('Application')->fetchById($dto->getApplication());

        return $application->getLicence();
    }

    /**
     * We should only have application as context
     *
     * @param $dto
     * @return bool
     */
    #[\Override]
    protected function hasContext($dto)
    {
        return $dto->getApplication() !== null;
    }
}
