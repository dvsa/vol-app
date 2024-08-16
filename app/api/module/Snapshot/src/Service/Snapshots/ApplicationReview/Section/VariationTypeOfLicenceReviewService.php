<?php

/**
 * Variation Type Of Licence Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Variation Type Of Licence Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationTypeOfLicenceReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     *
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        $licenceLicenceType = $data['licence']['licenceType']['id'];
        $applicationLicenceType = $data['licenceType']['id'];

        if (
            $licenceLicenceType == Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL &&
            $applicationLicenceType == Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
        ) {
            return $this->getStandardInternationalResponse($data);
        }

        return $this->getNonStandardInternationalResponse($data);
    }

    /**
     * Get the readonly config applicable to variations where both existing licence and variation have a licence
     * type of standard international
     *
     *
     * @return array
     */
    private function getStandardInternationalResponse(array $data)
    {
        $applicationVehicleType = $data['vehicleType']['id'];
        $licenceVehicleType = $data['licence']['vehicleType']['id'];

        $value = $this->translateReplace(
            'variation-application-type-of-licence-std-int-value-template',
            [
                $this->translate('variation-application-type-of-licence-std-int-value-' . $licenceVehicleType),
                $this->translate('variation-application-type-of-licence-std-int-value-' . $applicationVehicleType)
            ]
        );

        return [
            'multiItems' => [
                [
                    [
                        'label' => 'variation-application-type-of-licence-std-int-caption',
                        'value' => $value,
                    ],
                ]
            ]
        ];
    }

    /**
     * Get the readonly config applicable to variations where either the existing licence or variation (or both) have
     * a licence type other than standard international
     *
     *
     * @return array
     */
    private function getNonStandardInternationalResponse(array $data)
    {
        $freeText = $this->translateReplace(
            'variation-application-type-of-licence-freetext',
            [
                $this->formatRefdata($data['licence']['licenceType']),
                $this->formatRefdata($data['licenceType'])
            ]
        );

        return ['freetext' => $freeText];
    }
}
