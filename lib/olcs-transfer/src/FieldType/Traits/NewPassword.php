<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

trait NewPassword
{
    /**
     * @var String
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":8,"max":160})
     */
    protected ?string $newPassword = null;

    /**
     * @return ?string
     */
    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }
}
