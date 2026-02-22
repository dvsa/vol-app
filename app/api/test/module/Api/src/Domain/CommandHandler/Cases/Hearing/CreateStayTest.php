<?php

declare(strict_types=1);

/**
 * Create Stay Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Hearing;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Hearing\CreateStay;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Appeal as AppealRepo;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\Olcs\Api\Domain\Repository\Stay as StayRepo;
use Dvsa\Olcs\Api\Entity\Cases\Appeal as AppealEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Cases\Stay as StayEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Hearing\CreateStay as Cmd;
use Mockery as m;

/**
 * Create Stay Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class CreateStayTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateStay();
        $this->mockRepo('Stay', StayRepo::class);
        $this->mockRepo('Cases', CasesRepo::class);
        $this->mockRepo('Appeal', AppealRepo::class);

        $this->refData = [
            'stay_t_ut',
        ];

        parent::setUp();
    }

    public function testHandleCommandExistingAppeal(): void
    {
        $mockAppeal = m::mock(AppealEntity::class);
        $mockCase = m::mock(CasesEntity::class)->makePartial();
        $mockCase->shouldReceive('getAppeal')
            ->andReturn($mockAppeal);

        $mockCase->shouldReceive('getStays')
            ->andReturn(new ArrayCollection([]));

        $this->references = [
            CasesEntity::class => [
                24 => $mockCase
            ]
        ];

        parent::initReferences();

        $command = Cmd::create(
            [
                "case" => 24,
                "stayType" => "stay_t_ut",
                "requestDate" => "2015-01-05",
                "decisionDate" => "2015-01-09",
                "outcome" => "stay_s_granted",
                "notes" => "booo",
                "isWithdrawn" => "Y",
                "withdrawnDate" => "2015-05-05"
            ]
        );

        /** @var StayEntity $stay */
        $s = null;

        $this->repoMap['Stay']
            ->shouldReceive('save')
            ->with(m::type(StayEntity::class))
            ->andReturnUsing(
                function (StayEntity $stay) use (&$s) {
                    $s = $stay;
                    $stay->setId(99);
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Stay created', $result->getMessages());
    }

    public function testHandleCommandNoExistingAppeal(): void
    {
        $mockCase = m::mock(CasesEntity::class)->makePartial();
        $mockCase->shouldReceive('getAppeal')
            ->andReturn(null);

        $this->references = [
            CasesEntity::class => [
                24 => $mockCase
            ]
        ];

        parent::initReferences();

        $command = Cmd::create(
            [
                "case" => 24,
                "stayType" => "stay_t_ut",
                "requestDate" => "2015-01-05",
                "decisionDate" => "2015-01-09",
                "outcome" => "stay_s_granted",
                "notes" => "booo",
                "isWithdrawn" => "Y",
                "withdrawnDate" => "2015-05-05"
            ]
        );

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandStayTypeAlreadyExists(): void
    {
        $mockAppeal = m::mock(AppealEntity::class);

        $mockCase = m::mock(CasesEntity::class)->makePartial();
        $mockCase->shouldReceive('hasStayType')->andReturn(true);

        $mockCase->shouldReceive('getAppeal')
            ->andReturn($mockAppeal);

        $this->references = [
            CasesEntity::class => [
                24 => $mockCase
            ]
        ];

        parent::initReferences();

        $command = Cmd::create(
            [
                "case" => 24,
                "stayType" => "stay_t_ut",
                "requestDate" => "2015-01-05",
                "decisionDate" => "2015-01-09",
                "outcome" => "stay_s_granted",
                "notes" => "booo",
                "isWithdrawn" => "Y",
                "withdrawnDate" => "2015-05-05"
            ]
        );

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }
}
