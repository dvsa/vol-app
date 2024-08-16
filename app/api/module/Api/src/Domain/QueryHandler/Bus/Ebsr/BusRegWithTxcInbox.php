<?php

/**
 * BusRegWithTxcInbox
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority;
use Dvsa\Olcs\Api\Entity\Ebsr\TxcInbox;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Bus as Repository;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;

/**
 * BusRegWithTxcInbox
 */
class BusRegWithTxcInbox extends AbstractQueryHandler
{
    protected $repoServiceName = 'Bus';

    /**
     * Handle Query - relationship between busReg and TxcInbox is 1:n to allow multiple entries for different local
     * authorities. However the actual result for a look up will always be a 1:1 providing the local authority or
     * organisation filter is applied. Filter by organisation returns where localAuthority is null.
     *
     * @param QueryInterface $query
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws NotFoundException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var Repository $repo */
        $repo = $this->getRepo();

        $currentUser = $this->getCurrentUser();

        $txcInboxResult = [
            'txcInboxs' => [
                'zipDocument',
                'pdfDocument',
                'routeDocument'
            ]
        ];

        $localAuthority = $currentUser->getLocalAuthority();
        $organisation = $this->getCurrentOrganisation();

        // relationship is 1:n to allow multiple entries for different local authorities.
        // However the actual result for a look up will always be a 1:1 providing the local authority or
        // organisation filter is applied
        if (empty($localAuthority) && $organisation instanceof Organisation) {
            $result = $repo->fetchWithTxcInboxListForOrganisation($query, $organisation->getId());
        } elseif (empty($organisation) && $localAuthority instanceof LocalAuthority) {
            $result = $repo->fetchWithTxcInboxListForLocalAuthority($query, $localAuthority->getId());
        } else {
            $result = $this->getRepo('Bus')->fetchUsingId($query);
            if (empty($result)) {
                throw new NotFoundException();
            }
            // dont return txcInboxs for anonymous users
            $txcInboxResult = [];
        }

        if (empty($result)) {
            throw new NotFoundException();
        }

        // merge resultset we want with a variable txcInbox result. Set to null for anonymous users
        return $this->result(
            $result,
            array_merge(
                [
                    'status',
                    'licence' => [
                        'organisation' => ['disqualifications'],
                        'licenceType',
                        'status',
                    ],
                    'busNoticePeriod',
                    'busServiceTypes',
                    'trafficAreas',
                    'localAuthoritys',
                    'subsidised',
                    'otherServices',
                    'variationReasons',
                    'npPublicationNo'
                ],
                $txcInboxResult
            ),
            [
                'npPublicationNo' => $result->getLicence()->determineNpNumber()
            ]
        );
    }
}
