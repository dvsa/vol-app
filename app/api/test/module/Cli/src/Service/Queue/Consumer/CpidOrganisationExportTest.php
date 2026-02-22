<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue\Consumer;

use Doctrine\ORM\Internal\Hydration\IterableResult;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\CpidOrganisationExport;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Cli\Service\Queue\Consumer\CpidOrganisationExport
 */
class CpidOrganisationExportTest extends AbstractConsumerTestCase
{
    protected $queueEntity = null;

    /** @var Repository\Organisation */
    private $organisationRepo;

    public function setUp(): void
    {
        $user = new Entity\User\User('pid', 'type');
        $user->setId(1);

        $this->queueEntity = (new Queue())
            ->setType(new RefData(Queue::TYPE_CPID_EXPORT_CSV))
            ->setStatus(new RefData(Queue::STATUS_QUEUED))
            ->setCreatedBy($user)
            ->setOptions(json_encode(['status' => 'unit_Status']));

        $row = ['A1', 'B1', 'C1'];

        $this->organisationRepo = m::mock(Repository\Organisation::class);
        $this->organisationRepo->shouldReceive('fetchAllByStatusForCpidExport')
            ->with('unit_Status')
            ->andReturn(
                m::mock(IterableResult::class)
                    ->makePartial()
                    ->shouldReceive('next')
                    ->twice()
                    ->andReturn([$row], false)
                    ->getMock()
            );

        parent::setUp();
    }

    #[\Override]
    protected function instantiate(): void
    {
        $this->sut = m::mock(
            CpidOrganisationExport::class . '[success, failed]',
            [
                $this->abstractConsumerServices,
                $this->organisationRepo
            ]
        )->shouldAllowMockingProtectedMethods();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestMessageProvider')]
    public function testProcessMessage(mixed $shouldThrowException): void
    {
        /** @var m\MockInterface $sut */
        if ($shouldThrowException) {
            $this->chm->shouldReceive('handleCommand')
                ->once()
                ->andThrow(new \Exception('AN EXCEPTION'));

            $expectResult = 'failed';

            $this->sut->shouldReceive('failed')
                ->once()
                ->with($this->queueEntity, 'Unable to export list. AN EXCEPTION')
                ->andReturn($expectResult);
        } else {
            $this->chm->shouldReceive('handleCommand')
                ->once()
                ->andReturnUsing(
                    function (TransferCmd\Document\Upload $cmd) {
                        static::assertEquals(base64_encode("A1,B1,C1\n"), $cmd->getContent());
                    }
                );

            $expectResult = 'success';

            $this->sut->shouldReceive('success')
                ->once()
                ->with($this->queueEntity, 'Organisation list exported.')
                ->andReturn($expectResult);
        }

        static::assertEquals(
            $this->sut->processMessage($this->queueEntity),
            $expectResult
        );
    }

    public static function dpTestMessageProvider(): array
    {
        return [
            [
                'shouldThrowException' => false,
            ],
            [
                'shouldThrowException' => true,
            ],
        ];
    }
}
