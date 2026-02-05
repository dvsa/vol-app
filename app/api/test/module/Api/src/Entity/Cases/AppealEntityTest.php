<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Cases;

use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Cases\Appeal as Entity;
use Mockery as m;

/**
 * Appeal Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class AppealEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Tests updating an appeal
     */
    public function testUpdate(): void
    {
        $reason = m::mock(RefData::class);
        $appealDate = '2017-05-10';
        $appealNo = '235235';
        $deadlineDate = '2017-05-11';
        $outlineGround = 'outline ground';
        $hearingDate = '2017-05-12';
        $decisionDate = '2017-05-13';
        $papersDueDate = '2017-05-14';
        $papersDueTcDate = '2017-05-15';
        $papersSentDate = '2017-05-16';
        $papersSentTcDate = '2017-05-17';
        $comment = 'comment';
        $outcome = m::mock(RefData::class);
        $isWithdrawn = 'Y';
        $withdrawnDate = '2017-05-18';
        $dvsaNotified = 'Y';

        $entity = new Entity('appealNo1234');
        $returnedValue = $entity->update(
            $reason,
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
            $outcome,
            $isWithdrawn,
            $withdrawnDate,
            $dvsaNotified
        );

        $this->assertInstanceOf(Entity::class, $returnedValue);
        $this->assertSame($reason, $entity->getReason());
        $this->assertEquals($appealDate, $entity->getAppealDate()->format('Y-m-d'));
        $this->assertEquals($appealNo, $entity->getAppealNo());
        $this->assertEquals($deadlineDate, $entity->getDeadlineDate()->format('Y-m-d'));
        $this->assertEquals($outlineGround, $entity->getOutlineGround());
        $this->assertEquals($hearingDate, $entity->getHearingDate()->format('Y-m-d'));
        $this->assertEquals($decisionDate, $entity->getDecisionDate()->format('Y-m-d'));
        $this->assertEquals($papersDueDate, $entity->getPapersDueDate()->format('Y-m-d'));
        $this->assertEquals($papersDueTcDate, $entity->getPapersDueTcDate()->format('Y-m-d'));
        $this->assertEquals($papersSentDate, $entity->getPapersSentDate()->format('Y-m-d'));
        $this->assertEquals($papersSentTcDate, $entity->getPapersSentTcDate()->format('Y-m-d'));
        $this->assertEquals($comment, $entity->getComment());
        $this->assertSame($outcome, $entity->getOutcome());
        $this->assertEquals($withdrawnDate, $entity->getWithdrawnDate()->format('Y-m-d'));
        $this->assertEquals($dvsaNotified, $entity->getDvsaNotified());
    }

    /**
     * Tests existing withdrawn date is removed and new withdrawn date is ignored, when isWithdrawn is set to 'N'
     */
    public function testUpdateNotWithdrawn(): void
    {
        $entity = new Entity('appealNo1234');
        $entity->setWithdrawnDate(new \DateTime('2017-05-20'));
        $entity->update(
            m::mock(RefData::class),
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            'N',
            '2017-05-20',
            null
        );

        $this->assertNull($entity->getWithdrawnDate());
    }
}
