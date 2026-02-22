<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\Exception\ORMException;
use Dvsa\Olcs\Api\Domain\Command\ContinuationDetail\Process;
use Dvsa\Olcs\Api\Domain\Command\Queue\Complete;
use Dvsa\Olcs\Api\Domain\Command\Queue\Failed;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\Exception;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\ContinuationChecklist;
use Dvsa\Olcs\Transfer\Command\ContinuationDetail\Update;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Cli\Service\Queue\Consumer\ContinuationChecklist::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer::class)]
class ContinuationChecklistTest extends AbstractConsumerTestCase
{
    protected $consumerClass = ContinuationChecklist::class;

    /** @var  ContinuationChecklist */
    protected $sut;

    public function testProcessMessageSuccess(): void
    {
        $user = new UserEntity('pid', 'type');
        $user->setId(1);
        $item = new QueueEntity();
        $item->setId(99);
        $item->setEntityId(69);
        $item->setCreatedBy($user);

        $expectedDtoData = ['id' => 69, 'user' => 1];
        $cmdResult = new Result();
        $cmdResult
            ->addId('continuationDetail', 69)
            ->addId('document', 101)
            ->addMessage('ContinuationDetail updated')
            ->addMessage('Document created');

        $this->expectCommand(
            Process::class,
            $expectedDtoData,
            $cmdResult
        );

        $this->expectCommand(
            Complete::class,
            ['item' => $item],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($item);

        $this->assertEquals(
            'Successfully processed message: 99  ContinuationDetail updated, Document created',
            $result
        );
    }

    public function testProcessMessageFailure(): void
    {
        $user = new UserEntity('pid', 'type');
        $user->setId(1);
        $item = new QueueEntity();
        $item->setId(99);
        $item->setEntityId(69);
        $item->setCreatedBy($user);

        $this->expectCommandException(
            Process::class,
            ['id' => 69, 'user' => 1],
            Exception::class,
            'epic fail'
        );

        $this->expectCommandException(
            Update::class,
            [
                'id' => 69,
                'status' => ContinuationDetailEntity::STATUS_ERROR,
                'version' => null,
                'received' => null,
                'totAuthVehicles' => null,
                'totPsvDiscs' => null,
                'totCommunityLicences' => null,
            ],
            Exception::class,
            'marking as fail failed'
        );

        $this->expectCommand(
            Failed::class,
            [
                'item' => $item,
                'lastError' => 'epic fail, marking as fail failed',
            ],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($item);

        $this->assertEquals(
            'Failed to process message: 99  epic fail, marking as fail failed',
            $result
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandledExceptionProvider')]
    public function testProcessMessageFailureHandledExceptions(mixed $exception, mixed $exceptionMessageString): void
    {
        $user = new UserEntity('pid', 'type');
        $user->setId(1);
        $item = new QueueEntity();
        $item->setId(99);
        $item->setEntityId(69);
        $item->setCreatedBy($user);

        $this->expectCommandException(
            Process::class,
            ['id' => 69, 'user' => 1],
            $exception,
            $exceptionMessageString
        );

        $this->expectCommandException(
            Update::class,
            [
                'id' => 69,
                'status' => ContinuationDetailEntity::STATUS_ERROR,
                'version' => null,
                'received' => null,
                'totAuthVehicles' => null,
                'totPsvDiscs' => null,
                'totCommunityLicences' => null,
            ],
            Exception::class,
            'marking as fail failed'
        );

        $this->expectCommand(
            Failed::class,
            [
                'item' => $item,
                'lastError' => "{$exceptionMessageString}, marking as fail failed",
            ],
            new Result(),
            false
        );

        $result = $this->sut->processMessage($item);

        $this->assertEquals(
            "Failed to process message: 99  {$exceptionMessageString}, marking as fail failed",
            $result
        );
    }

    public static function dpHandledExceptionProvider(): array
    {
        return [
            [ORMException::class, 'ORM Exception'],
            [DBALException::class, 'Database Failure']
        ];
    }
}
