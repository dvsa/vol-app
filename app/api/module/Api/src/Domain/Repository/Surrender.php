<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;

class Surrender extends AbstractRepository
{
    protected $entity = \Dvsa\Olcs\Api\Entity\Surrender::class;

    public function fetchByLicenceId($licenceId, $hydrateMode = Query::HYDRATE_OBJECT)
    {
        return parent::fetchByX('licence', [$licenceId, $hydrateMode]);
    }

    public function fetchOneByLicenceId($licenceId, $hydrateMode = Query::HYDRATE_OBJECT)
    {
        return parent::fetchOneByX('licence', [$licenceId, $hydrateMode]);
    }
}
