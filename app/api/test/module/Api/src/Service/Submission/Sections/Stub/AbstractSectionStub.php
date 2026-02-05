<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections\Stub;

use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Service\Submission\Sections\AbstractSection;

class AbstractSectionStub extends AbstractSection
{
    /** @SuppressWarnings("unused") */
    public function generateSection(CasesEntity $casesEntity): void
    {
    }

    #[\Override]
    public function handleQuery($query)
    {
        return parent::handleQuery($query);
    }

    #[\Override]
    public function extractPerson($contactDetails = null)
    {
        return parent::extractPerson($contactDetails);
    }

    #[\Override]
    public function formatDate($datetime = null)
    {
        return parent::formatDate($datetime);
    }
}
