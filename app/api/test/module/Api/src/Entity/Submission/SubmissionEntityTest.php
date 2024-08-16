<?php

namespace Dvsa\OlcsTest\Api\Entity\Submission;

use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Submission\Submission as Entity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Mockery as m;

/**
 * Submission Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class SubmissionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;
    /**
     * @var Entity
     */
    protected $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->sut = $this->instantiate($this->entityClass);
    }

    public function testConstructor()
    {
        $case = m::mock(CaseEntity::class)->makePartial();

        $submissionType = m::mock(RefDataEntity::class)->makePartial();

        $submission = new Entity($case, $submissionType);

        $this->assertSame($submissionType, $submission->getSubmissionType());
        $this->assertSame($case, $submission->getCase());
    }

    public function testClose()
    {
        $case = m::mock(CaseEntity::class)->makePartial();

        $submissionType = m::mock(RefDataEntity::class)->makePartial();

        $submission = new Entity($case, $submissionType);

        $this->assertFalse($submission->isClosed());
        $this->assertTrue($submission->canClose());

        $submission->close();
        $this->assertFalse($submission->canClose());
        $this->assertTrue($submission->isClosed());
    }

    /**
     * Tests cases attached to NI licences
     */
    public function testIsNiLicenceCase()
    {
        $mockLicence = m::mock();
        $mockLicence->shouldReceive('getNiFlag')->andReturn('Y');

        $case = m::mock(CaseEntity::class)->makePartial();
        $case->shouldReceive('getLicence')->andReturn($mockLicence);

        $submissionType = m::mock(RefDataEntity::class)->makePartial();
        $submission = new Entity($case, $submissionType);

        $this->assertTrue($submission->isNi());
    }

    /**
     * Tests cases attached to licences Non-NI
     */
    public function testIsNotNiLicenceCase()
    {
        $mockLicence = m::mock();
        $mockLicence->shouldReceive('getNiFlag')->andReturn('N');

        $case = m::mock(CaseEntity::class)->makePartial();
        $case->shouldReceive('getLicence')->andReturn($mockLicence);

        $submissionType = m::mock(RefDataEntity::class)->makePartial();
        $submission = new Entity($case, $submissionType);

        $this->assertFalse($submission->isNi());
    }

    /**
     * Tests cases attached to applications
     */
    public function testIsNiApplicationCase()
    {
        $mockApplication = m::mock();
        $mockApplication->shouldReceive('getNiFlag')->andReturn('Y');

        $case = m::mock(CaseEntity::class)->makePartial();
        $case->shouldReceive('getLicence')->andReturnNull();

        $case = m::mock(CaseEntity::class)->makePartial();
        $case->shouldReceive('getApplication')->andReturn($mockApplication);

        $submissionType = m::mock(RefDataEntity::class)->makePartial();
        $submission = new Entity($case, $submissionType);

        $this->assertTrue($submission->isNi());
    }

    /**
     * Tests cases attached to applications Not NI
     */
    public function testIsNotNiApplicationCase()
    {
        $mockApplication = m::mock();
        $mockApplication->shouldReceive('getNiFlag')->andReturn('N');

        $case = m::mock(CaseEntity::class)->makePartial();
        $case->shouldReceive('getLicence')->andReturnNull();

        $case = m::mock(CaseEntity::class)->makePartial();
        $case->shouldReceive('getApplication')->andReturn($mockApplication);

        $submissionType = m::mock(RefDataEntity::class)->makePartial();
        $submission = new Entity($case, $submissionType);

        $this->assertFalse($submission->isNi());
    }

    /**
     * Tests cases attached to transport manager
     */
    public function testIsNiTransportManagerCase()
    {
        $mockApplication = m::mock();
        $mockApplication->shouldReceive('getNiFlag')->andReturn('N');

        $case = m::mock(CaseEntity::class)->makePartial();
        $case->shouldReceive('getLicence')->andReturnNull();

        $case = m::mock(CaseEntity::class)->makePartial();
        $case->shouldReceive('getApplication')->andReturnNull();

        $submissionType = m::mock(RefDataEntity::class)->makePartial();
        $submission = new Entity($case, $submissionType);

        $this->assertFalse($submission->isNi());
    }

    public function testGetRelatedOrganisation()
    {
        $case = m::mock(CaseEntity::class)->makePartial();
        $case->shouldReceive('getRelatedOrganisation')->with()->once()->andReturn('ORG1');

        $submissionType = m::mock(RefDataEntity::class)->makePartial();
        $submission = new Entity($case, $submissionType);

        $this->assertSame('ORG1', $submission->getRelatedOrganisation());
    }

    public function testGetSetSectionData()
    {
        $value  = $this->sut->getSectionData();
        $this->assertNull($value);
        $this->sut->setSectionData('KEY', 'VALUE');
        $value  = $this->sut->getSectionData();
        $this->assertSame(['KEY' => 'VALUE'], $value);
    }

    public function testSectionData()
    {
        $this->sut->setSectionData('KEY', 'VALUE');
        $this->sut->setSubmissionDataSnapshot();
        $this->assertSame('{"KEY":"VALUE"}', ($this->sut->getDataSnapshot()));
    }

    public function testNewSubmissionDataSnapshot()
    {
        $this->sut->setNewSubmissionDataSnapshot(['KEY' => 'VALUE']);
        $this->assertSame('{"KEY":"VALUE"}', ($this->sut->getDataSnapshot()));
    }

    public function testReopen()
    {
        $this->sut->setClosedDate('SOMETHING');
        $this->sut->reopen();
        $this->assertNull($this->sut->getClosedDate());
    }

    public function testReopenError()
    {
        $this->sut->setClosedDate(null);
        $this->expectException(ForbiddenException::class);
        $this->sut->reopen();
    }
    public function testCloseError()
    {
        $this->sut->setClosedDate('YYYY-MM-DD');
        $this->expectException(ForbiddenException::class);
        $this->sut->close();
    }
}
