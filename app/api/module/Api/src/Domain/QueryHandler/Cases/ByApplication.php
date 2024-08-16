<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Query\Cases\ByLicence as ByLicenceQry;

/**
 * Cases by Application or associated licence
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ByApplication extends AbstractQueryHandler
{
    protected $extraRepos = ['Application'];

    public function handleQuery(QueryInterface $query)
    {
        $application = $this->getRepo('Application')->fetchById($query->getApplication());

        $licenceQuery = [
            'sort' => $query->getSort(),
            'order' => $query->getOrder(),
            'page' => $query->getPage(),
            'limit' => $query->getLimit(),
            'licence' => $application->getLicence()->getId(),
        ];

        return $this->getQueryHandler()->handleQuery(ByLicenceQry::create($licenceQuery), false);
    }
}
