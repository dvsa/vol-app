<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

trait EvidenceUploadType
{
    protected $evidenceUploadType;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 2})
     */
    public function getEvidenceUploadType(): ?string
    {
        return $this->evidenceUploadType;
    }
}
