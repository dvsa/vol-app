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
            return "Digitally signed by $attributes->firstname $attributes->surname at $date";
        }
        return 'Physical signature';
    }

    public function returnLicenceDocumentDetailsText(): string
    {
        if ($this->surrender['licenceDocumentStatus']['id'] === RefData::SURRENDER_DOC_STATUS_STOLEN) {
            return 'Licence document stolen details';
        }
        return 'Licence document lost details';
    }

    public function returnCommunityLicenceDocumentDetailsText(): string
    {
        if ($this->surrender['communityLicenceDocumentStatus']['id'] === RefData::SURRENDER_DOC_STATUS_STOLEN) {
            return 'Community licence stolen document details';
        }
        return 'Community licence lost document details';
    }
}
