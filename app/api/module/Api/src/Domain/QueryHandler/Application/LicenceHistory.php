<?php

/**
 * Licence History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;
use Laminas\Filter\Word\UnderscoreToCamelCase;

/**
 * Licence History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceHistory extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    public function handleQuery(QueryInterface $query)
    {
        $application = $this->getRepo()->fetchUsingId($query, Query::HYDRATE_OBJECT);

        $types = [
            OtherLicence::TYPE_CURRENT,
            OtherLicence::TYPE_APPLIED,
            OtherLicence::TYPE_REFUSED,
            OtherLicence::TYPE_REVOKED,
            OtherLicence::TYPE_PUBLIC_INQUIRY,
            OtherLicence::TYPE_DISQUALIFIED,
            OtherLicence::TYPE_HELD
        ];

        $filter = new UnderscoreToCamelCase();

        $data = [];
        foreach ($types as $type) {
            $formattedType = lcfirst((string) $filter->filter($type));

            $otherLicences = $application->getOtherLicencesByType($this->getRepo()->getRefdataReference($type));
            $data['otherLicences'][$formattedType] = $this->resultList($otherLicences);
        }

        return $this->result(
            $application,
            [],
            $data
        );
    }
}
