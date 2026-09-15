<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Case Type
 */
trait CaseType
{
    /**
     * @var ?string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={"haystack": {"case_t_app","case_t_imp","case_t_lic","case_t_tm"}}
     * )
     */
    protected ?string $caseType = null;

    /**
     * @return string|null
     */
    public function getCaseType(): ?string
    {
        return $this->caseType;
    }
}
