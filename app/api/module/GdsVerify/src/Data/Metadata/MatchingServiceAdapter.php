<?php

namespace Dvsa\Olcs\GdsVerify\Data\Metadata;

use Dvsa\Olcs\GdsVerify\Exception;

/**
 * Class MatchingServiceAdapter
 */
class MatchingServiceAdapter
{
    /**
     * @var \SAML2\XML\md\EntitiesDescriptor
     */
    private $metadataDocument;

    /**
     * MatchingServiceAdapter constructor.
     *
     * @param string $xml XML
     *
     * @throws Exception
     */
    public function __construct($xml)
    {
        $document = new \DOMDocument();
        $document->loadXML($xml);

        $element = $document->documentElement;

        $this->metadataDocument = new \SAML2\XML\md\EntitiesDescriptor($element);
    }

    /**
     * Get the base64 encoded signing certificate
     *
     * @return string base64 encoded
     * @throws Exception
     */
    public function getSigningCertificate()
    {
        try {
            /** @var \SAML2\XML\md\IDPSSODescriptor $roleDescriptor */
            $roleDescriptor = $this->metadataDocument->children[0]->RoleDescriptor[0];

            /** @var \SAML2\XML\md\KeyDescriptor $keyDescriptor */
            foreach ($roleDescriptor->KeyDescriptor as $keyDescriptor) {
                if ($keyDescriptor->use === 'signing') {
                    return $this->formatCertificate($keyDescriptor->KeyInfo->info[1]->data[0]->certificate);
                }
            }
        } catch (\Exception $e) {
            throw new Exception('Matching Service Adapter signing certificate not found : ' . $e->getMessage());
        }

        throw new Exception('Matching Service Adapter signing certificate not found');
    }

    /**
     * Method to ensure always a valid certifcate -
     * For OpenSSL to recognize it as a PEM format, it must be encoded in Base64, with the following header :
     *
     * -----BEGIN CERTIFICATE-----
     * and footer:
     *
     * -----END CERTIFICATE-----
     * Also, each line must be maximum 79 characters long.
     *
     *
     * @return string
     */
    private function formatCertificate(string $certificateString): string
    {
        return "-----BEGIN CERTIFICATE-----\n"
            . trim(wordwrap((string) preg_replace("/\r|\n|\t|\s/", "", $certificateString), 64, PHP_EOL, true))
            . "\n-----END CERTIFICATE-----";
    }

    /**
     * Get the SSO Url
     *
     * @return string
     * @throws Exception
     */
    public function getSsoUrl()
    {
        try {
            /** @var \SAML2\XML\md\IDPSSODescriptor $roleDescriptor */
            foreach ($this->metadataDocument->children[0]->RoleDescriptor as $roleDescriptor) {
                if ($roleDescriptor instanceof \SAML2\XML\md\IDPSSODescriptor) {
                    return trim($roleDescriptor->SingleSignOnService[0]->Location);
                }
            }
        } catch (\Exception $e) {
            throw new Exception('SSO URL not found in metadata : ' . $e->getMessage());
        }

        throw new Exception('SSO URL not found in metadata');
    }
}
