<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
/**
 * Xml
 */
trait Xml
{
    /**
     * @var string
     */
    #[Transfer\Escape(false)]
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    #[Transfer\Validator('\Dvsa\Olcs\Transfer\Validators\Xml', options: ['usePluginManager' => true])]
    public $xml;

    /**
     * @return string
     */
    public function getXml()
    {
        return $this->xml;
    }
}
