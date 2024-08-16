<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as EbsrSubmissionRepo;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * OrganisationUnprocessedList
 *
 * Brings back a list of unprocessed ebsr submissions for an organisation
 */
class OrganisationUnprocessedList extends AbstractQueryHandler
{
    protected $repoServiceName = 'EbsrSubmission';

    /**
     * query to retrieve list of unprocessed ebsr packs for an organisation
     *
     * @param QueryInterface $query the query
     *
     * @return array
     * @throws ValidationException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var EbsrSubmissionRepo $repo */
        $repo = $this->getRepo();

        $organisation = $this->getCurrentOrganisation();

        if (!$organisation instanceof Organisation) {
            throw new ValidationException(['No organisation was found']);
        }

        $results = $repo->fetchForOrganisationByStatus($organisation->getId(), EbsrSubmissionEntity::UPLOADED_STATUS);

        $documents = [];

        /** @var EbsrSubmission $ebsrSub */
        foreach ($results as $ebsrSub) {
            $documents[] = $ebsrSub->getDocument();
        }

        return $this->resultList($documents);
    }
}
