<?php

/**
 * Variation Undertakings Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Utils\Helper\ValueHelper;

/**
 * Variation Undertakings Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationUndertakingsReviewService extends AbstractReviewService
{
    public const GV81 = 'markup-application_undertakings_GV81';
    public const GV81_STANDARD = 'markup-application_undertakings_GV81-Standard';
    public const GV81_DECLARE = 'markup-application_undertakings_GV81-declare';

    public const GV81NI = 'markup-application_undertakings_GV81-NI';
    public const GV81NI_STANDARD = 'markup-application_undertakings_GV81-NI-Standard';
    public const GV81NI_DECLARE = 'markup-application_undertakings_GV81-NI-declare';

    public const GV81_AUTH_LGV = 'markup-application_undertakings_GV81-auth-lgv';
    public const GV81_AUTH_OTHER = 'markup-application_undertakings_GV81-auth-other';
    public const GV81NI_AUTH_OTHER = 'markup-application_undertakings_GV81-NI-auth-other';

    public const GV80A = 'markup-application_undertakings_GV80A';
    public const GV80A_DECLARE = 'markup-application_undertakings_GV80A-declare';

    public const GV80ANI = 'markup-application_undertakings_GV80A-NI';
    public const GV80ANI_DECALRE = 'markup-application_undertakings_GV80A-NI-declare';

    public const PSV430 = 'markup-application_undertakings_PSV430';
    public const PSV430_STANDARD = 'markup-application_undertakings_PSV430-Standard';
    public const PSV430_DECLARE = 'markup-application_undertakings_PSV430-declare';

    public const SIGNATURE = 'markup-application_undertakings_signature';

    private $standardOptions = [
        Licence::LICENCE_TYPE_STANDARD_NATIONAL,
        Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
    ];

    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     * @return array
     */
    #[\Override]
    public function getConfigFromData(array $data = [])
    {
        return [
            'markup' => $this->getMarkup($data)
        ];
    }

    private function getMarkup($data)
    {
        if ($this->isPsv($data)) {
            return $this->getPsv430($data);
        }

        if ($this->isUpgrade($data)) {
            if (ValueHelper::isOn($data['niFlag'])) {
                return $this->getNiGv80A($data);
            }

            return $this->getGv80A($data);
        }

        if (ValueHelper::isOn($data['niFlag'])) {
            return $this->getGv81Ni($data);
        }

        return $this->getGv81($data);
    }

    public function getLongTextMarkup(array $data): string
    {
        if ($this->isPsv($data)) {
            return $this->translate(
                $this->isStandard($data)
                    ? 'markup-variation-declaration-psv-standard'
                    : 'markup-variation-declaration-psv-restricted'
            );
        }

        if ($this->isUpgrade($data)) {
            return $this->translate(
                ValueHelper::isOn($data['niFlag'])
                    ? 'markup-variation-declaration-goods-ni-upgrade'
                    : 'markup-variation-declaration-goods-gb-upgrade'
            );
        }

        $region = ValueHelper::isOn($data['niFlag']) ? 'ni' : 'gb';
        $type = match (true) {
            $data['vehicleType']['id'] === RefData::APP_VEHICLE_TYPE_LGV => 'lgv',
            $this->isStandard($data) => 'standard',
            default => 'restricted',
        };

        return $this->translate(sprintf('markup-variation-declaration-goods-%s-%s', $region, $type));
    }

    public function getLongTextReviewMarkup(): string
    {
        return $this->translate('markup-review-text-variation');
    }

    private function getGv81(array $data)
    {
        $isStandard = $this->isStandard($data);
        $isInternal = $this->isInternal($data);

        $additionalParts = [
            $isInternal ? $this->translate(self::GV81_DECLARE) : '',
            $isInternal ? $this->getSignature($data) : '',
            $this->translate($this->getGv81AuthTranslationKey($data)),
            $isStandard ? $this->translate(self::GV81_STANDARD) : ''
        ];

        return $this->translateReplace(self::GV81, $additionalParts);
    }

    private function getGv81Ni(array $data)
    {
        $isStandard = $this->isStandard($data);
        $isInternal = $this->isInternal($data);

        $additionalParts = [
            $isInternal ? $this->translate(self::GV81NI_DECLARE) : '',
            $isInternal ? $this->getSignature($data) : '',
            $this->translate($this->getGv81AuthTranslationKey($data)),
            $isStandard ? $this->translate(self::GV81NI_STANDARD) : ''
        ];

        return $this->translateReplace(self::GV81NI, $additionalParts);
    }

    /**
     * Get the translation key corresponding to the auth bullet points within the declaration
     *
     *
     * @return string
     */
    private function getGv81AuthTranslationKey(array $data)
    {
        $vehicleTypeId = $data['vehicleType']['id'];
        if ($vehicleTypeId == RefData::APP_VEHICLE_TYPE_LGV) {
            return self::GV81_AUTH_LGV;
        }

        if (ValueHelper::isOn($data['niFlag'])) {
            return self::GV81NI_AUTH_OTHER;
        }

        return self::GV81_AUTH_OTHER;
    }

    private function getGv80A(array $data)
    {
        $isInternal = $this->isInternal($data);
        $additionalParts = [
            $isInternal ? $this->translate(self::GV80A_DECLARE) : '',
            $isInternal ? $this->getSignature($data) : ''
        ];

        return $this->translateReplace(self::GV80A, $additionalParts);
    }

    private function getNiGv80A(array $data)
    {
        $isInternal = $this->isInternal($data);
        $additionalParts = [
            $isInternal ? $this->translate(self::GV80ANI_DECALRE) : '',
            $isInternal ? $this->getSignature($data) : ''
        ];

        return $this->translateReplace(self::GV80ANI, $additionalParts);
    }

    private function getPsv430(array $data)
    {
        $isStandard = $this->isStandard($data);
        $isInternal = $this->isInternal($data);
        $additionalParts = [
            $isInternal ? $this->translate(self::PSV430_DECLARE) : '',
            $isInternal ? $this->getSignature($data) : '',
            $isStandard ? $this->translate(self::PSV430_STANDARD) : ''
        ];

        return $this->translateReplace(self::PSV430, $additionalParts);
    }

    private function isStandard(array $data)
    {
        return in_array($data['licenceType']['id'], $this->standardOptions);
    }

    /**
     * If the variation is upgrading from restricted to standard
     *
     * @return bool
     */
    private function isUpgrade(array $data)
    {
        return ($data['licence']['licenceType']['id'] === Licence::LICENCE_TYPE_RESTRICTED && $this->isStandard($data));
    }
}
