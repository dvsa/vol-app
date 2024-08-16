<?php

/**
 * Business Details
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;

/**
 * Business Details
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessDetails extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['Organisation'];

    public function handleQuery(QueryInterface $query)
    {
        /** @var LicenceEntity $licence */
        $licence = $this->getRepo()->fetchUsingId($query);
        $organisation = $licence->getOrganisation();

        /** @var Organisation $organisation */
        $organisation = $this->getRepo('Organisation')->fetchBusinessDetailsById($organisation->getId());

        return $this->result(
            $organisation,
            [
                'contactDetails' => [
                    'address' => [
                        'countryCode'
                    ]
                ]
            ],
            [
                'tradingNames' => $this->resultList($licence->getTradingNames()),
                'companySubsidiaries' => $this->resultList($licence->getCompanySubsidiaries()),
            ]
        );
    }
}
