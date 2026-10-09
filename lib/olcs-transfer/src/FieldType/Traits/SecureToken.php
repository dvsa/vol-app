<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * SecureToken
 */
trait SecureToken
{
    /**
     * @var string
     */
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    protected $secureToken;

    /**
     * @return string
     */
    public function getSecureToken()
    {
        return $this->secureToken;
    }
}
