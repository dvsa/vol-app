<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;

/**
 * Cases with OOO and OOR dates attached
 */
final class CasesWithOppositionDates extends AbstractQueryHandler
{
    protected $repoServiceName = 'Cases';

    public function handleQuery(QueryInterface $query)
    {
        $case = $this->getRepo()->fetchUsingId($query);

        return $this->result(
            $case,
            [
                'application' => [
                    'operatingCentres',
                    'publicationLinks' => [
                        'filter' => fn($element) => in_array(
                            (string)$element->getPublicationSection(),
                            [
                                PublicationSectionEntity::APP_NEW_SECTION,
                                PublicationSectionEntity::VAR_NEW_SECTION,
                            ]
                        ),
                        'publication'
                    ]
                ],
                'licence' => [
                    'goodsOrPsv'
                ]
            ],
            [
                'oooDate' => $this->calculateOoo($case),
                'oorDate' => $this->calculateOor($case)
            ]
        );
    }

    /**
     * Calculate Out of Representation date
     *
     *
     * @return string A date string or and empty string
     */
    public function calculateOor(CaseEntity $case)
    {
        if (!empty($case->getApplication())) {
            $oorDate = $case->getApplication()->getOutOfRepresentationDate();
            if ($oorDate instanceof \DateTime) {
                return $oorDate->format(\DateTime::ISO8601);
            }
        }

        return '';
    }

    /**
     * Calculate the Out of Opposition date
     *
     *
     * @return string A date string or and empty string
     */
    public function calculateOoo(CaseEntity $case)
    {
        if (!empty($case->getApplication())) {
            $oooDate = $case->getApplication()->getOutOfOppositionDate();
            if ($oooDate instanceof \DateTime) {
                return $oooDate->format(\DateTime::ISO8601);
            }
        }
        return '';
    }
}
