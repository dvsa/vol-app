<?php

namespace Olcs\View\Helper;

use Common\RefData;
use Laminas\View\Helper\AbstractHelper;

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
            $unixTimeStamp = strtotime((string) $this->surrender["digitalSignature"]['createdOn']);
            $date = date("j M Y", $unixTimeStamp);
            $attributes = json_decode((string) $this->surrender["digitalSignature"]["attributes"]);
            return "Digitally signed by $attributes->firstname $attributes->surname on $date";
        }
        return 'Physical signature';
    }

    public function returnLicenceDocumentDetailsText(): string
    {
        if ($this->surrender['licenceDocumentStatus']['id'] === RefData::SURRENDER_DOC_STATUS_STOLEN) {
            return 'Details of stolen operator licence document';
        }
        return 'Details of lost operator licence document';
    }

    public function returnCommunityLicenceDocumentDetailsText(): string
    {
        if ($this->surrender['communityLicenceDocumentStatus']['id'] === RefData::SURRENDER_DOC_STATUS_STOLEN) {
            return 'Details of stolen community licence document';
        }
        return 'Details of lost community licence document';
    }
}
