<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

trait Password
{
    /**
     * @var String
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":8,"max":255})
     * @Transfer\Escape(false)
     */
    protected $password = null;

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }
}
