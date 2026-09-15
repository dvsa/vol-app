<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\AbstractIdWithVersionCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/application/single/knowledge-experience")
 * @Transfer\Method("PUT")
 */
final class UpdateKnowledgeExperience extends AbstractIdWithVersionCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 2})
     * @Transfer\Optional
     */
    protected $evidenceUploadType;

    public function getEvidenceUploadType(): ?string
    {
        return $this->evidenceUploadType;
    }

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $knowledgeExperienceOlat;

    public function getKnowledgeExperienceOlat(): ?string
    {
        return $this->knowledgeExperienceOlat;
    }
}
