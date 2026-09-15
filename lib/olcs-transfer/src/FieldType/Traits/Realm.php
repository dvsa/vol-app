<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

trait Realm
{
    /**
     * @var String
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"selfserve","internal"}})
     */
    protected ?string $realm = null;

    /**
     * @return ?string
     */
    public function getRealm(): ?string
    {
        return $this->realm;
    }
}
