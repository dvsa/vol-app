<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Transfer\Query\Address\GetAddress;
use Dvsa\Olcs\Transfer\Query\Address\GetList;

/**
 * Address Data Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class AddressDataService extends AbstractDataService
{
    /**
     * Request Address by Uprn from PostCode Api
     *
     * @param string $uprn Uprn
     *
     * @return array
     * @throws DataServiceException
     */
    public function getAddressForUprn($uprn)
    {
        $dtoData = GetAddress::create(['uprn' => $uprn]);

        /** @var Response $response */
        $response = $this->handleQuery($dtoData);

        if (!$response->isOk() || count($response->getResult()['results']) === 0) {
            throw new DataServiceException('unknown-error');
        }

        return $response->getResult()['results'][0];
    }

    /**
     * Request Addresses by Post from PostCode Api
     *
     * @param string $postcode Post Code
     *
     * @return mixed
     * @throws DataServiceException
     */
    public function getAddressesForPostcode($postcode)
    {
        $dtoData = GetList::create(['postcode' => $postcode]);

        /** @var Response $response */
        $response = $this->handleQuery($dtoData);

        if (!$response->isOk()) {
            throw new DataServiceException('unknown-error');
        }

        return $response->getResult()['results'];
    }
}
