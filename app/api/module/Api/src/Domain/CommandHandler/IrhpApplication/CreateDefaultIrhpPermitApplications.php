<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create default irhp permit applications
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CreateDefaultIrhpPermitApplications extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrhpApplication';

    protected $extraRepos = ['IrhpPermitApplication', 'IrhpPermitWindow'];

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $irhpApplicationId = $command->getId();

        /** @var IrhpApplication $irhpApplication */
        $irhpApplication = $this->getRepo()->fetchById($irhpApplicationId);
        $permitTypeId = $irhpApplication->getIrhpPermitType()->getId();

        if (
            !in_array(
                $permitTypeId,
                [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL,
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE,
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER,
                ]
            )
        ) {
            $this->result->addMessage('No default irhp permit applications need to be created');
            return $this->result;
        }

        switch ($permitTypeId) {
            case IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL:
            case IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL:
            case IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL:
            case IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE:
            case IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER:
                $irhpPermitWindows = $this->getRepo('IrhpPermitWindow')->fetchOpenWindowsByType(
                    $permitTypeId,
                    new DateTime()
                );
                break;
            case IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM:
            case IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT:
                $irhpPermitWindow = $this->getRepo('IrhpPermitWindow')->fetchLastOpenWindowByStockId(
                    $command->getIrhpPermitStock()
                );
                $irhpPermitWindows = [$irhpPermitWindow];
                break;
        }

        foreach ($irhpPermitWindows as $irhpPermitWindow) {
            $irhpPermitApplication = IrhpPermitApplication::createNewForIrhpApplication(
                $irhpApplication,
                $irhpPermitWindow
            );
            $this->getRepo('IrhpPermitApplication')->save($irhpPermitApplication);
        }

        $this->result->addMessage('Created ' . count($irhpPermitWindows) . ' irhp permit applications');
        return $this->result;
    }
}
