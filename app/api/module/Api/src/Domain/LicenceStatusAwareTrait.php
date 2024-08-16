<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Entity\Licence\Licence;

trait LicenceStatusAwareTrait
{
    private function isLicenceStatusSurrenderable(Licence $licence): bool
    {
        return $this->isLicenceStatusStrictlyActive($licence);
    }

    private function isLicenceStatusAccessibleForExternalUser(Licence $licence): bool
    {
        return in_array($licence->getStatus()->getId(), $this->getLicenceStatusesStrictlyActive());
    }

    private function isLicenceStatusActive(Licence $licence): bool
    {
        return in_array($licence->getStatus()->getId(), $this->getLicenceStatusesActive());
    }

    public function notBeenSurrendered(Licence $licence): bool
    {
        return  $licence->getStatus()->getId() !== Licence::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION;
    }
    private function isLicenceStatusStrictlyActive(Licence $licence): bool
    {
        return in_array($licence->getStatus()->getId(), $this->getLicenceStatusesStrictlyActive());
    }

    private function getLicenceStatusesActive(): array
    {
        return array_merge(
            $this->getLicenceStatusesStrictlyActive(),
            [
                Licence::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION,
            ]
        );
    }

    private function getLicenceStatusesStrictlyActive(): array
    {
        return [
            Licence::LICENCE_STATUS_VALID,
            Licence::LICENCE_STATUS_SUSPENDED,
            Licence::LICENCE_STATUS_CURTAILED,
        ];
    }
}
