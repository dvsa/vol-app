<?php

/**
 * Transport Managers Query Handler
 * Retreive the licence and a list of transport manager licence (including contact details)
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Transport Managers Query Handler
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TransportManagers extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';
    protected $extraRepos = ['TransportManagerLicence'];

    public function handleQuery(QueryInterface $query)
    {
        /* @var $licence \Dvsa\Olcs\Api\Entity\Licence\Licence */
        $licence = $this->getRepo()->fetchUsingId($query);
        $tmls = $this->getRepo('TransportManagerLicence')->fetchWithContactDetailsByLicence($licence->getId());

        return [
            'id' => $licence->getId(),
            'tmLicences' => $tmls
        ];
    }
}
