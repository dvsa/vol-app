<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Address;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Service\AddressHelper\AddressHelperService;
use Dvsa\Olcs\DvsaAddressService\Client\Mapper\AddressMapper;
use Dvsa\Olcs\DvsaAddressService\Model\Address;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class GetList extends AbstractQueryHandler
{
    public function __construct(protected AddressHelperService $addressHelperService)
    {
    }

    /**
     * @param \Dvsa\Olcs\Transfer\Query\Address\GetList $query
     * @return array
     */
    public function handleQuery(QueryInterface $query): array
    {
        $addresses = $this->addressHelperService->lookupAddress($query->getPostcode());

        return [
            'result' => AddressMapper::convertAddressObjectsToArrayRepresentation($addresses),
            'count' => count($addresses),
        ];
    }
}
