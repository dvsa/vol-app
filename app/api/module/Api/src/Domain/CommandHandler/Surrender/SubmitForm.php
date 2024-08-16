<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Surrender;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Surrender\Snapshot;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

class SubmitForm extends AbstractSurrenderCommandHandler
{
    use AuthAwareTrait;

    protected $extraRepos = ['Licence'];

    /**
     * @param CommandInterface $command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = $this->handleSideEffect(\Dvsa\Olcs\Transfer\Command\Surrender\Update::create(
            [
                'id' => $command->getId(),
                'status' => Surrender::SURRENDER_STATUS_SIGNED,
                'signatureType' => $this->getRepo()->getRefdataReference(RefData::SIG_PHYSICAL_SIGNATURE)
            ]
        ));

        $licenceRepo = $this->getRepo('Licence');
        /** @var Licence $licence */
        $licence = $licenceRepo->fetchById($command->getId());
        $licence->setStatus($this->getRepo()->getRefdataReference(Licence::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION));
        $licenceRepo->save($licence);

        $this->result->addMessage($result);

        $this->result->merge($this->handleSideEffect(Snapshot::create(['id' => $command->getId()])));

        /** @var $surrender \Dvsa\Olcs\Api\Entity\Surrender */
        $surrender = $this->getRepo()->fetchOneByLicenceId($command->getId(), Query::HYDRATE_OBJECT);
        $surrenderId = $surrender->getId();

        $this->result->merge($this->createSurrenderTask($command->getId(), $surrenderId));

        $this->handleEventHistory($licence, EventHistoryType::EVENT_CODE_SURRENDER_UNDER_CONSIDERATION);

        return $this->result;
    }

    private function createSurrenderTask($licId, $surrenderId)
    {
        $actionDate = new DateTime();
        $actionDate->add(new \DateInterval('P14D'));

        $taskData = [
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_SURRENDER,
            'description' => 'Digital surrender',
            'isClosed' => 'N',
            'urgent' => 'N',
            'licence' => $licId,
            'surrender' => $surrenderId,
            'actionDate' => $actionDate->format('Y-m-d')
        ];

        return $this->handleSideEffect(CreateTask::create($taskData));
    }
}
