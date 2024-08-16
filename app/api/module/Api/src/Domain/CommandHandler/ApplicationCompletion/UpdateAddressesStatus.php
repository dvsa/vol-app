<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Update Addresses Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateAddressesStatus extends AbstractUpdateStatus implements AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Application';

    protected $section = 'Addresses';

    protected function isSectionValid(Application $application)
    {
        $licence = $application->getLicence();

        // Must have correspondence address
        $corAdd = $licence->getCorrespondenceCd();

        if ($corAdd === null) {
            return false;
        }

        // Must have at least 1 phone contact
        $phoneContacts = $corAdd->getPhoneContacts();
        if ($phoneContacts->count() < 1 && $this->isExternalUser()) {
            return false;
        }

        $allowedLicTypes = [
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
        ];

        // If we need an establishment address
        if (in_array($application->getLicenceType()->getId(), $allowedLicTypes)) {
            $estAdd = $licence->getEstablishmentCd();

            if ($estAdd === null) {
                return false;
            }
        }

        return true;
    }
}
