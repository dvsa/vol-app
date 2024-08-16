<?php

/**
 * Get documents for tm responsibilities
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TmResponsibilities;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Get documents for tm responsibilities
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GetDocumentsForResponsibilities extends AbstractQueryHandler
{
    protected $repoServiceName = 'Document';

    protected $extraRepos = ['TransportManagerApplication', 'TransportManagerLicence'];

    public function handleQuery(QueryInterface $query)
    {
        if ($query->getType() == 'application') {
            $tmApplication = $this->getRepo('TransportManagerApplication')
                ->fetchForResponsibilities($query->getLicOrAppId());
            $documents = $this->getRepo()
                ->fetchListForTmApplication(
                    $query->getTransportManager(),
                    $tmApplication->getApplication()->getId()
                );
        } else {
            $tmLicence = $this->getRepo('TransportManagerLicence')
                ->fetchForResponsibilities($query->getLicOrAppId());
            $documents = $this->getRepo()
                ->fetchListForTmLicence(
                    $query->getTransportManager(),
                    $tmLicence->getLicence()->getId()
                );
        }
        return [
            'result' => $this->resultList($documents),
            'count' => count($documents),
        ];
    }
}
