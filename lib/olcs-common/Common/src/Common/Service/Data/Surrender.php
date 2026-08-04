<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Dvsa\Olcs\Transfer\Query\Surrender\ByLicence;

class Surrender extends AbstractDataService
{
    /**
     * @throws DataServiceException
     */
    public function fetchSurrenderData(int $licenceId): array
    {
        $surrenderQuery = ByLicence::create(
            ['id' => $licenceId]
        );

        $response = $this->handleQuery($surrenderQuery);
        if ($response->isOk()) {
            return $response->getResult();
        }

        throw new DataServiceException('unknown-error');
    }
}
