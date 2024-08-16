<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\DigitalSignature;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\RefData;

class SignatureReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given record
     *
     * @param array $data
     * @return mixed
     */
    public function getConfigFromData(array $data = [])
    {
        if ($data['signatureType']->getId() === RefData::SIG_PHYSICAL_SIGNATURE) {
            return $this->getPhysicalConfig($data['organisation'], $data['isNi']);
        }

        return $this->getDigitalConfig($data['digitalSignature']);
    }

    private function getPhysicalConfig(Organisation $organisation, $isNi)
    {
        $title = $this->getSignatureLabel($organisation);
        $address = $isNi ? static::SIGNATURE_ADDRESS_NI : static::SIGNATURE_ADDRESS_GB;

        $return = [
            'markup' => $this->translateReplace(
                static::SIGNATURE,
                [
                    $this->translate($title),
                    $this->translate($address)
                ]
            )
        ];

        return $return;
    }

    private function getDigitalConfig(DigitalSignature $signature)
    {
        return [
            'markup' => $this->translateReplace(
                'markup-signature-digital',
                [
                    $signature->getSignatureName(),
                    $this->formatDate($signature->getDateOfBirth()),
                    $this->formatDate($signature->getCreatedOn(true))
                ]
            )
        ];
    }

    private function getSignatureLabel(Organisation $organisation)
    {
        return match ($organisation->getType()->getId()) {
            Organisation::ORG_TYPE_REGISTERED_COMPANY, Organisation::ORG_TYPE_LLP => 'undertakings_directors_signature',
            Organisation::ORG_TYPE_PARTNERSHIP => 'undertakings_partners_signature',
            Organisation::ORG_TYPE_SOLE_TRADER => 'undertakings_owners_signature',
            default => 'undertakings_responsiblepersons_signature',
        };
    }
}
