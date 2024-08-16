<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Utils\Helper\ValueHelper;

/**
 * Application Undertakings Review Service
 *
 * @NOTE This is also re-used in Dvsa\Olcs\Api\Domain\QueryHandler\Application\DeclarationUndertakings
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationUndertakingsReviewService extends AbstractReviewService
{
    public const GV79 = 'markup-application_undertakings_GV79';
    public const GV79_STANDARD = 'markup-application_undertakings_GV79-Standard';
    public const GV79_DECLARE = 'markup-application_undertakings_GV79-declare';

    public const GV79_AUTH_LGV = 'markup-application_undertakings_GV79-auth-lgv';

    public const GV79_AUTH_LGV_NI = 'markup-application_undertakings_GV79-auth-lgv-NI';

    public const GV79_SI = 'markup-application_undertakings_GV79-si';

    public const GV79_AUTH_RESTRICTED = 'markup-application_undertakings_GV79-auth-restricted';
    public const GV79_AUTH_OTHER = 'markup-application_undertakings_GV79-auth-other';
    public const GV79NI_AUTH_OTHER = 'markup-application_undertakings_GV79-NI-auth-other';

    public const GV79NI = 'markup-application_undertakings_GV79-NI';
    public const GV79NI_STANDARD = 'markup-application_undertakings_GV79-NI-Standard';
    public const GV79NI_DECLARE = 'markup-application_undertakings_GV79-NI-declare';

    public const PSV421 = 'markup-application_undertakings_PSV421';
    public const PSV421_STANDARD = 'markup-application_undertakings_PSV421-Standard';
    public const PSV421_DECLARE = 'markup-application_undertakings_PSV421-declare';

    public const PSV356 = 'markup-application_undertakings_PSV356';

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
    public function getConfigFromData(array $data = [])
    {
        return [
            'markup' => $this->getMarkup($data)
        ];
    }

    public function getMarkup($data)
    {
        if ($this->isPsv($data)) {
            if ($data['licenceType']['id'] === Licence::LICENCE_TYPE_SPECIAL_RESTRICTED) {
                return $this->getPsv356();
            }

            return $this->getPsv421($data);
        }

        if (ValueHelper::isOn($data['niFlag'])) {
            return $this->getGv79Ni($data);
        }

        if ($this->isLgvOnly($data)) {
            return $this->getLgvOnly($data);
        }

        if ($this->isGoodsStandardInternational($data)) {
            return $this->getGvSI($data);
        }

        if ($this->isGoodsRestricted($data)) {
            return $this->getGoodsRestricted($data);
        }

        return $this->getGv79($data);
    }

    private function getGv79(array $data)
    {
        $isInternal = $this->isInternal($data);
        $additionalParts = [
            $isInternal ? $this->translate(self::GV79_DECLARE) : ''
        ];
        return $this->translateReplace(self::GV79, $additionalParts);
    }

    private function getLgvOnly(array $data)
    {
        $isInternal = $this->isInternal($data);
        $additionalParts = [
            $isInternal ? $this->translate(self::GV79_DECLARE) : ''
        ];
        return $this->translateReplace(self::GV79_AUTH_LGV, $additionalParts);
    }

    private function getGvSI(array $data)
    {
        $isInternal = $this->isInternal($data);
        $additionalParts = [
            $isInternal ? $this->translate(self::GV79_DECLARE) : ''
        ];
        return $this->translateReplace(self::GV79_SI, $additionalParts);
    }

    private function getGoodsRestricted(array $data)
    {
        $isInternal = $this->isInternal($data);
        $additionalParts = [
            $isInternal ? $this->translate(self::GV79_DECLARE) : ''
        ];
        return $this->translateReplace(self::GV79_AUTH_RESTRICTED, $additionalParts);
    }

    private function getGv79Ni(array $data)
    {
        $isStandard = $this->isStandard($data);
        $isInternal = $this->isInternal($data);

        $additionalParts = [
            $this->translate($this->getGv79AuthTranslationKeyNI($data)),
            $isStandard ? $this->translate(self::GV79NI_STANDARD) : '',
            $isInternal ? $this->translate(self::GV79NI_DECLARE) : ''
        ];

        return $this->translateReplace(self::GV79NI, $additionalParts);
    }

    /**
     * Get the translation key corresponding to the auth bullet points within the declaration
     *
     *
     * @return string
     */
    private function getGv79AuthTranslationKeyNI(array $data)
    {
        $vehicleTypeId = $data['vehicleType']['id'];
        if ($vehicleTypeId == RefData::APP_VEHICLE_TYPE_LGV) {
            return self::GV79_AUTH_LGV_NI;
        }

        return self::GV79NI_AUTH_OTHER;
    }

    private function getPsv421(array $data)
    {
        $isStandard = $this->isStandard($data);
        $isInternal = $this->isInternal($data);

        $additionalParts = [
            $isStandard ? $this->translate(self::PSV421_STANDARD) : '',
            $isInternal ? $this->translate(self::PSV421_DECLARE) : ''
        ];

        return $this->translateReplace(self::PSV421, $additionalParts);
    }

    private function getPsv356()
    {
        return $this->translate(self::PSV356);
    }

    private function isStandard(array $data)
    {
        return in_array($data['licenceType']['id'], $this->standardOptions);
    }

    private function isGoodsRestricted(array $data)
    {
        return $data['licenceType']['id'] == Licence::LICENCE_TYPE_RESTRICTED
            && $data['vehicleType']['id'] == RefData::APP_VEHICLE_TYPE_HGV;
    }

    private function isGoodsStandardInternational(array $data)
    {
        return $data['licenceType']['id'] == Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
            && $data['vehicleType']['id'] == RefData::APP_VEHICLE_TYPE_MIXED;
    }

    private function isLgvOnly(array $data)
    {
        return $data['licenceType']['id'] == Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
            && $data['vehicleType']['id'] == RefData::APP_VEHICLE_TYPE_LGV;
    }
}
