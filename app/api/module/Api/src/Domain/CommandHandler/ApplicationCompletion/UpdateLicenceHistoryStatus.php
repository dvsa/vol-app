<?php

/**
 * Update LicenceHistory Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;

/**
 * Update LicenceHistory Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateLicenceHistoryStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'LicenceHistory';

    protected function isSectionValid(Application $application)
    {
        $licenceTypes = [
            OtherLicence::TYPE_CURRENT => $application->getPrevHasLicence(),
            OtherLicence::TYPE_APPLIED => $application->getPrevHadLicence(),
            OtherLicence::TYPE_REFUSED => $application->getPrevBeenRefused(),
            OtherLicence::TYPE_REVOKED => $application->getPrevBeenRevoked(),
            OtherLicence::TYPE_DISQUALIFIED => $application->getPrevBeenDisqualifiedTc(),
            OtherLicence::TYPE_PUBLIC_INQUIRY => $application->getPrevBeenAtPi(),
            OtherLicence::TYPE_HELD => $application->getPrevPurchasedAssets()
        ];

        $yn = ['Y', 'N'];
        $hasAnsweredYes = false;
        $yesLicenceTypes = [];

        // Separate foreachs is more efficient, if any of the answers are not Y/N then we won't bother fetching the
        // other licences
        foreach ($licenceTypes as $licenceType => $answer) {
            if (!in_array($answer, $yn)) {
                return false;
            }

            if ($answer === 'Y') {
                $hasAnsweredYes = true;
                $yesLicenceTypes[] = $licenceType;
            }
        }

        // Can return early if there are no yes answers
        if (!$hasAnsweredYes) {
            return true;
        }

        $otherLicences = $application->getOtherLicences();

        foreach ($yesLicenceTypes as $licenceType) {
            $hasLicences = false;

            /** @var OtherLicence $otherLicence */
            foreach ($otherLicences as $otherLicence) {
                if ($otherLicence->getPreviousLicenceType()->getId() === $licenceType) {
                    $hasLicences = true;
                    continue 2;
                }
            }

            if (!$hasLicences) {
                return false;
            }
        }

        return true;
    }
}
