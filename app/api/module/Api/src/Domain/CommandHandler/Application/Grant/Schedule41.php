<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Process Schedule 41
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Schedule41 extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Application';

    protected $extraRepos = ['LicenceOperatingCentre', 'ConditionUndertaking'];

    public function handleCommand(CommandInterface $command)
    {
        /* @var $application ApplicationEntity */
        $application = $this->getRepo()->fetchUsingId($command);

        $result = new \Dvsa\Olcs\Api\Domain\Command\Result();

        $count = 0;
        $createTaskForLicence = false;
        /* @var $aoc \Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre */
        foreach ($application->getOperatingCentres() as $aoc) {
            // if has an S4
            if ($aoc->getS4()) {
                $this->deleteLicenceOperatingCentre($aoc->getS4()->getLicence(), $aoc->getOperatingCentre());
                $this->deleteConditionUndertakings($aoc->getS4()->getLicence(), $aoc->getOperatingCentre());
                if ($aoc->getS4()->getSurrenderLicence() === 'Y') {
                    $createTaskForLicence = $aoc->getS4()->getLicence();
                }
                $count++;
            }
        }

        if ($createTaskForLicence) {
            $result->merge($this->createTask($createTaskForLicence));
        }
        $result->addMessage($count . ' S4 operating centres processed');

        return $result;
    }

    /**
     * Delete any licenceOperatingCentres from the donor licence for an operating centre that has been moved
     */
    protected function deleteLicenceOperatingCentre(Licence $licence, OperatingCentre $operatingCentre)
    {
        /* @var $loc \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre */
        foreach ($licence->getOperatingCentres() as $loc) {
            if ($loc->getOperatingCentre() === $operatingCentre) {
                $this->getRepo('LicenceOperatingCentre')->delete($loc);
            }
        }
    }

    /**
     * Delete any conditionsUndertakings from the donor licence for an operating centre that has been moved
     */
    protected function deleteConditionUndertakings(Licence $licence, OperatingCentre $operatingCentre)
    {
        /* @var $conditionUndertaking \Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking */
        foreach ($licence->getConditionUndertakings() as $conditionUndertaking) {
            if ($conditionUndertaking->getOperatingCentre() === $operatingCentre) {
                $this->getRepo('ConditionUndertaking')->delete($conditionUndertaking);
            }
        }
    }

    /**
     * Create a Task for a surrended donor licence
     *
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    protected function createTask(Licence $licence)
    {
        $currentUser = $this->getCurrentUser();
        $taskData = [
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::TASK_SUB_CATEGORY_SUR_41_ASSISTED_DIGITAL,
            'description' => 'Surrender a donor licence: ' . $licence->getLicNo(),
            'actionDate' => (new DateTime())->modify('+1 month')->format(\DateTime::W3C),
            'licence' => $licence->getId(),
            'assignedToUser' => $currentUser->getId(),
            'assignedToTeam' => $currentUser->getTeam()->getId(),
        ];

        return $this->handleSideEffect(\Dvsa\Olcs\Api\Domain\Command\Task\CreateTask::create($taskData));
    }
}
