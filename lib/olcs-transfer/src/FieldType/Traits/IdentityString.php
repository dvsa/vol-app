<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Identity String
 * @author Andy Newton <andy@vitri.ltd>
 */
trait IdentityString
{
    /**
     * @var string
     */
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    #[Transfer\Validator('Laminas\Validator\StringLength', options: ['max' => 512])]
    protected $id;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
