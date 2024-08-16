<?php

namespace Dvsa\Olcs\GdsVerify\Data\Metadata;

use Dvsa\Olcs\GdsVerify\Exception;

/**
 * Class Federation
 */
class Federation
{
    /**
     * @var \SAML2\XML\md\EntityDescriptor
     */
    private $metadataDocument;

    /**
     * Federation constructor.
     *
     * @param string $xml Metadata XML
     */
    public function __construct($xml)
    {
        $document = new \DOMDocument();
        $document->loadXML($xml);

        $this->metadataDocument = new \SAML2\XML\md\EntityDescriptor($document->documentElement);
    }

    /**
     * Get the SSO URL, ie the url to send the SAML request to
     *
     * @return string
     */
    public function getSsoUrl()
    {
        /** @var \SAML2\XML\md\IDPSSODescriptor $roleDescriptor */
        $roleDescriptor = $this->metadataDocument->RoleDescriptor[0];
        /** @var \SAML2\XML\md\EndpointType $singleSignOnService */
        $singleSignOnService = $roleDescriptor->SingleSignOnService[0];

        return $singleSignOnService->Location;
    }

    /**
     * Get the base64 encoded signing certificate
     *
     * NB Multiple signing certs exist in meta, this method currently just gets the first
     *
     * @return string base64 encoded
     * @throws Exception
     */
    public function getSigningCertificate()
    {
        /** @var \SAML2\XML\md\IDPSSODescriptor $roleDescriptor */
        $roleDescriptor = $this->metadataDocument->RoleDescriptor[0];

        /** @var \SAML2\XML\md\KeyDescriptor $keyDescriptor */
        foreach ($roleDescriptor->KeyDescriptor as $keyDescriptor) {
            if ($keyDescriptor->use === 'signing') {
                return $this->formatCertificate($keyDescriptor->KeyInfo->info[1]->data[0]->certificate);
            }
        }

        throw new Exception('Federation signing certificate not found');
    }

    /**
     * Make sure certificate 64 character line length OLCS-24826
     *
     * @return string
     */
    private function formatCertificate(string $certificateString): string
    {
        return "-----BEGIN CERTIFICATE-----\n"
            . trim(wordwrap((string) preg_replace("/\r|\n|\t|\s/", "", $certificateString), 64, PHP_EOL, true))
            . "\n-----END CERTIFICATE-----";
    }
}
