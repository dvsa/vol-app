<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;

/**
 * Interface SectionGeneratorInterface
 * @package Dvsa\Olcs\Api\Service\Submission\Sections
 */
interface SectionGeneratorInterface
{
    public function generateSection(CasesEntity $casesEntity);
}
