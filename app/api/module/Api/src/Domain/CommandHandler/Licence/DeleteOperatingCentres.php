<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteApplicationLinks;
use Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteConditionUndertakings;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class DeleteOperatingCentres extends AbstractCommandHandler implements TransactionedInterface, CacheAwareInterface
{
    use CacheAwareTrait;

    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['LicenceOperatingCentre'];

    /**
     * Handle Command
     *
     * @param \Dvsa\Olcs\Transfer\Command\Licence\DeleteOperatingCentres $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var LicenceEntity $licence */
        $licence = $this->getRepo()->fetchById($command->getLicence());

        $locs = $licence->getOperatingCentres();

        $count = 0;

        /** @var LicenceOperatingCentre $loc */
        foreach ($locs as $loc) {
            if (in_array($loc->getId(), $command->getIds())) {
                $message = $loc->checkCanDelete();
                if ($message) {
                    throw new \Dvsa\Olcs\Api\Domain\Exception\BadRequestException(key($message));
                }

                $count++;
                $this->getRepo('LicenceOperatingCentre')->delete($loc);
                $this->result->merge($this->deleteConditionUndertakings($loc));
                $this->result->merge($this->deleteFromOtherApplications($loc));
            }
        }

        $this->clearLicenceCaches($licence);
        $this->result->addMessage($count . ' Operating Centre(s) removed');

        return $this->result;
    }

    /**
     * Delete Condition Undertakings
     *
     * @param LicenceOperatingCentre $loc Licence Operating Centre Entity
     *
     * @return Result
     */
    private function deleteConditionUndertakings($loc)
    {
        return $this->handleSideEffect(
            DeleteConditionUndertakings::create(
                [
                    'operatingCentre' => $loc->getOperatingCentre(),
                    'licence' => $loc->getLicence(),
                ]
            )
        );
    }

    /**
     * Delete From Other Applications
     *
     * @param LicenceOperatingCentre $loc Licence Operating Centre Entity
     *
     * @return Result
     */
    private function deleteFromOtherApplications($loc)
    {
        return $this->handleSideEffect(
            DeleteApplicationLinks::create(['operatingCentre' => $loc->getOperatingCentre()])
        );
    }
}
