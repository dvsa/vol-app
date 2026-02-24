<?php

/**
 * Update
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\CompanySubsidiary\Licence;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\CompanySubsidiary\Modify;

/**
 * Update
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Update extends Modify
{
    /**
     * We should always have a licenceId so we can grab the licence from the licence repo
     *
     * @param $dto
     * @return mixed
     */
    #[\Override]
    protected function getLicence($dto)
    {
        return $this->getRepo('Licence')->fetchById($dto->getLicence());
    }

    /**
     * We should only have licence as context
     *
     * @param $dto
     * @return bool
     */
    #[\Override]
    protected function hasContext($dto)
    {
        return $dto->getLicence() !== null;
    }
}
