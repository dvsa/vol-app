<?php

namespace Olcs\View\Helper;

use Common\RefData;
use Zend\View\Helper\AbstractHelper;

class SurrenderDetails extends AbstractHelper
{
    private $surrender;

    public function __invoke($surrenderData)
    {
        $this->surrender = $surrenderData;
        return $this;
    }

    public function getDeclarationSignatureText(): string
    {
        if ($this->surrender['signatureType']['id'] === RefData::SIGNATURE_TYPE_DIGITAL_SIGNATURE) {
            $unixTimeStamp = strtotime($this->surrender["digitalSignature"]['createdOn']);
            $date = date("j M Y", $unixTimeStamp);
            $attributes = json_decode($this->surrender["digitalSignature"]["attributes"]);
            return "Digitally signed by $attributes->firstname $attributes->lastname at $date";
        }
        return 'Physical signature';
    }
}
