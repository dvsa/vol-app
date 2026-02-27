<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Validation\Validators\IsLicenceSurrenderable;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Surrender;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class IsLicenceSurrenderableTest extends AbstractValidatorsTestCase
{
    /**
     * @var IsLicenceSurrenderable
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new IsLicenceSurrenderable();

        parent::setUp();
    }

    public function testValidLicenceNoExistingSurrenderNoOpenApplications(): void
    {
        $licenceId = 1;
        $licenceStatus = Licence::LICENCE_STATUS_VALID;
        $openApplications = [];
        $expected = true;

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getStatus->getId')->andReturn($licenceStatus);
        $licenceRepo = $this->mockRepo('Licence');
        $licenceRepo->shouldReceive('fetchById')->with($licenceId)->andReturn($licence);

        $surrenderRepo = $this->mockRepo('Surrender');
        $surrenderRepo->shouldReceive('fetchOneByLicenceId')->with($licenceId)->andThrow(NotFoundException::class);

        $applicationRepo = $this->mockRepo('Application');
        $applicationRepo->shouldReceive('fetchOpenApplicationsForLicence')->with($licenceId)->andReturn($openApplications);

        $this->assertEquals($expected, $this->sut->isValid($licenceId));
    }

    public function testValidLicenceNoExistingSurrenderWithOpenApplications(): void
    {
        $licenceId = 1;
        $licenceStatus = Licence::LICENCE_STATUS_VALID;
        $existingSurrender = [];
        $openApplications = ['open application'];

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getStatus->getId')->andReturn($licenceStatus);
        $licenceRepo = $this->mockRepo('Licence');
        $licenceRepo->shouldReceive('fetchById')->with($licenceId)->andReturn($licence);

        $surrenderRepo = $this->mockRepo('Surrender');
        $surrenderRepo->shouldReceive('fetchByLicenceId')->with($licenceId)->andReturn($existingSurrender);

        $applicationRepo = $this->mockRepo('Application');
        $applicationRepo->shouldReceive('fetchOpenApplicationsForLicence')->with($licenceId)->andReturn($openApplications);

        $this->expectException(ForbiddenException::class);

        $this->sut->isValid($licenceId);
    }

    public function testValidLicenceWithExistingSurrenderNotWithdrawn(): void
    {
        $licenceId = 1;
        $licenceStatus = Licence::LICENCE_STATUS_VALID;

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getStatus->getId')->andReturn($licenceStatus);
        $licenceRepo = $this->mockRepo('Licence');
        $licenceRepo->shouldReceive('fetchById')->with($licenceId)->andReturn($licence);

        $applicationRepo = $this->mockRepo('Application');
        $applicationRepo->shouldReceive('fetchOpenApplicationsForLicence')->with($licenceId)->andReturn([]);

        $existingSurrender = m::mock(Surrender::class);
        $existingSurrender->shouldReceive('getStatus->getId')
            ->andReturn('surr_sts_signed');

        $surrenderRepo = $this->mockRepo('Surrender');
        $surrenderRepo->shouldReceive('fetchOneByLicenceId')->with($licenceId)->andReturn($existingSurrender);

        $this->expectException(ForbiddenException::class);

        $this->sut->isValid($licenceId);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    public function testValidLicenceWithExistingSurrenderWithdrawn(): void
    {
        $licenceId = 1;
        $licenceStatus = Licence::LICENCE_STATUS_VALID;

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getStatus->getId')->andReturn($licenceStatus);
        $licenceRepo = $this->mockRepo('Licence');
        $licenceRepo->shouldReceive('fetchById')->with($licenceId)->andReturn($licence);

        $applicationRepo = $this->mockRepo('Application');
        $applicationRepo->shouldReceive('fetchOpenApplicationsForLicence')->with($licenceId)->andReturn([]);

        $existingSurrender = m::mock(Surrender::class);
        $existingSurrender->shouldReceive('getStatus->getId')
            ->andReturn('surr_sts_withdrawn');

        $surrenderRepo = $this->mockRepo('Surrender');
        $surrenderRepo->shouldReceive('fetchOneByLicenceId')->with($licenceId)->andReturn($existingSurrender);

        $this->sut->isValid($licenceId);
    }

    public function testNotValidLicence(): void
    {
        $licenceId = 1;
        $licenceStatus = Licence::LICENCE_STATUS_REVOKED;

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getStatus->getId')->andReturn($licenceStatus);
        $licence->shouldReceive('getStatus->getDescription')->once();
        $licenceRepo = $this->mockRepo('Licence');
        $licenceRepo->shouldReceive('fetchById')->with($licenceId)->andReturn($licence);

        $this->expectException(ForbiddenException::class);

        $this->sut->isValid($licenceId);
    }

    public function testNotExistingLicence(): void
    {
        $licenceId = 1;
        $licenceRepo = $this->mockRepo('Licence');
        $licenceRepo->shouldReceive('fetchById')->with($licenceId)->andThrow(BadRequestException::class);

        $this->expectException(BadRequestException::class);

        $this->sut->isValid($licenceId);
    }
}
