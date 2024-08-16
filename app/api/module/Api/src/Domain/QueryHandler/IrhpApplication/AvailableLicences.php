<?php

/**
 * Retrieve IRHP application record alongside available licences
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\AvailableLicences as AvailableLicencesQry;
use Dvsa\Olcs\Transfer\Query\Organisation\OrganisationAvailableLicences as OrganisationAvailableLicencesQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class AvailableLicences extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpApplication';

    public function handleQuery(QueryInterface $query)
    {
        /**
         * @var IrhpApplicationRepo   $irhpApplicationRepo
         * @var IrhpApplicationEntity $irhpApplication
         * @var AvailableLicencesQry  $query
         */
        $irhpApplicationRepo = $this->getRepo();
        $irhpApplication = $irhpApplicationRepo->fetchUsingId($query);
        $permitType = $irhpApplication->getIrhpPermitType();

        $queryData = [
            'id' => $this->getCurrentOrganisation()->getId(),
            'irhpPermitType' => $permitType->getId(),
        ];

        $queryData['irhpPermitStock'] = $permitType->isMultiStock()
            ? null
            : $irhpApplication->getAssociatedStock()->getId();

        $availableLicencesQry = OrganisationAvailableLicencesQry::create($queryData);
        $availableLicences = $this->getQueryHandler()->handleQuery($availableLicencesQry);

        $availableLicences['selectedLicence'] = $irhpApplication->getLicence()->getId();
        $availableLicences['isNotYetSubmitted'] = $irhpApplication->isNotYetSubmitted();

        return $availableLicences;
    }
}
