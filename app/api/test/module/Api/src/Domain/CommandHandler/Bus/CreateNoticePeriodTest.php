<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus;

use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\CreateNoticePeriod as Handler;
use Dvsa\Olcs\Transfer\Command\Bus\CreateNoticePeriod as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\BusNoticePeriod as BusNoticePeriodRepo;
use Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod as BusNoticePeriodEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * @see Handler
 */
class CreateNoticePeriodTest extends AbstractCommandHandlerTestCase
{
    private $repo = 'BusNoticePeriod';

    public function setUp(): void
    {
        $this->sut = new Handler();
        $this->mockRepo($this->repo, BusNoticePeriodRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $noticePeriodId = 111;
        $standardPeriod = 42;
        $noticeArea = 'notice area';

        $cmdData = [
            'noticeArea' => $noticeArea,
            'standardPeriod' => $standardPeriod,
        ];

        $command = Cmd::create($cmdData);

        $this->repoMap[$this->repo]->expects('save')
            ->with(m::type(BusNoticePeriodEntity::class))
            ->andReturnUsing(
                function (BusNoticePeriodEntity $entity) use (&$saved, $noticePeriodId, $standardPeriod, $noticeArea) {
                    $entity->setId($noticePeriodId);
                    $entity->setStandardPeriod($standardPeriod);
                    $entity->setNoticeArea($noticeArea);
                    $saved = $entity;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                $this->repo => $noticePeriodId,
            ],
            'messages' => [
                Handler::SUCCESS_MSG,
            ],
        ];

        $this->assertEquals($expected, $result->toArray());
        $this->assertEquals($noticeArea, $saved->getNoticeArea());
        $this->assertEquals($standardPeriod, $saved->getStandardPeriod());
    }
}
