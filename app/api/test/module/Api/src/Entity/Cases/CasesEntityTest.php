<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Cases;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest as ErruRequestEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Cases\Cases as Entity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Cases\Complaint as ComplaintEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Cases\Appeal as AppealEntity;
use Dvsa\Olcs\Api\Entity\Cases\Stay as StayEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Note\Note as NoteEntity;
use Doctrine\Common\Collections\Criteria;

/**
 * Cases Entity Unit Tests
 * Initially auto-generated but won't be overridden
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Cases\Cases::class)]
class CasesEntityTest extends EntityTester
{
    /** @var  \Dvsa\Olcs\Api\Entity\Cases\Cases */
    protected $entity;

    public function setUp(): void
    {
        /** @var \Dvsa\Olcs\Api\Entity\Cases\Cases entity */
        $this->entity = $this->instantiate($this->entityClass);
    }

    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Tests update
     */
    public function testCreate(): void
    {
        $caseType = m::mock(RefData::class);
        $openDate = new \DateTime();
        $categorys = new ArrayCollection();
        $outcomes = new ArrayCollection();
        $ecmsNo = 'abcd123456';
        $description = 'description';
        $correctLicenceId = 7;
        $incorrectLicenceId = 99;

        $postedLicence = m::mock(LicenceEntity::class);
        $postedLicence->shouldReceive('getId')->andReturn($incorrectLicenceId);

        $applicationLicence = m::mock(LicenceEntity::class);
        $applicationLicence->shouldReceive('getId')->andReturn($correctLicenceId);
        $application = m::mock(ApplicationEntity::class);
        $application->shouldReceive('canCreateCase')->andReturn(true);
        $application->shouldReceive('getLicence')->andReturn($applicationLicence);
        $transportManager = null;

        $sut = new Entity(
            $openDate,
            $caseType,
            $categorys,
            $outcomes,
            $application,
            $postedLicence,
            $transportManager,
            $ecmsNo,
            $description
        );

        $this->assertEquals($caseType, $sut->getCaseType());
        $this->assertEquals($categorys, $sut->getCategorys());
        $this->assertEquals($outcomes, $sut->getOutcomes());
        $this->assertEquals($ecmsNo, $sut->getEcmsNo());
        $this->assertEquals($description, $sut->getDescription());
        $this->assertEquals($correctLicenceId, $sut->getLicence()->getId());
        $this->assertEquals($application, $sut->getApplication());
        $this->assertEquals($transportManager, $sut->getTransportManager());
    }

    /**
     * Tests create function throws an exception when application can't create cases
     */
    public function testCreateThrowsExceptionWhenApplicationCantCreate(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $caseType = m::mock(RefData::class);
        $openDate = new \DateTime();
        $categorys = new ArrayCollection();
        $outcomes = new ArrayCollection();
        $ecmsNo = 'abcd123456';
        $description = 'description';
        $postedLicence = m::mock(LicenceEntity::class);
        $application = m::mock(ApplicationEntity::class);
        $application->shouldReceive('canCreateCase')->andReturn(false);
        $transportManager = null;

        $sut = new Entity(
            $openDate,
            $caseType,
            $categorys,
            $outcomes,
            $application,
            $postedLicence,
            $transportManager,
            $ecmsNo,
            $description
        );
    }

    /**
     * Tests update
     */
    public function testUpdate(): void
    {
        $caseType = Entity::LICENCE_CASE_TYPE;

        $categorys = new ArrayCollection();
        $outcomes = new ArrayCollection();
        $ecmsNo = 'abcd123456';
        $description = 'description';

        $this->entity->setCaseType($caseType);

        $this->entity->update(
            $caseType,
            $categorys,
            $outcomes,
            $ecmsNo,
            $description
        );

        $this->assertEquals($caseType, $this->entity->getCaseType());
        $this->assertEquals($categorys, $this->entity->getCategorys());
        $this->assertEquals($outcomes, $this->entity->getOutcomes());
        $this->assertEquals($ecmsNo, $this->entity->getEcmsNo());
        $this->assertEquals($description, $this->entity->getDescription());
        ;
    }

    /**
     * Tests updateAnnualTestHistory
     */
    public function testUpdateAnnualTestHistory(): void
    {
        $annualTestHistory = 'annual test history';

        $this->entity->updateAnnualTestHistory(
            $annualTestHistory
        );

        $this->assertEquals($annualTestHistory, $this->entity->getAnnualTestHistory());
    }

    /**
     * Tests updateConvictionNote
     */
    public function testUpdateConvictionNote(): void
    {
        $convictionNote = 'conviction note';

        $this->entity->updateConvictionNote(
            $convictionNote
        );

        $this->assertEquals($convictionNote, $this->entity->getConvictionNote());
    }

    /**
     * Tests updateProhibitionNote
     */
    public function testUpdateProhibitionNote(): void
    {
        $prohibitionNote = 'prohibition note';

        $this->entity->updateProhibitionNote(
            $prohibitionNote
        );

        $this->assertEquals($prohibitionNote, $this->entity->getProhibitionNote());
    }

    public function testIsOpen(): void
    {
        $sut = $this->instantiate($this->entityClass);

        $this->assertTrue($sut->isOpen());

        $sut->setClosedDate('2015-06-10');

        $this->assertFalse($sut->isOpen());

        $sut->setClosedDate(null);
        $sut->setDeletedDate('2015-06-10');

        $this->assertFalse($sut->isOpen());
    }

    public function testHasComplaints(): void
    {
        $sut = $this->instantiate($this->entityClass);

        $this->assertFalse($sut->hasComplaints());

        $complaint = m::mock(ComplaintEntity::class)->makePartial();
        $sut->getComplaints()->add($complaint);

        $this->assertTrue($sut->hasComplaints());
    }

    /**
     * Tests closing a case
     */
    public function testClose(): void
    {
        $outcome = m::mock(RefData::class);

        $this->entity->setOutcomes(new ArrayCollection([$outcome]));

        $this->entity->close();

        $this->assertInstanceOf('\DateTime', $this->entity->getClosedDate());
    }

    public function testCloseThrowsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $this->entity->setOutcomes(new ArrayCollection());

        $this->entity->close();
    }

    public function testCloseWithOutstandingAppealThrowsValidationException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $outcome = m::mock(RefData::class);
        $appeal = new AppealEntity('1234');

        $this->entity->setAppeal($appeal);
        $this->entity->setOutcomes(new ArrayCollection([$outcome]));

        $this->entity->close();
    }

    public function testCloseWithWithdrawnAppeal(): void
    {
        $outcome = m::mock(RefData::class);
        $appeal = new AppealEntity('1234');
        $appeal->setWithdrawnDate('2015-06-10');

        $this->entity->setAppeal($appeal);
        $this->entity->setOutcomes(new ArrayCollection([$outcome]));

        $this->entity->close();
        $this->assertInstanceOf('\DateTime', $this->entity->getClosedDate());
    }

    public function testCloseWithCompleteAppeal(): void
    {
        $outcome = m::mock(RefData::class);
        $appeal = new AppealEntity('1234');

        $appeal->setOutcome($outcome);
        $appeal->setDecisionDate('2015-06-10');

        $this->entity->setAppeal($appeal);
        $this->entity->setOutcomes(new ArrayCollection([$outcome]));

        $this->entity->close();
        $this->assertInstanceOf('\DateTime', $this->entity->getClosedDate());
    }

    public function testCloseWithOutstandingStay(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $outcome = m::mock(RefData::class);
        $stay = new StayEntity($this->entity, $outcome);

        $this->entity->setStays(new ArrayCollection([$stay]));
        $this->entity->setOutcomes(new ArrayCollection([$outcome]));

        $this->entity->close();
    }

    public function testCloseWithWithdrawnStay(): void
    {
        $outcome = m::mock(RefData::class);
        $stay = new StayEntity($this->entity, $outcome);
        $stay->setWithdrawnDate('2015-06-10');

        $this->entity->setStays(new ArrayCollection([$stay]));
        $this->entity->setOutcomes(new ArrayCollection([$outcome]));

        $this->entity->close();
        $this->assertInstanceOf('\DateTime', $this->entity->getClosedDate());
    }

    public function testCloseWithCompleteStay(): void
    {
        $outcome = m::mock(RefData::class);
        $stay = new StayEntity($this->entity, $outcome);
        $stay->setOutcome($outcome);
        $stay->setDecisionDate('2015-06-10');

        $this->entity->setStays(new ArrayCollection([$stay]));
        $this->entity->setOutcomes(new ArrayCollection([$outcome]));

        $this->entity->close();
        $this->assertInstanceOf('\DateTime', $this->entity->getClosedDate());
    }

    public function testReopen(): void
    {
        $this->entity->setClosedDate(new \DateTime());

        $this->entity->reopen();

        $this->assertEquals(null, $this->entity->getClosedDate());
    }

    public function testReopenThrowsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $this->entity->setClosedDate(null);
        $this->entity->reopen();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('canAddSiProvider')]
    public function testCanAddSi(?m\mockInterface $erruRequest, ?\DateTime $closedDate, bool $expectedResult): void
    {
        $sut = $this->instantiate($this->entityClass);
        $sut->setErruRequest($erruRequest);
        $sut->setClosedDate($closedDate);

        $this->assertEquals($expectedResult, $sut->canAddSi());
    }

    /**
     * data provider for testCanAddSi
     */
    public static function canAddSiProvider(): array
    {
        $erruRequestNoModify = m::mock(ErruRequestEntity::class);
        $erruRequestNoModify->shouldReceive('canModify')->andReturn(false);

        $erruRequestCanModify = m::mock(ErruRequestEntity::class);
        $erruRequestCanModify->shouldReceive('canModify')->andReturn(true);

        $closedDate = new \DateTime('2016-12-25');

        return [
            [null, $closedDate, false],
            [$erruRequestNoModify, $closedDate, false],
            [$erruRequestCanModify, $closedDate, false],
            [null, null, false],
            [$erruRequestNoModify, null, false],
            [$erruRequestCanModify, null, true]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('canSendMsiResponseProvider')]
    public function testCanSendMsiResponse(
        ?m\mockInterface $erruRequest,
        ArrayCollection $si,
        ?\DateTime $closedDate,
        bool $expectedResult
    ): void {
        $sut = $this->instantiate($this->entityClass);
        $sut->setSeriousInfringements($si);
        $sut->setErruRequest($erruRequest);
        $sut->setClosedDate($closedDate);

        $this->assertEquals($expectedResult, $sut->canSendMsiResponse());
    }

    /**
     * data provider for testCanSendMsiResponse
     */
    public static function canSendMsiResponseProvider(): array
    {
        $siResponseSet = m::mock(SeriousInfringement::class);
        $siResponseSet->shouldReceive('responseSet')->andReturn(true);

        $siNoResponseSet = m::mock(SeriousInfringement::class);
        $siNoResponseSet->shouldReceive('responseSet')->andReturn(false);

        $erruRequestNoModify = m::mock(ErruRequestEntity::class);
        $erruRequestNoModify->shouldReceive('canModify')->andReturn(false);

        $erruRequestCanModify = m::mock(ErruRequestEntity::class);
        $erruRequestCanModify->shouldReceive('canModify')->andReturn(true);

        $closedDate = new \DateTime('2016-12-25');

        return [
            [null, new ArrayCollection([$siResponseSet]), $closedDate, false],
            [$erruRequestNoModify, new ArrayCollection([$siResponseSet]), $closedDate, false],
            [$erruRequestCanModify, new ArrayCollection([$siNoResponseSet, $siResponseSet]), $closedDate, false],
            [$erruRequestCanModify, new ArrayCollection([$siResponseSet]), $closedDate, false],
            [null, new ArrayCollection([$siResponseSet]), null, false],
            [$erruRequestNoModify, new ArrayCollection([$siResponseSet]), null, false],
            [$erruRequestCanModify, new ArrayCollection([$siNoResponseSet, $siResponseSet]), null, false],
            [$erruRequestCanModify, new ArrayCollection([$siResponseSet]), null, true]
        ];
    }

    public function testHasErruRequestedPenaltiesTrue(): void
    {
        //first infringement has no requested penalties
        $siEntity1 = m::mock(SeriousInfringement::class);
        $siEntity1->expects('hasRequestedPenalties')->andReturnFalse();

        //second infringement is checked and has penalties
        $siEntity2 = m::mock(SeriousInfringement::class);
        $siEntity2->expects('hasRequestedPenalties')->andReturnTrue();

        //third infringement doesn't need to be checked
        $siEntity3 = m::mock(SeriousInfringement::class);
        $siEntity3->expects('hasRequestedPenalties')->never();

        $this->entity->setSeriousInfringements(new ArrayCollection([$siEntity1, $siEntity2, $siEntity3]));
        $this->assertTrue($this->entity->hasErruRequestedPenalties());
    }

    public function testHasErruRequestedPenaltiesFalse(): void
    {
        //first infringement has no requested penalties
        $siEntity1 = m::mock(SeriousInfringement::class);
        $siEntity1->expects('hasRequestedPenalties')->andReturnFalse();

        //second infringement also has no requested penalties
        $siEntity2 = m::mock(SeriousInfringement::class);
        $siEntity2->expects('hasRequestedPenalties')->andReturnFalse();

        $this->entity->setSeriousInfringements(new ArrayCollection([$siEntity1, $siEntity2]));
        $this->assertFalse($this->entity->hasErruRequestedPenalties());
    }

    public function testHasErruRequestedPenaltiesNoSeriousInfringements(): void
    {
        $this->entity->setSeriousInfringements(new ArrayCollection());
        $this->assertFalse($this->entity->hasErruRequestedPenalties());
    }

    /**
     * Tests getCalculatedBundleValues
     */
    public function testGetCalculatedBundleValues(): void
    {
        /** @var Entity | m\MockInterface $sut */
        $sut = m::mock(Entity::class)->makePartial();
        $sut
            ->shouldReceive('canClose')->once()->andReturn('unit_CanClose')
            ->shouldReceive('canSendMsiResponse')->once()->andReturn('unit_CanSendMsi')
            ->shouldReceive('hasErruRequestedPenalties')->once()->andReturnFalse();

        $expected = [
            'isClosed' => false,
            'canReopen' => false,
            'canClose' => 'unit_CanClose',
            'canSendMsiResponse' => 'unit_CanSendMsi',
            'canAddSi' => false,
            'isErru' => false,
            'hasErruRequestedPenalties' => false,
        ];

        $this->assertEquals($expected, $sut->getCalculatedBundleValues());
    }

    public function testGetContextValue(): void
    {
        $this->entity->setId(111);

        $this->assertEquals(111, $this->entity->getContextValue());
    }

    /**
     * Tests getNoteType for given case type
     */
    public function testGetNoteType(): void
    {
        // check default
        $this->assertEquals(NoteEntity::NOTE_TYPE_CASE, $this->entity->getNoteType());

        $this->entity->setCaseType(Entity::LICENCE_CASE_TYPE);
        $this->assertEquals(NoteEntity::NOTE_TYPE_LICENCE, $this->entity->getNoteType());

        $this->entity->setCaseType(Entity::APP_CASE_TYPE);
        $this->assertEquals(NoteEntity::NOTE_TYPE_APPLICATION, $this->entity->getNoteType());

        $this->entity->setCaseType(Entity::TM_CASE_TYPE);
        $this->assertEquals(NoteEntity::NOTE_TYPE_TRANSPORT_MANAGER, $this->entity->getNoteType());
    }

    /**
     * Tests getRelatedOrgnisation
     */
    public function testGetRelatedOrganisationApplication(): void
    {
        $mockApplication = m::mock(ApplicationEntity::class);
        $mockOrganisation1 = m::mock(OrganisationEntity::class);
        $mockOrganisation2 = m::mock(OrganisationEntity::class);
        $mockOrganisation3 = m::mock(OrganisationEntity::class);
        $mockTm = m::mock(TransportManagerEntity::class);
        $mockLicence = m::mock(LicenceEntity::class);

        $this->assertNull($this->entity->getRelatedOrganisation());

        // test TransportManager case type
        $mockTm->shouldReceive('getRelatedOrganisation')->once()->andReturn($mockOrganisation1);
        $this->entity->setTransportManager($mockTm);
        $this->assertEquals($mockOrganisation1, $this->entity->getRelatedOrganisation());

        // test Licence case type
        $mockLicence->shouldReceive('getRelatedOrganisation')->once()->andReturn($mockOrganisation2);
        $this->entity->setLicence($mockLicence);
        $this->assertEquals($mockOrganisation2, $this->entity->getRelatedOrganisation());

        // test application case type
        $mockApplication->shouldReceive('getRelatedOrganisation')->once()->andReturn($mockOrganisation3);
        $this->entity->setApplication($mockApplication);
        $this->assertEquals($mockOrganisation3, $this->entity->getRelatedOrganisation());
    }

    public function testHasStayType(): void
    {
        $stayType = m::mock(RefData::class);

        $correctLicenceId = 7;
        $incorrectLicenceId = 99;

        $postedLicence = m::mock(LicenceEntity::class);
        $postedLicence->shouldReceive('getId')->andReturn($incorrectLicenceId);

        $applicationLicence = m::mock(LicenceEntity::class);
        $applicationLicence->shouldReceive('getId')->andReturn($correctLicenceId);
        $application = m::mock(ApplicationEntity::class);
        $application->shouldReceive('canCreateCase')->andReturn(true);
        $application->shouldReceive('getLicence')->andReturn($applicationLicence);

        $sut = m::mock(Entity::class)->makePartial();

        $sut->shouldReceive('getStays->matching')
            ->with(m::type(Criteria::class))
            ->andReturnUsing(
                function (Criteria $criteria) use ($stayType) {

                    /** @var \Doctrine\Common\Collections\Expr\Comparison $expr */
                    $expr = $criteria->getWhereExpression();

                    $this->assertEquals('stayType', $expr->getField());
                    $this->assertEquals('=', $expr->getOperator());
                    $this->assertEquals($stayType, $expr->getValue()->getValue());

                    $collection = m::mock();
                    $collection->shouldReceive('isEmpty')
                        ->andReturn(false);

                    return $collection;
                }
            );
        $this->assertTrue($sut->hasStayType($stayType));
    }

    public function testHasAppeal(): void
    {
        $caseType = m::mock(RefData::class);
        $openDate = new \DateTime();
        $categorys = new ArrayCollection();
        $outcomes = new ArrayCollection();
        $ecmsNo = 'abcd123456';
        $description = 'description';
        $correctLicenceId = 7;
        $incorrectLicenceId = 99;

        $postedLicence = m::mock(LicenceEntity::class);
        $postedLicence->shouldReceive('getId')->andReturn($incorrectLicenceId);

        $applicationLicence = m::mock(LicenceEntity::class);
        $applicationLicence->shouldReceive('getId')->andReturn($correctLicenceId);
        $application = m::mock(ApplicationEntity::class);
        $application->shouldReceive('canCreateCase')->andReturn(true);
        $application->shouldReceive('getLicence')->andReturn($applicationLicence);
        $transportManager = null;

        $sut = new Entity(
            $openDate,
            $caseType,
            $categorys,
            $outcomes,
            $application,
            $postedLicence,
            $transportManager,
            $ecmsNo,
            $description
        );

        $this->assertFalse($sut->hasAppeal());
        $sut->setAppeal(m::mock(AppealEntity::class));
        $this->assertTrue($sut->hasAppeal());
    }

    public function testIsTm(): void
    {
        $caseType = m::mock(RefData::class);
        $openDate = new \DateTime();
        $categorys = new ArrayCollection();
        $outcomes = new ArrayCollection();
        $ecmsNo = 'abcd123456';
        $description = 'description';
        $correctLicenceId = 7;
        $incorrectLicenceId = 99;

        $postedLicence = m::mock(LicenceEntity::class);
        $postedLicence->shouldReceive('getId')->andReturn($incorrectLicenceId);

        $applicationLicence = m::mock(LicenceEntity::class);
        $applicationLicence->shouldReceive('getId')->andReturn($correctLicenceId);
        $application = m::mock(ApplicationEntity::class);
        $application->shouldReceive('canCreateCase')->andReturn(true);
        $application->shouldReceive('getLicence')->andReturn($applicationLicence);
        $transportManager = null;

        $sut = new Entity(
            $openDate,
            $caseType,
            $categorys,
            $outcomes,
            $application,
            $postedLicence,
            $transportManager,
            $ecmsNo,
            $description
        );

        $this->assertFalse($sut->isTm());
        $sut->setTransportManager(m::mock(TransportManagerEntity::class));
        $this->assertTrue($sut->isTm());
    }
}
