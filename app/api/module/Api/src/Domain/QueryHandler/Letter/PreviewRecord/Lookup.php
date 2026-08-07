<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\PreviewRecord;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Resolve a search term to a licence and its applications for the builder preview.
 *
 * Returns found=false rather than a 404 on a miss: this backs a typeahead, and a
 * half-typed licence number is the normal case, not an error.
 */
class Lookup extends AbstractQueryHandler
{
    /**
     * A long-lived licence can carry a great many applications; the picker only needs the
     * recent ones, and each row costs a lazy status load. Newest-first, then capped.
     */
    private const MAX_APPLICATIONS = 25;

    protected $repoServiceName = 'Licence';

    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        $term = trim((string) $query->getTerm());

        try {
            // A licence number always carries a letter prefix (OB1234567); a bare
            // number can only be a database id.
            /** @var LicenceEntity $licence */
            $licence = ctype_digit($term)
                ? $this->getRepo()->fetchById((int) $term)
                : $this->getRepo()->fetchByLicNoWithoutAdditionalData($term);
        } catch (NotFoundException) {
            return ['found' => false, 'term' => $term];
        }

        $applications = [];
        foreach ($licence->getApplications() as $application) {
            $applications[] = [
                'id' => $application->getId(),
                'status' => $application->getStatus()?->getDescription(),
                'isVariation' => (bool) $application->getIsVariation(),
            ];
        }

        // Newest first: the application a caseworker wants is almost always the latest
        usort($applications, static fn(array $a, array $b): int => $b['id'] <=> $a['id']);

        $truncated = count($applications) > self::MAX_APPLICATIONS;
        if ($truncated) {
            $applications = array_slice($applications, 0, self::MAX_APPLICATIONS);
        }

        return [
            'found' => true,
            'applicationsTruncated' => $truncated,
            'term' => $term,
            'licence' => [
                'id' => $licence->getId(),
                'licNo' => $licence->getLicNo(),
                'organisationName' => $licence->getOrganisation()?->getName(),
                'goodsOrPsv' => $licence->getGoodsOrPsv()?->getDescription(),
                'isNi' => (bool) $licence->isNi(),
            ],
            'applications' => $applications,
        ];
    }
}
