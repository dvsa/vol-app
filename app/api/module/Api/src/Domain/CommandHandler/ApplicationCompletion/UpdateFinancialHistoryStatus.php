<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Update FinancialHistory Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateFinancialHistoryStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'FinancialHistory';

    /**
     * Check is section valid
     *
     * @param Application $application Application Entity
     *
     * @return bool
     */
    protected function isSectionValid(Application $application)
    {
        if ($application->getInsolvencyConfirmation() !== 'Y') {
            return false;
        }

        $yesNos = [
            $application->getBankrupt(),
            $application->getLiquidation(),
            $application->getReceivership(),
            $application->getAdministration(),
            $application->getDisqualified()
        ];

        foreach ($yesNos as $yesNo) {
            if (!in_array($yesNo, ['Y', 'N'], true)) {
                return false;
            }

            if ($yesNo === 'Y' && strlen((string) preg_replace('/\s+/', '', $application->getInsolvencyDetails())) < 150) {
                return false;
            }
        }

        return true;
    }
}
