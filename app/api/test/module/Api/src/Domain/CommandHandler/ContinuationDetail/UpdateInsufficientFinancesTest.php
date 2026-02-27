<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ContinuationDetail;

use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail\UpdateInsufficientFinances as CommandHandler;
use Dvsa\Olcs\Transfer\Command\ContinuationDetail\UpdateInsufficientFinances as UpdateCommand;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;

/**
 * UpdateInsufficientFinancesTest
 */
class UpdateInsufficientFinancesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ContinuationDetail', \Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 3,
            'version' => 7,
            'financialEvidenceUploaded' => true,
        ];
        $command = UpdateCommand::create($data);

        $continuationDetail = new ContinuationDetailEntity();
        $continuationDetail->setId(154);

        $this->repoMap['ContinuationDetail']->shouldReceive('fetchById')->with(3, Query::HYDRATE_OBJECT, 7)->once()
            ->andReturn($continuationDetail);

        $this->repoMap['ContinuationDetail']->shouldReceive('save')->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame($data['financialEvidenceUploaded'], $continuationDetail->getFinancialEvidenceUploaded());

        $this->assertEquals(['ContinuationDetail insufficient finances updated'], $result->getMessages());
        $this->assertEquals(['continuationDetail' => 154], $result->getIds());
    }
}
