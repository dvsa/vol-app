<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections\Stub;

use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Service\Submission\Sections\AbstractSection;

class AbstractSectionStub extends AbstractSection
{
    /** @SuppressWarnings("unused") */
    public function generateSection(CasesEntity $casesEntity)
    {
    }

    public function handleQuery($query)
    {
        return parent::handleQuery($query);
    }

    public function extractPerson($contactDetails = null)
    {
        return parent::extractPerson($contactDetails);
    }

    public function formatDate($datetime = null)
    {
        return parent::formatDate($datetime);
    }
}
