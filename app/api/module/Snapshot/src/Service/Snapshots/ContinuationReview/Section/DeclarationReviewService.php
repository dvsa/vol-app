<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;

/**
 * Declaration Continuation Review Service
 */
class DeclarationReviewService extends AbstractReviewService
{
    public const SIGNATURE = 'markup-continuation_signature';
    public const SIGNATURE_ADDRESS_GB = 'markup-application_undertakings_signature_address_gb';
    public const SIGNATURE_ADDRESS_NI = 'markup-application_undertakings_signature_address_ni';

    /**
     * Format the readonly config from the given data
     *
     * @param ContinuationDetail $continuationDetail continuation detail
     *
     * @return array
     */
    public function getConfigFromData(ContinuationDetail $continuationDetail)
    {

        $items[] = [
            'label' => 'continuations.declaration.signature-type.label',
            'value' => $this->translate($this->getSignatureType($continuationDetail)),
        ];

        if ($continuationDetail->getDigitalSignature()) {
            $items[] = [
                'label' => 'continuations.declaration.signed-by',
                'value' => $continuationDetail->getDigitalSignature()->getSignatureName()
            ];
            $items[] = [
                'label' => 'continuations.declaration.date-of-birth',
                'value' => $this->formatDate($continuationDetail->getDigitalSignature()->getDateOfBirth())
            ];
            $items[] = [
                'label' => 'continuations.declaration.signature-date',
                'value' => $this->formatDate($continuationDetail->getDigitalSignature()->getCreatedOn())
            ];
        }
        $mainItems = [
            [
                'markup' => $this->getDeclarationMarkup($continuationDetail)
            ],
            [
                'header' => 'continuations.declaration.signature-details',
                'items' => $items
            ]
        ];
        if (
            $continuationDetail->getSignatureType() !== null
            && $continuationDetail->getSignatureType()->getId() === RefData::SIG_PHYSICAL_SIGNATURE
        ) {
            $mainItems[] = [
                'markup' => $this->getSignature($continuationDetail)
            ];
        }

        return [
            'mainItems' => $mainItems
        ];
    }

    /**
     * Get signature type text
     *
     * @param ContinuationDetail $continuationDetail Continuation detail
     *
     * @return string
     */
    private function getSignatureType(ContinuationDetail $continuationDetail)
    {
        $signatureTypeOptions = [
            RefData::SIG_DIGITAL_SIGNATURE => 'continuations.declaration.signature-type.digital',
            RefData::SIG_PHYSICAL_SIGNATURE => 'continuations.declaration.signature-type.print',
        ];

        $signatureType = 'continuations.declaration.signature-type.unknown';
        if (isset($signatureTypeOptions[(string)$continuationDetail->getSignatureType()])) {
            $signatureType = $signatureTypeOptions[$continuationDetail->getSignatureType()->getId()];
        }

        return $signatureType;
    }

    /**
     * Get the markup for the declaration content
     *
     * @param ContinuationDetail $continuationDetail Continuation detail
     *
     * @return string
     */
    public function getDeclarationMarkup(ContinuationDetail $continuationDetail)
    {
        $additional = [];

        $licence = $continuationDetail->getLicence();
        if ($licence->isGoods()) {
            // Goods
            if ($licence->isNi()) {
                $markupKey = 'markup-continuation-declaration-goods-ni';
                $markupStandard = 'markup-continuation-declaration-goods-ni-standard';
                $markupOperatingCentres = 'markup-continuation-declaration-goods-ni-operating-centres-not-lgv';
            } else {
                $markupKey = 'markup-continuation-declaration-goods-gb';
                $markupStandard = 'markup-continuation-declaration-goods-gb-standard';
                $markupOperatingCentres = 'markup-continuation-declaration-goods-gb-operating-centres-not-lgv';
            }

            if ($licence->isLgv()) {
                $markupOperatingCentres = 'markup-continuation-declaration-goods-operating-centres-lgv';
            }

            $additional[] = $this->translate($markupOperatingCentres);
        } else {
            // PSV
            if ($licence->isSpecialRestricted()) {
                $markupKey = 'markup-continuation-declaration-psv-sr';
            } else {
                $markupKey = 'markup-continuation-declaration-psv';
                $markupStandard = 'markup-continuation-declaration-psv-standard';
            }
        }

        if (!empty($markupStandard) && ($licence->isStandardNational() || $licence->isStandardInternational())) {
            // add extra bullets if licence is a stanard
            $additional[] = $this->translate($markupStandard);
        } else {
            $additional[] = '';
        }
        $markup = $this->translateReplace($markupKey, $additional);

        return $markup;
    }

    /**
     * Get signature
     *
     * @param ContinuationDetail $continuationDetail continuation detail
     *
     * @return string
     */
    protected function getSignature($continuationDetail)
    {
        $titles = [
            Organisation::ORG_TYPE_REGISTERED_COMPANY => 'undertakings_directors_signature',
            Organisation::ORG_TYPE_LLP => 'undertakings_directors_signature',
            Organisation::ORG_TYPE_PARTNERSHIP => 'undertakings_partners_signature',
            Organisation::ORG_TYPE_SOLE_TRADER => 'undertakings_owners_signature',
            Organisation::ORG_TYPE_OTHER => 'undertakings_responsiblepersons_signature',
            Organisation::ORG_TYPE_IRFO => 'undertakings_responsiblepersons_signature'
        ];
        $addresses = [
            true => self::SIGNATURE_ADDRESS_NI,
            false => self::SIGNATURE_ADDRESS_GB
        ];
        $title = $titles[$continuationDetail->getLicence()->getOrganisation()->getType()->getId()];
        $address = $this->translate($addresses[$continuationDetail->getLicence()->getTrafficArea()->getIsNi()]);

        $additionalParts = [
            $this->translate($title),
            $address
        ];
        return $this->translateReplace(self::SIGNATURE, $additionalParts);
    }
}
