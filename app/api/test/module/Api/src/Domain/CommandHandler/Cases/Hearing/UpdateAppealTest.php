<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Hearing;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Hearing\UpdateAppeal;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Cases\Appeal as AppealEntity;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Hearing\UpdateAppeal::class)]
class UpdateAppealTest extends AbstractCommandHandlerTestCase
{
    /** @var  UpdateAppeal */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new UpdateAppeal();

        $this->mockRepo('Appeal', Repository\Appeal::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'appeal_r_lic_non_pi',
            'appeal_o_dis'
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $id = 99;
        $version = 2;
        $appealDate = '2015-01-05';
        $appealNo = '12332';
        $outlineGround = 'dsfsfsf';
        $reason = 'appeal_r_lic_non_pi';
        $outcome = 'appeal_o_dis';
        $comment = 'comment';
        $isWithdrawn = 'Y';
        $hearingDate = '2015-05-01';
        $decisionDate = '2015-05-06';
        $papersDueTcDate = '2015-05-07';
        $papersDueDate = '2015-05-08';
        $papersSentTcDate = '2015-05-09';
        $papersSentDate = '2015-05-10';
        $withdrawnDate = '2015-05-11';
        $deadlineDate = '2015-05-12';
        $dvsaNotified = 'Y';

        $command = TransferCmd\Cases\Hearing\UpdateAppeal::create(
            [
                "id" => $id,
                "version" => $version,
                "appealDate" => $appealDate,
                "appealNo" => $appealNo,
                "outlineGround" => $outlineGround,
                "reason" => $reason,
                "outcome" => $outcome,
                "comment" => $comment,
                "isWithdrawn" => $isWithdrawn,
                "hearingDate" => $hearingDate,
                "decisionDate" => $decisionDate,
                "papersDueTcDate" => $papersDueTcDate,
                "papersDueDate" => $papersDueDate,
                "papersSentTcDate" => $papersSentTcDate,
                "papersSentDate" => $papersSentDate,
                "withdrawnDate" => $withdrawnDate,
                "deadlineDate" => $deadlineDate,
                "dvsaNotified" => $dvsaNotified
            ]
        );

        /** @var AppealEntity $appeal */
        $appeal = m::mock(AppealEntity::class)->makePartial();
        $appeal->shouldReceive('update')
            ->once()
            ->with(
                $this->refData[$reason],
                $appealDate,
                $appealNo,
                $deadlineDate,
                $outlineGround,
                $hearingDate,
                $decisionDate,
                $papersDueDate,
                $papersDueTcDate,
                $papersSentDate,
                $papersSentTcDate,
                $comment,
                $this->refData[$outcome],
                $isWithdrawn,
                $withdrawnDate,
                $dvsaNotified
            );

        $this->repoMap['Appeal']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $command->getVersion())
            ->andReturn($appeal)
            ->once()
            ->shouldReceive('save')
            ->with(m::type(AppealEntity::class))
            ->andReturnUsing(
                function (AppealEntity $appeal) {
                    $appeal->setId(99);
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Appeal updated', $result->getMessages());
    }
}
