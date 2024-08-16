<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerLicence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence as TmlEntity;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence as TmlRepo;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\TransportManagerLicence\Delete as DeleteDto;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;

/**
 * Delete a transport manager from a licence
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class Delete extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TransportManagerLicence';

    /**
     * handle command
     *
     * @param CommandInterface|DeleteDto $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var $tmlRepo   TmlRepo
         * @var $tmlEntity TmlEntity
         */
        $result = new Result();
        $tmlRepo = $this->getRepo();

        //we'll only ever have one record at once currently
        foreach ($command->getIds() as $tmlId) {
            $tmlEntity = $tmlRepo->fetchById($tmlId);

            $licence = $tmlEntity->getLicence();
            $tmId = $tmlEntity->getTransportManager()->getId();

            $last = ($licence->getTmLicences()->count() === 1 ? true : false);
            $optOut = $command->getYesNo();
            if ($last) {
                $optOutTmLetterValue = match ($optOut) {
                    'Y' => 0,
                    'N' => 1,
                    default => 0,
                };

                $licence->setOptOutTmLetter($optOutTmLetterValue);
                $result->addMessage("optOutTmLetter flag set to {$optOutTmLetterValue} for licence {$tmlId}");
            } else {
                // The task for last TM removal is created by lastTmLetter batch job.
                $result->merge(
                    $this->handleSideEffect(
                        $this->createTaskSideEffect($licence->getId(), $tmId)
                    )
                );
            }
            $tmlRepo->delete($tmlEntity);
            $result->addMessage("Transport manager licence {$tmlId} deleted");
        }

        return $result;
    }

    /**
     * Creates a command for task creation
     *
     * @param int $licenceId licence id
     * @param int $tmId      tm id
     *
     * @return CreateTask
     */
    private function createTaskSideEffect($licenceId, $tmId)
    {
        $params = [
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => SubCategory::TM_SUB_CATEGORY_TM1_REMOVAL,
            'description' => TmlEntity::DESC_TM_REMOVED,
            'licence' => $licenceId,
            'transportManager' => $tmId,
            'urgent' => 'N',
        ];

        return CreateTask::create($params);
    }
}
