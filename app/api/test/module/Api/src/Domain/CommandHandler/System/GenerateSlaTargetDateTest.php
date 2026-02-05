<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\System;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\System\GenerateSlaTargetDate as CommandHandler;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate as Command;
use Dvsa\Olcs\Api\Domain\Repository\Pi as PiRepo;
use Dvsa\Olcs\Api\Domain\Repository\ProposeToRevoke as ProposeToRevokeRepo;
use Dvsa\Olcs\Api\Domain\Repository\Sla as SlaRepo;
use Dvsa\Olcs\Api\Domain\Repository\SlaTargetDate as SlaTargetDateRepo;
use Dvsa\Olcs\Api\Domain\Repository\Statement as StatementRepo;
use Dvsa\Olcs\Api\Domain\Repository\Submission as SubmissionRepo;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculatorInterface;
use Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke as ProposeToRevokeEntity;
use Dvsa\Olcs\Api\Entity\Cases\Statement as StatementEntity;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Api\Entity\Submission\Submission as SubmissionEntity;
use Dvsa\Olcs\Api\Entity\System\Sla as SlaEntity;
use Dvsa\Olcs\Api\Entity\System\SlaTargetDate as SlaTargetDateEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Mockery as m;

/**
 * GenerateSlaTargetDate command handler test
 */
class GenerateSlaTargetDateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Pi', PiRepo::class);
        $this->mockRepo('Sla', SlaRepo::class);
        $this->mockRepo('SlaTargetDate', SlaTargetDateRepo::class);
        $this->mockRepo('Submission', SubmissionRepo::class);
        $this->mockRepo('ProposeToRevoke', ProposeToRevokeRepo::class);
        $this->mockRepo('Statement', StatementRepo::class);

        $this->mockedSmServices = [
            SlaCalculatorInterface::class => m::mock(SlaCalculatorInterface::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            TrafficAreaEntity::class => [
                TrafficAreaEntity::SE_MET_TRAFFIC_AREA_CODE => m::mock(TrafficAreaEntity::class),
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandForPublicInquiry(): void
    {
        $params = [
            'pi' => 111,
        ];
        $command = Command::create($params);

        $this->doTest($command, $params, 'pi', PiEntity::class, 'Pi', ['pi', 'pi_hearing']);
    }

    public function testHandleCommandForSubmission(): void
    {
        $params = [
            'submission' => 111,
        ];
        $command = Command::create($params);

        $this->doTest($command, $params, 'submission', SubmissionEntity::class, 'Submission', ['submission']);
    }

    public function testHandleCommandForProposeToRevoke(): void
    {
        $params = [
            'proposeToRevoke' => 111,
        ];
        $command = Command::create($params);

        $this->doTest($command, $params, 'proposeToRevoke', ProposeToRevokeEntity::class, 'ProposeToRevoke', ['ptr']);
    }

    public function testHandleCommandForStatement(): void
    {
        $params = [
            'statement' => 111,
        ];
        $command = Command::create($params);

        $this->doTest($command, $params, 'statement', StatementEntity::class, 'Statement', ['statement']);
    }

    public function doTest(Command $command, mixed $params, mixed $entityParam, mixed $entityClass, mixed $repoName, mixed $categories): void
    {

        $v1 = new \DateTime('2015-01-01');
        $v2 = new \DateTime('2015-02-02');

        $entity = m::mock($entityClass)->makePartial();
        $entity->setId($params[$entityParam]);
        $entity->setSlaTargetDates(new ArrayCollection());
        $entity->shouldReceive('getCase->isTm')->once()->andReturn(false);
        $entity->shouldReceive('getCase->getLicence->getTrafficArea')->once()
            ->andReturn($this->references[TrafficAreaEntity::class][TrafficAreaEntity::SE_MET_TRAFFIC_AREA_CODE]);
        $entity->shouldReceive('getField1')->andReturn($v1);
        $entity->shouldReceive('getField2')->andReturn($v2);
        $entity->shouldReceive('getField3')->andReturn(null);

        $this->repoMap[$repoName]->shouldReceive('fetchById')
            ->once()
            ->with($params[$entityParam])
            ->andReturn($entity)
            ->shouldReceive('save')
            ->once();

        $sla1 = m::mock(SlaEntity::class)->makePartial();
        $sla1->setId(1);
        $sla1->setCompareTo('field1');
        $sla1->shouldReceive('appliesTo')->with($v1)->once()->andReturn(true);

        $sla2 = m::mock(SlaEntity::class)->makePartial();
        $sla2->setId(2);
        $sla2->setCompareTo('field2');
        $sla2->shouldReceive('appliesTo')->with($v2)->once()->andReturn(true);

        $sla3 = m::mock(SlaEntity::class)->makePartial();
        $sla3->setId(3);
        $sla3->setCompareTo('field3');
        $sla3->shouldReceive('appliesTo')->never();

        $slaTargetDate2 = m::mock(SlaTargetDateEntity::class)->makePartial();
        $slaTargetDate2->setSla($sla2);
        $slaTargetDate2->setAgreedDate(new \DateTime('2014-02-02'));
        $entity->getSlaTargetDates()->set($sla2->getId(), $slaTargetDate2);

        $slaTargetDate3 = m::mock(SlaTargetDateEntity::class)->makePartial();
        $slaTargetDate3->setSla($sla3);
        $slaTargetDate3->setAgreedDate(new \DateTime('2014-03-03'));
        $entity->getSlaTargetDates()->set($sla3->getId(), $slaTargetDate3);

        $v1Sla = new \DateTime('2016-01-01');
        $v2Sla = new \DateTime('2016-02-02');
        $this->mockedSmServices[SlaCalculatorInterface::class]
            ->shouldReceive('applySla')
            ->once()
            ->with($v1, $sla1, $this->references[TrafficAreaEntity::class][TrafficAreaEntity::SE_MET_TRAFFIC_AREA_CODE])
            ->andReturn($v1Sla)
            ->shouldReceive('applySla')
            ->once()
            ->with($v2, $sla2, $this->references[TrafficAreaEntity::class][TrafficAreaEntity::SE_MET_TRAFFIC_AREA_CODE])
            ->andReturn($v2Sla);

        $this->repoMap['Sla']->shouldReceive('fetchByCategories')
            ->once()
            ->with($categories, Query::HYDRATE_OBJECT)
            ->andReturn([$sla1, $sla2, $sla3]);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['SLA Target Dates successfully saved'], $result->getMessages());

        $this->assertEquals(2, $entity->getSlaTargetDates()->count());

        $std1 = $entity->getSlaTargetDates()->get($sla1->getId());
        $this->assertEquals($sla1, $std1->getSla());
        $this->assertEquals($v1, $std1->getAgreedDate());
        $this->assertEquals($v1Sla, $std1->getTargetDate());

        $std2 = $entity->getSlaTargetDates()->get($sla2->getId());
        $this->assertEquals($sla2, $std2->getSla());
        $this->assertEquals($v2, $std2->getAgreedDate());
        $this->assertEquals($v2Sla, $std2->getTargetDate());
    }
}
