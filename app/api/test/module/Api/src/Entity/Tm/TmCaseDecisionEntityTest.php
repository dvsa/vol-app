<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Tm;

use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Tm\TmCaseDecision as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * TmCaseDecision Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class TmCaseDecisionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstruct(): void
    {
        $case = m::mock(CasesEntity::class);
        $decision = m::mock(RefData::class);

        $entity = new Entity($case, $decision);

        $this->assertSame($case, $entity->getCase());
        $this->assertSame($decision, $entity->getDecision());
    }

    public function testCreateForReputeNotLost(): void
    {
        $data = [
            'case' => 11,
            'isMsi' => 'Y',
            'decisionDate' => '2016-01-01',
            'notifiedDate' => '2016-01-01',
            'reputeNotLostReason' => 'testing',
        ];

        $case = m::mock(CasesEntity::class);

        $decision = m::mock(RefData::class)->makePartial();
        $decision->setId(Entity::DECISION_REPUTE_NOT_LOST);

        $entity = Entity::create($case, $decision, $data);

        $this->assertSame($case, $entity->getCase());
        $this->assertSame($decision, $entity->getDecision());
        $this->assertEquals($data['isMsi'], $entity->getIsMsi());
        $this->assertInstanceOf(\DateTime::class, $entity->getDecisionDate());
        $this->assertEquals($data['decisionDate'], $entity->getDecisionDate()->format('Y-m-d'));
        $this->assertInstanceOf(\DateTime::class, $entity->getNotifiedDate());
        $this->assertEquals($data['notifiedDate'], $entity->getNotifiedDate()->format('Y-m-d'));
        $this->assertEquals($data['reputeNotLostReason'], $entity->getReputeNotLostReason());
    }

    public function testUpdateForReputeNotLost(): void
    {
        $data = [
            'isMsi' => 'Y',
            'decisionDate' => '2016-01-01',
            'notifiedDate' => '2016-01-01',
            'reputeNotLostReason' => 'testing',
        ];

        $case = m::mock(CasesEntity::class);

        $decision = m::mock(RefData::class)->makePartial();
        $decision->setId(Entity::DECISION_REPUTE_NOT_LOST);

        $entity = new Entity($case, $decision);

        // set existing data on the entity before update
        $entity->setIsMsi('N');
        $entity->setDecisionDate(new \DateTime('2015-10-12'));

        $entity->update($data);

        $this->assertSame($case, $entity->getCase());
        $this->assertSame($decision, $entity->getDecision());
        $this->assertEquals($data['isMsi'], $entity->getIsMsi());
        $this->assertInstanceOf(\DateTime::class, $entity->getDecisionDate());
        $this->assertEquals($data['decisionDate'], $entity->getDecisionDate()->format('Y-m-d'));
        $this->assertInstanceOf(\DateTime::class, $entity->getNotifiedDate());
        $this->assertEquals($data['notifiedDate'], $entity->getNotifiedDate()->format('Y-m-d'));
        $this->assertEquals($data['reputeNotLostReason'], $entity->getReputeNotLostReason());
    }

    public function testCreateForNoFurtherAction(): void
    {
        $data = [
            'case' => 11,
            'isMsi' => 'Y',
            'decisionDate' => '2016-01-01',
            'notifiedDate' => '2016-01-01',
            'noFurtherActionReason' => 'testing',
        ];

        $case = m::mock(CasesEntity::class);

        $decision = m::mock(RefData::class)->makePartial();
        $decision->setId(Entity::DECISION_NO_FURTHER_ACTION);

        $entity = Entity::create($case, $decision, $data);

        $this->assertSame($case, $entity->getCase());
        $this->assertSame($decision, $entity->getDecision());
        $this->assertEquals($data['isMsi'], $entity->getIsMsi());
        $this->assertInstanceOf(\DateTime::class, $entity->getDecisionDate());
        $this->assertEquals($data['decisionDate'], $entity->getDecisionDate()->format('Y-m-d'));
        $this->assertInstanceOf(\DateTime::class, $entity->getNotifiedDate());
        $this->assertEquals($data['notifiedDate'], $entity->getNotifiedDate()->format('Y-m-d'));
        $this->assertEquals($data['noFurtherActionReason'], $entity->getNoFurtherActionReason());
    }

    public function testUpdateForNoFurtherAction(): void
    {
        $data = [
            'isMsi' => 'Y',
            'decisionDate' => '2016-01-01',
            'notifiedDate' => '2016-01-01',
            'noFurtherActionReason' => 'testing',
        ];

        $case = m::mock(CasesEntity::class);

        $decision = m::mock(RefData::class)->makePartial();
        $decision->setId(Entity::DECISION_NO_FURTHER_ACTION);

        $entity = new Entity($case, $decision);

        // set existing data on the entity before update
        $entity->setIsMsi('N');
        $entity->setDecisionDate(new \DateTime('2015-10-12'));

        $entity->update($data);

        $this->assertSame($case, $entity->getCase());
        $this->assertSame($decision, $entity->getDecision());
        $this->assertEquals($data['isMsi'], $entity->getIsMsi());
        $this->assertInstanceOf(\DateTime::class, $entity->getDecisionDate());
        $this->assertEquals($data['decisionDate'], $entity->getDecisionDate()->format('Y-m-d'));
        $this->assertInstanceOf(\DateTime::class, $entity->getNotifiedDate());
        $this->assertEquals($data['notifiedDate'], $entity->getNotifiedDate()->format('Y-m-d'));
        $this->assertEquals($data['noFurtherActionReason'], $entity->getNoFurtherActionReason());
    }

    public function testCreateForDeclareUnfit(): void
    {
        $unfitnessReason = m::mock(RefData::class)->makePartial();
        $unfitnessReason->setId('unfit');

        $rehabMeasure = m::mock(RefData::class)->makePartial();
        $rehabMeasure->setId('rehab');

        $data = [
            'case' => 11,
            'isMsi' => 'Y',
            'decisionDate' => '2016-01-01',
            'notifiedDate' => '2016-01-01',
            'unfitnessStartDate' => '2016-02-01',
            'unfitnessEndDate' => '2016-02-01',
            'unfitnessReasons' => [$unfitnessReason],
            'rehabMeasures' => [$rehabMeasure],
        ];

        $case = m::mock(CasesEntity::class);

        $decision = m::mock(RefData::class)->makePartial();
        $decision->setId(Entity::DECISION_DECLARE_UNFIT);

        $entity = Entity::create($case, $decision, $data);

        $this->assertSame($case, $entity->getCase());
        $this->assertSame($decision, $entity->getDecision());
        $this->assertEquals($data['isMsi'], $entity->getIsMsi());
        $this->assertInstanceOf(\DateTime::class, $entity->getDecisionDate());
        $this->assertEquals($data['decisionDate'], $entity->getDecisionDate()->format('Y-m-d'));
        $this->assertInstanceOf(\DateTime::class, $entity->getNotifiedDate());
        $this->assertEquals($data['notifiedDate'], $entity->getNotifiedDate()->format('Y-m-d'));
        $this->assertInstanceOf(\DateTime::class, $entity->getUnfitnessStartDate());
        $this->assertEquals($data['unfitnessStartDate'], $entity->getUnfitnessStartDate()->format('Y-m-d'));
        $this->assertInstanceOf(\DateTime::class, $entity->getUnfitnessEndDate());
        $this->assertEquals($data['unfitnessEndDate'], $entity->getUnfitnessEndDate()->format('Y-m-d'));
        $this->assertEquals($data['unfitnessReasons'], $entity->getUnfitnessReasons());
        $this->assertEquals($data['rehabMeasures'], $entity->getRehabMeasures());
    }

    public function testUpdateForDeclareUnfit(): void
    {
        $unfitnessReason = m::mock(RefData::class)->makePartial();
        $unfitnessReason->setId('unfit');

        $rehabMeasure = m::mock(RefData::class)->makePartial();
        $rehabMeasure->setId('rehab');

        $data = [
            'isMsi' => 'Y',
            'decisionDate' => '2016-01-01',
            'notifiedDate' => '2016-01-01',
            'unfitnessStartDate' => '2016-02-01',
            'unfitnessEndDate' => '2016-02-01',
            'unfitnessReasons' => [$unfitnessReason],
            'rehabMeasures' => [$rehabMeasure],
        ];

        $case = m::mock(CasesEntity::class);

        $decision = m::mock(RefData::class)->makePartial();
        $decision->setId(Entity::DECISION_DECLARE_UNFIT);

        $entity = new Entity($case, $decision);

        // set existing data on the entity before update
        $entity->setIsMsi('N');
        $entity->setDecisionDate(new \DateTime('2015-10-12'));

        $entity->update($data);

        $this->assertSame($case, $entity->getCase());
        $this->assertSame($decision, $entity->getDecision());
        $this->assertEquals($data['isMsi'], $entity->getIsMsi());
        $this->assertInstanceOf(\DateTime::class, $entity->getDecisionDate());
        $this->assertEquals($data['decisionDate'], $entity->getDecisionDate()->format('Y-m-d'));
        $this->assertInstanceOf(\DateTime::class, $entity->getNotifiedDate());
        $this->assertEquals($data['notifiedDate'], $entity->getNotifiedDate()->format('Y-m-d'));
        $this->assertInstanceOf(\DateTime::class, $entity->getUnfitnessStartDate());
        $this->assertEquals($data['unfitnessStartDate'], $entity->getUnfitnessStartDate()->format('Y-m-d'));
        $this->assertInstanceOf(\DateTime::class, $entity->getUnfitnessEndDate());
        $this->assertEquals($data['unfitnessEndDate'], $entity->getUnfitnessEndDate()->format('Y-m-d'));
        $this->assertEquals($data['unfitnessReasons'], $entity->getUnfitnessReasons());
        $this->assertEquals($data['rehabMeasures'], $entity->getRehabMeasures());
    }

    public function testUpdateForDeclareUnfitThrowsIncorrectNotifiedDateException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $data = [
            'case' => 11,
            'isMsi' => 'Y',
            'decisionDate' => '2016-01-02',
            'notifiedDate' => '2016-01-01',
            'unfitnessStartDate' => '2016-02-01',
            'unfitnessEndDate' => '2016-02-01',
            'unfitnessReasons' => ['unfitnessReason'],
            'rehabMeasures' => ['rehabMeasure'],
        ];

        $case = m::mock(CasesEntity::class);

        $decision = m::mock(RefData::class)->makePartial();
        $decision->setId(Entity::DECISION_DECLARE_UNFIT);

        $entity = new Entity($case, $decision);
        $entity->update($data);
    }

    public function testUpdateForDeclareUnfitThrowsIncorrectUnfitnessEndDateException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $data = [
            'case' => 11,
            'isMsi' => 'Y',
            'decisionDate' => '2016-01-01',
            'notifiedDate' => '2016-01-01',
            'unfitnessStartDate' => '2016-02-02',
            'unfitnessEndDate' => '2016-02-01',
            'unfitnessReasons' => ['unfitnessReason'],
            'rehabMeasures' => ['rehabMeasure'],
        ];

        $case = m::mock(CasesEntity::class);

        $decision = m::mock(RefData::class)->makePartial();
        $decision->setId(Entity::DECISION_DECLARE_UNFIT);

        $entity = new Entity($case, $decision);
        $entity->update($data);
    }
}
