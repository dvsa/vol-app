<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Prohibition\Prohibition;

/**
 * Class ProhibiitonHistory
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
final class ProhibitionHistory extends AbstractSection
{
    /**
     * Generate only the section data required.
     *
     * @param CasesEntity $case Case relating to the submission
     *
     * @return array Data array containing information for the submission section
     */
    public function generateSection(CasesEntity $case)
    {
        $prohibitions = $case->getProhibitions();

        $data = [];

        /** @var Prohibition $entity */
        foreach ($prohibitions as $prohibition) {
            $thisRow = [];
            $thisRow['id'] = $prohibition->getId();
            $thisRow['version'] = $prohibition->getVersion();
            $thisRow['prohibitionDate'] = $this->formatDate($prohibition->getProhibitionDate());
            $thisRow['clearedDate'] = $this->formatDate($prohibition->getClearedDate());
            $thisRow['vehicle'] = $prohibition->getVrm();
            $thisRow['trailer'] = $prohibition->getIsTrailer();
            $thisRow['imposedAt'] = $prohibition->getImposedAt();
            $thisRow['prohibitionType'] = $prohibition->getProhibitionType()->getDescription();

            $data[] = $thisRow;
        }

        return [
            'data' => [
                'tables' => [
                    'prohibition-history' => $data
                ],
                'text' => $case->getProhibitionNote()
            ]
        ];
    }
}
