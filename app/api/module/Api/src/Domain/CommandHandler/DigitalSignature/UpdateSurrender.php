<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\DigitalSignature;

use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateSurrender as UpdateSurrenderCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Surrender\Snapshot;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Repository\Surrender as SurrenderRepo;
use Dvsa\Olcs\Api\Entity\DigitalSignature;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Surrender as SurrenderEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

final class UpdateSurrender extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Surrender';

    public function __construct(private readonly EventHistoryCreator $eventHistoryCreator)
    {
    }

    public function handleCommand(CommandInterface $command): Result
    {
        assert($command instanceof UpdateSurrenderCmd);

        $repo = $this->getRepo('Surrender');
        assert($repo instanceof SurrenderRepo);

        $licenceId = (int)$command->getLicence();
        $surrender = $repo->fetchOneByLicenceId($licenceId);
        assert($surrender instanceof SurrenderEntity);

        $licenceStatus = $repo->getRefdataReference(LicenceEntity::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION);
        $surrenderStatus = $repo->getRefdataReference(SurrenderEntity::SURRENDER_STATUS_SIGNED);
        $signatureType = $repo->getRefdataReference(RefData::SIG_DIGITAL_SIGNATURE);
        $digitalSignature = $repo->getReference(DigitalSignature::class, $command->getDigitalSignature());

        $surrender->updateDigitalSignature($licenceStatus, $surrenderStatus, $signatureType, $digitalSignature);
        $repo->save($surrender);

        $this->result->addMessage('Digital signature added to Surrender for Licence ' . $licenceId);
        $this->result->merge($this->handleSideEffect(Snapshot::create(['id' => $licenceId])));
        $this->result->merge($this->handleSideEffect($this->createSurrenderTaskCmd($licenceId)));

        $this->eventHistoryCreator->create(
            $surrender->getLicence(),
            EventHistoryType::EVENT_CODE_SURRENDER_UNDER_CONSIDERATION
        );

        return $this->result;
    }

    private function createSurrenderTaskCmd(int $licenceId): CreateTask
    {
        $taskData = [
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_SURRENDER,
            'description' => 'Digital surrender',
            'isClosed' => 'N',
            'urgent' => 'N',
            'licence' => $licenceId,
        ];

        return CreateTask::create($taskData);
    }
}
